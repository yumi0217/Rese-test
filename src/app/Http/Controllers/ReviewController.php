<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ReviewRequest;

class ReviewController extends Controller
{
    public function create(Reservation $reservation)
    {
        // 自分の予約以外は不可
        abort_unless($reservation->user_id === Auth::id(), 403);

        // 既にレビュー済みなら不可
        abort_if(
            Review::where('reservation_id', $reservation->id)->exists(),
            403,
            'この予約は既にレビュー済みです。'
        );

        return view('reviews.create', [
            'reservation' => $reservation->load('restaurant'),
        ]);
    }

    public function store(ReviewRequest $request)
    {
        $data = $request->validated();

        $reservation = Reservation::with('restaurant')->findOrFail($data['reservation_id']);

        // 自分の予約か＆未レビューかを最終チェック
        abort_unless($reservation->user_id === $request->user()->id, 403);
        abort_if(Review::where('reservation_id', $reservation->id)->exists(), 403);

        Review::create([
            'user_id'        => $request->user()->id,
            'restaurant_id'  => $reservation->restaurant_id,
            'reservation_id' => $reservation->id,
            'rating'         => $data['rating'],
            'comment'        => $data['comment'] ?? null,
        ]);

        return redirect()
            ->route('detail', ['shop_id' => $reservation->restaurant_id])
            ->with('status', 'レビューを投稿しました。');
    }
}
