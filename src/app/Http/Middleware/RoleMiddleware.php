<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * 使い方例:
     * ->middleware('role:admin')
     * ->middleware('role:owner,user')
     */
    public function handle($request, Closure $next, ...$roles)
    {
        // 未ログインならログイン画面へ
        if (!Auth::check()) {
            return redirect()->route('login'); // Fortifyなら 'login' でOK
        }

        $user = Auth::user();

        // role列が無い/空、または許可ロールに含まれない場合は403
        if (!$user->role || (!empty($roles) && !in_array($user->role, $roles, true))) {
            abort(403);
        }

        return $next($request);
    }
}
