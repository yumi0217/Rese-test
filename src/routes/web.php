<?php

use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\FavoriteController;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use App\Models\User;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminOwnerController;
use App\Http\Controllers\Owner\OwnerController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Owner\OwnerNoticeController;

/*
|--------------------------------------------------------------------------
| Public / Auth
|--------------------------------------------------------------------------
*/
// 会員登録
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::get('/register/complete', [RegisteredUserController::class, 'complete'])->name('auth.complete');

// ログイン／ログアウト
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// 店舗一覧・詳細
Route::get('/', [ShopController::class, 'index'])->name('shops.index');
Route::get('/menu', fn() => Auth::check() ? view('menu.auth') : view('menu.guest'))->name('menu');
Route::get('/detail/{shop_id}', [ShopController::class, 'detail'])->name('detail');

/*
|--------------------------------------------------------------------------
| Reservations
|--------------------------------------------------------------------------
*/
// 予約作成（未ログインも通す：コントローラ側で誘導）
Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
Route::get('/reservations/complete', [ReservationController::class, 'complete'])->name('reservations.complete');

// ユーザー専用（要ログイン＋role:user）
Route::middleware(['auth', 'role:user'])->group(function () {
    // 予約キャンセル
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])
        ->name('reservations.destroy');

    // マイページ
    Route::get('/mypage', [UserController::class, 'show'])->name('mypage');

    // お気に入り（URL: /favorites/toggle/123 でも、POST body: restaurant_id でもOK）
    Route::post('/favorites/toggle/{restaurant?}', [FavoriteController::class, 'toggle'])
        ->name('favorites.toggle');
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
});

// routes/web.php
Route::middleware('auth')->group(function () {
    Route::get('/payments/checkout/{reservation}', [PaymentController::class, 'checkout'])
        ->name('payments.checkout');
    Route::get('/payments/success', [PaymentController::class, 'success'])->name('payments.success');
    Route::get('/payments/cancel',  [PaymentController::class, 'cancel'])->name('payments.cancel');
});

// 利用者：QR表示
Route::middleware(['web', 'auth'])->get(
    '/reservations/{reservation}/qr',
    [\App\Http\Controllers\QrController::class, 'show']
)->name('reservations.qr.show');

Route::middleware('auth')->group(function () {
    Route::get('/reservations/{reservation}/edit', [\App\Http\Controllers\ReservationController::class, 'edit'])
        ->name('reservations.edit');
    Route::match(['put', 'patch'], '/reservations/{reservation}', [\App\Http\Controllers\ReservationController::class, 'update'])
        ->name('reservations.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/reviews/create/{reservation}', [App\Http\Controllers\ReviewController::class, 'create'])
        ->name('reviews.create');
    Route::post('/reviews', [App\Http\Controllers\ReviewController::class, 'store'])
        ->name('reviews.store');
});



/*
|--------------------------------------------------------------------------
| Email Verification (simple)
|--------------------------------------------------------------------------
*/
Route::get('/email/verify', fn() => view('auth.verify-email'))->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);
    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) abort(403);
    if ($user->hasVerifiedEmail()) {
        return redirect()->route('auth.complete')->with('status', 'already-verified');
    }
    $user->forceFill(['email_verified_at' => now()])->save();
    event(new Verified($user));
    return redirect()->route('auth.complete')->with('success', 'メール認証が完了しました。');
})->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

/*
|--------------------------------------------------------------------------
| Admin (auth + role:admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/owners/create', [AdminOwnerController::class, 'create'])->name('admin.owners.create');
    Route::post('/owners', [AdminOwnerController::class, 'store'])->name('admin.owners.store');
    Route::get('/owners/{owner}/edit', [AdminOwnerController::class, 'edit'])->name('admin.owners.edit');
    Route::put('/owners/{owner}', [AdminOwnerController::class, 'update'])->name('admin.owners.update');
});

/*
|--------------------------------------------------------------------------
| Owner (auth + role:owner)
|--------------------------------------------------------------------------
*/
Route::prefix('owner')->as('owner.')->middleware(['auth', 'role:owner'])->group(function () {
    Route::get('/dashboard', [OwnerController::class, 'dashboard'])->name('dashboard');
    Route::get('/shops',              [\App\Http\Controllers\Owner\OwnerShopController::class, 'index'])->name('shops.index');   // 一覧（カード＋編集ボタン）
    Route::get('/shops/{shop}/edit',  [\App\Http\Controllers\Owner\OwnerShopController::class, 'edit'])->name('shops.edit');     // 編集フォーム
    Route::put('/shops/{shop}',       [\App\Http\Controllers\Owner\OwnerShopController::class, 'update'])->name('shops.update');
    Route::get('/shops/create', [\App\Http\Controllers\Owner\OwnerShopController::class, 'create'])
        ->name('shops.create');
    Route::post('/shops', [\App\Http\Controllers\Owner\OwnerShopController::class, 'store'])
        ->name('shops.store');
});

// 店舗側：照合
// 店舗側：照合（owners.* に統一）
Route::middleware(['auth', 'role:owner'])->prefix('owner')->group(function () {
    Route::get('/qr/verify',       [\App\Http\Controllers\QrController::class, 'verifyForm'])
        ->name('owners.qr.verify');        // ← 複数
    Route::post('/qr/verify',      [\App\Http\Controllers\QrController::class, 'verify'])
        ->name('owners.qr.verify.post');   // ← 複数
    Route::get('/qr/verify/check', [\App\Http\Controllers\QrController::class, 'verifyGet'])
        ->name('owners.qr.verify.get');    // ← 複数
});


Route::middleware(['auth', 'role:owner'])->group(function () {
    Route::get('/notice',  [OwnerNoticeController::class, 'create'])->name('notice.create');
    Route::post('/notice', [OwnerNoticeController::class, 'send'])->name('notice.send');
});
