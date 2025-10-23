<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Reservation;
use App\Models\Area;
use App\Models\Genre;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    /**
     * 飲食店一覧ページ表示
     */
    public function index(Request $request)
    {
        $areaId  = (int) $request->query('area', 0);
        $genreId = (int) $request->query('genre', 0);
        $keyword = trim((string) $request->query('keyword', ''));

        $query = Restaurant::with(['area', 'genre']);

        if ($areaId > 0)  $query->where('area_id', $areaId);
        if ($genreId > 0) $query->where('genre_id', $genreId);
        if ($keyword !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $keyword) . '%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)->orWhere('description', 'like', $like);
            });
        }

        $shops  = $query->orderBy('id')->paginate(12)->appends($request->query());
        $areas  = Area::orderBy('name')->get();
        $genres = Genre::orderBy('id')->get();

        return view('shops.index', compact('shops', 'areas', 'genres', 'areaId', 'genreId', 'keyword'));
    }

    /**
     * 飲食店詳細
     */
    public function detail($shop_id, Request $request)
    {
        $restaurant = Restaurant::with(['area', 'genre'])->findOrFail($shop_id);

        // 選択肢
        $timeOptions = [];
        $t = new \DateTime('10:00');
        $end = new \DateTime('22:00');
        while ($t <= $end) {
            $timeOptions[] = $t->format('H:i');
            $t->modify('+30 minutes');
        }
        $numbers = range(1, 10);

        $prefill = session()->pull('prefill_reservation', []);
        $userId  = Auth::id() ?? 0;
        $hasMyPending = false;

        // 自分の未消化の最新予約（あれば初期値の第2優先）
        $pDate = $pTime = null;
        $pNum = null;
        if ($userId) {
            $myPending = Reservation::where('user_id', $userId)
                ->where('restaurant_id', $restaurant->id)
                ->whereDate('reservation_date', '>=', now()->toDateString())
                ->latest('reservation_date')->latest('id')->first();

            if ($myPending) {
                $hasMyPending = true;
                $pDate = $myPending->reservation_date instanceof \DateTimeInterface
                    ? $myPending->reservation_date->format('Y-m-d')
                    : (string) $myPending->reservation_date;

                $pTime = $myPending->reservation_time instanceof \DateTimeInterface
                    ? $myPending->reservation_time->format('H:i')
                    : substr((string) $myPending->reservation_time, 0, 5);

                $pNum = (int) $myPending->number_of_people;
            }
        }

        // 初期値の決定（old/query > myPending > session > 既定）
        $initial = [
            'date'   => old('date')   ?? $request->query('date')   ?? $pDate ?? ($prefill['date']   ?? now()->toDateString()),
            'time'   => old('time')   ?? $request->query('time')   ?? $pTime ?? ($prefill['time']   ?? '10:00'),
            'number' => old('number') ?? $request->query('number') ?? $pNum ?? ($prefill['number'] ?? 1),
        ];

        // 初期値の最終正規化（"12:00:00" や "1200" を "12:00" に、人数は int に）
        if (!is_null($initial['time'])) {
            $tval = trim((string)$initial['time']);
            if (preg_match('/^(\d{2}:\d{2})/', $tval, $m)) {
                $initial['time'] = $m[1];
            } elseif (preg_match('/^(\d{2})(\d{2})$/', $tval, $m)) {
                $initial['time'] = $m[1] . ':' . $m[2];
            } else {
                $initial['time'] = '10:00';
            }
        } else {
            $initial['time'] = '10:00';
        }
        $initial['number'] = is_null($initial['number']) ? 1 : (int)$initial['number'];

        // 来店済み＆未レビュー（ボタン用）
        $reviewable = null;
        if ($userId) {
            $reviewable = Reservation::with('review')
                ->where('user_id', $userId)
                ->where('restaurant_id', $restaurant->id)
                ->whereDoesntHave('review')
                ->latest('reservation_date')->latest('id')->first();
        }

        return view('shops.detail', [
            'restaurant'          => $restaurant,
            'timeOptions'         => $timeOptions,
            'numbers'             => $numbers,
            'initial'             => $initial,
            'userId'              => $userId,
            'hasMyPending'        => $hasMyPending,
            'canReview'           => (bool) $reviewable,
            'reviewReservationId' => $reviewable?->id,
        ]);
    }
}
