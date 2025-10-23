<?php
// app/Http/Controllers/Owner/OwnerController.php
namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Review;

class OwnerController extends Controller
{
    public function dashboard(Request $request)
    {
        $owner = Auth::user();

        // この代表者が管轄する店舗ID一覧
        $shopIds = $owner->restaurants()->pluck('id');

        // ====== フィルタ入力 ======
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');
        $timeFrom = $request->query('time_from');
        $timeTo   = $request->query('time_to');

        // ====== 予約一覧 ======
        $q = Reservation::with(['user', 'restaurant'])
            ->whereIn('restaurant_id', $shopIds->isNotEmpty() ? $shopIds : [-1]);

        if ($dateFrom) $q->whereDate('reservation_date', '>=', $dateFrom);
        if ($dateTo)   $q->whereDate('reservation_date', '<=', $dateTo);
        if ($timeFrom) $q->whereTime('reservation_time', '>=', $timeFrom);
        if ($timeTo)   $q->whereTime('reservation_time', '<=', $timeTo);

        $reservations = $q->orderBy('reservation_date')
            ->orderBy('reservation_time')
            ->paginate(10)
            ->appends($request->query());

        // ====== KPI ======
        $kpiBase = Reservation::whereIn('restaurant_id', $shopIds->isNotEmpty() ? $shopIds : [-1]);

        $todayCount = (clone $kpiBase)
            ->whereDate('reservation_date', now()->toDateString())
            ->count();

        $totalCount = (clone $kpiBase)->count();

        // ★ 店舗ごとのレビュー集計（平均＆件数）
        $restaurants = Restaurant::whereIn('id', $shopIds)
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->get();

        // ★ 最新レビュー（直近10件）
        $latestReviews = Review::with(['user', 'restaurant'])
            ->whereIn('restaurant_id', $shopIds)
            ->latest()
            ->take(10)
            ->get();

        return view('owners.dashboard', [
            'owner'        => $owner,
            'reservations' => $reservations,
            'todayCount'   => $todayCount,
            'totalCount'   => $totalCount,
            'dateFrom'     => $dateFrom,
            'dateTo'       => $dateTo,
            'timeFrom'     => $timeFrom,
            'timeTo'       => $timeTo,
            // ★ 追加
            'restaurants'   => $restaurants,
            'latestReviews' => $latestReviews,
        ]);
    }
}
