<?php
// app/Http/Controllers/Admin/AdminOwnerController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Restaurant; // ★追加
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Admin\AdminOwnerStoreRequest;

class AdminOwnerController extends Controller
{
    /** 店舗代表者 作成フォーム表示 */
    public function create()
    {
        // ★ 追加：チェックボックス表示用に全店舗を渡す
        $restaurants = Restaurant::orderBy('name')->get();
        return view('admin.owners.create', compact('restaurants'));
    }

    /** 店舗代表者 作成処理（パスワードは手入力必須） */
    public function store(AdminOwnerStoreRequest $request)
    {
        $data = $request->validated();

        $owner = User::create([
            'name'              => $data['name'],
            'email'             => strtolower($data['email']),
            'password'          => Hash::make($data['password']),
            'role'              => 'owner',          // 固定
            'email_verified_at' => now(),
        ]);

        // ★ 追加：チェックされた店舗をこのオーナーに割当
        $shopIds = $request->input('shop_ids', []);
        if (!empty($shopIds)) {
            Restaurant::whereIn('id', $shopIds)->update(['owner_id' => $owner->id]);
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('status', '店舗代表者を作成しました。');
    }

    /** 店舗代表者 編集表示 */
    public function edit(User $owner)
    {
        if ($owner->role !== 'owner') abort(404);

        // ★ 追加：全店舗と、現在このオーナーが持っている店舗ID一覧
        $restaurants = Restaurant::orderBy('name')->get();
        $assignedShopIds = Restaurant::where('owner_id', $owner->id)->pluck('id')->all();

        return view('admin.owners.edit', compact('owner', 'restaurants', 'assignedShopIds'));
    }

    /** 店舗代表者 更新処理 */
    public function update(Request $request, User $owner)
    {
        if ($owner->role !== 'owner') abort(404);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email,' . $owner->id],
            'password'  => ['nullable', 'string', 'min:8', 'confirmed'], // 変更時のみ
            // ★ 追加：担当店舗
            'shop_ids'   => ['array'],
            'shop_ids.*' => ['integer', 'exists:restaurants,id'],
        ]);

        $owner->name  = $data['name'];
        $owner->email = strtolower($data['email']);
        if (!empty($data['password'])) {
            $owner->password = Hash::make($data['password']);
        }
        $owner->role = 'owner'; // 念のため固定
        $owner->save();

        // ★ 追加：割当の更新（いったん外してから、選択分を割当）
        $shopIds = $request->input('shop_ids', []);
        Restaurant::where('owner_id', $owner->id)->update(['owner_id' => null]);
        if (!empty($shopIds)) {
            Restaurant::whereIn('id', $shopIds)->update(['owner_id' => $owner->id]);
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('status', '店舗代表者を更新しました。');
    }
}
