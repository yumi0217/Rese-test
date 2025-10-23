<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest; // ★ 追加（FormRequest）
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class RegisteredUserController extends Controller
{
    /**
     * 登録フォーム表示
     */
    public function create(Request $request)
    {
        // 他画面から持ち越されたエラー/入力値をクリア（任意）
        $request->session()->forget(['errors', '_old_input']);

        return view('auth.register');
    }

    /**
     * 新規ユーザー登録処理
     */
    public function store(RegisterRequest $request) // ★ ここを RegisterRequest に
    {
        // ★ ここで既にバリデーション済み
        $data = $request->validated();

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // 認証メール送信（MustVerifyEmail 前提）
        event(new Registered($user));

        // 自動ログインは行わず、認証案内へ
        return redirect()
            ->route('verification.notice')
            ->with('status', 'verification-link-sent');
    }

    public function complete()
    {
        return view('auth.complete'); // resources/views/auth/complete.blade.php
    }
}
