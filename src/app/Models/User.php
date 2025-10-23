<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'email_verified_at',
        'stripe_customer_id',
        'stripe_payment_method',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * このユーザーが管轄する店舗（owner_id がこのユーザー）
     */
    public function restaurants()
    {
        return $this->hasMany(Restaurant::class, 'owner_id');
    }

    /** --------------------------
     *  お気に入り関連（追加分）
     *  -------------------------- */

    /** 中間テーブル favorites そのもの */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /** お気に入り登録した店舗一覧（多対多） */
    public function favoriteRestaurants()
    {
        return $this->belongsToMany(Restaurant::class, 'favorites')
            ->withTimestamps();
    }

    /** 指定店舗をお気に入り済みか判定 */
    public function hasFavoritedRestaurant(Restaurant $restaurant): bool
    {
        return $this->favoriteRestaurants()
            ->where('restaurant_id', $restaurant->id)
            ->exists();
    }
}
