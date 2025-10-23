<?php
// app/Http/Controllers/Admin/AdminController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        // 役割が owner のユーザーを新しい順で取得（ページネーション）
        $owners = User::where('role', 'owner')
            ->orderByDesc('id')
            ->paginate(12); // 件数は必要に応じて

        return view('admin.dashboard', compact('owners'));
    }
}
