<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function store(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();

        $role = Auth::user()->role ?? 'user';
        $to = match ($role) {
            'admin' => route('admin.dashboard'),
            'owner' => url('/owner/dashboard'),   // 将来使うなら
            default => url('/shops'),
        };

        return redirect()->intended($to);
    }
}
