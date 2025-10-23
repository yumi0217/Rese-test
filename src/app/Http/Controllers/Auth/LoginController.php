<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;

class LoginController extends Controller
{
    // ログイン画面
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // ログイン処理
    public function login(LoginRequest $request)
    {
        // ※ バリデーションは LoginRequest::rules() / messages() で実施
        // 認証（失敗時は日本語メッセージで ValidationException）
        $request->authenticate();

        // ロール一致チェック（タブ選択と実ユーザーの role）
        $user = Auth::user();
        if ($user->role !== $request->input('expected_role')) {
            Auth::logout();

            return back()
                ->withInput()
                ->with('role_mismatch', '選択したロールでログインしてください。');
        }

        // セッション再生成
        $request->session()->regenerate();

        // ロール別に遷移
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'owner' => redirect()->route('owner.dashboard'),
            default => redirect()->route('shops.index'),
        };
    }

    // ログアウト
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('menu');
    }
}
