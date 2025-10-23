<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(Request $request)
    {
        $user = $request->user();

        // 過去も含めて、日付→時間の昇順で一覧
        $reservations = Reservation::with('restaurant')
            ->where('user_id', $user->id)
            ->orderBy('reservation_date')   // 昇順
            ->orderBy('reservation_time')   // 昇順
            ->get();

        // お気に入り店舗
        $favoriteRestaurants = $user->favoriteRestaurants()
            ->with(['area', 'genre'])
            ->get();

        return view('users.mypage', compact('user', 'reservations', 'favoriteRestaurants'));
    }
}
