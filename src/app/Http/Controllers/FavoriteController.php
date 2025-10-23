<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** お気に入り一覧 */
    public function index()
    {
        $restaurants = request()->user()
            ->favoriteRestaurants()
            ->with(['area', 'genre'])
            ->orderByDesc('favorites.created_at')
            ->paginate(12);

        return view('favorites.index', compact('restaurants'));
    }

    /**
     * お気に入りトグル（ルート {restaurant} でも、POST restaurant_id でも可）
     */
    public function toggle(Request $request, ?Restaurant $restaurant = null)
    {
        // 1) ルートパラメータ or 2) POST の restaurant_id
        $restaurantId = $restaurant?->id ?? (int) $request->input('restaurant_id');
        if (!$restaurantId) {
            return back()->withErrors(['favorite' => 'restaurant_id is required']);
        }

        $restaurant = Restaurant::findOrFail($restaurantId);
        $userId     = $request->user()->id;

        $fav = Favorite::where('user_id', $userId)
            ->where('restaurant_id', $restaurant->id)
            ->first();

        if ($fav) {
            $fav->delete();
            return back()->with('status', 'unfav');
        }

        Favorite::create([
            'user_id'        => $userId,
            'restaurant_id'  => $restaurant->id,
        ]);

        return back()->with('status', 'fav');
    }

    /**
     * 旧フォーム互換：POSTで restaurant_id を送る呼び方
     * （内部的には toggle と同じ挙動）
     */
    public function toggleById(Request $request)
    {
        $data = $request->validate([
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
        ]);

        // toggle と同じ処理へ委譲
        return $this->toggle($request, Restaurant::findOrFail((int) $data['restaurant_id']));
    }
}
