<?php
// src/app/Http/Controllers/Owner/OwnerShopController.php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Area;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Owner\StoreShopRequest;

class OwnerShopController extends Controller
{
    /**
     * 管轄店舗のカード一覧（検索/フィルタ付き）
     */
    public function index(Request $request)
    {
        $ownerId = Auth::id();

        // フィルタ入力
        $areaId  = (int) $request->query('area', 0);
        $genreId = (int) $request->query('genre', 0);
        $keyword = trim((string) $request->query('q', ''));

        $q = Restaurant::with(['area', 'genre'])
            ->where('owner_id', $ownerId);

        if ($areaId > 0)  $q->where('area_id',  $areaId);
        if ($genreId > 0) $q->where('genre_id', $genreId);
        if ($keyword !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $keyword) . '%';
            $q->where(function ($x) use ($like) {
                $x->where('name', 'like', $like)
                    ->orWhere('description', 'like', $like);
            });
        }

        $shops  = $q->orderBy('id')
            ->paginate(12)
            ->appends($request->query());

        // フィルタUI用
        $areas  = Area::orderBy('name')->get();
        $genres = Genre::orderBy('id')->get(); // name列があれば orderBy('name')

        return view('owners.shops.index', compact(
            'shops',
            'areas',
            'genres',
            'areaId',
            'genreId',
            'keyword'
        ));
    }

    /**
     * 編集フォーム
     */
    public function edit(Restaurant $shop)
    {
        $this->authorizeShop($shop);

        $areas  = Area::orderBy('name')->get();
        $genres = Genre::orderBy('id')->get();

        return view('owners.shops.edit', compact('shop', 'areas', 'genres'));
    }

    /**
     * 更新（画像アップロード対応）
     */
    public function update(Request $request, Restaurant $shop)
    {
        $this->authorizeShop($shop);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'area_id'     => ['required', 'integer', 'exists:areas,id'],
            'genre_id'    => ['required', 'integer', 'exists:genres,id'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], // 5MB
        ]);

        // 画像差し替え
        if ($request->hasFile('image')) {
            if ($shop->image_url && Storage::disk('public')->exists($shop->image_url)) {
                Storage::disk('public')->delete($shop->image_url);
            }
            $path = $request->file('image')->store('shops', 'public'); // storage/app/public/shops/...
            $data['image_url'] = $path; // モデルのカラム名に合わせる
        }

        $shop->update($data);

        return redirect()
            ->route('owner.shops.index')
            ->with('status', '店舗情報を更新しました。');
    }

    /**
     * オーナー本人の店舗かチェック
     */
    private function authorizeShop(Restaurant $shop): void
    {
        if ($shop->owner_id !== Auth::id()) {
            abort(403);
        }
    }

    public function create()
    {
        $areas  = Area::orderBy('name')->get();
        $genres = Genre::orderBy('id')->get();

        return view('owners.shops.create', compact('areas', 'genres'));
    }

    /**
     * 追加（StoreShopRequestで検証するので validate 不要）
     */
    public function store(StoreShopRequest $request)
    {
        // ← ここだけ変更：FormRequest の検証結果を取得
        $data = $request->validated();

        // 画像保存
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('shops', 'public');
            $data['image_url'] = $path; // モデルのカラム名に合わせて
        }

        // オーナー紐付け
        $data['owner_id'] = Auth::id();

        Restaurant::create($data);

        return redirect()
            ->route('owner.shops.index')
            ->with('status', '店舗を追加しました。');
    }
}
