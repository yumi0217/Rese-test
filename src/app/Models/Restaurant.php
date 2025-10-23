<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;

    // 複数代入可能なカラム
    protected $fillable = [
        'owner_id',       // ★ 追加：管轄する店舗代表者
        'name',
        'description',
        'area_id',
        'genre_id',
        'image_url',      // ★ 変更：migrationに合わせて image_url を使用
    ];

    /**
     * リレーション
     */

    // 店舗代表者（users.owner_id）
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function genre()
    {
        return $this->belongsTo(Genre::class);
    }

    // 中間テーブル favorites（1対多：レコード自体）
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    // この店舗をお気に入りしているユーザー一覧（多対多）
    public function favoredByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites')
            ->withTimestamps();
    }

    public function reservations()
    {
        // ★ 外部キーを明示（reservations.restaurant_id）
        return $this->hasMany(Reservation::class, 'restaurant_id');
    }

    public function reviews()
    {
        return $this->hasMany(\App\Models\Review::class);
    }


    /**
     * 便利関数：指定ユーザーがお気に入り済みか
     */
    public function isFavoritedBy(?User $user): bool
    {
        if (!$user) return false;
        return $this->favoredByUsers()->where('user_id', $user->id)->exists();
    }
}
