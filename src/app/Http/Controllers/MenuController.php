<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    public function show()
    {
        return Auth::check()
            ? view('menu.auth')   // ログイン済みメニュー
            : view('menu.guest'); // 未ログインメニュー
    }
}
