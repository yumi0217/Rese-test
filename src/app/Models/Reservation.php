<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'restaurant_id',
        'reservation_date',
        'reservation_time',
        'number_of_people',
        'qr_token',
    ];

    // DATE は日付としてだけ扱う（時刻が付かない）
    protected $casts = [
        'reservation_date' => 'date',
    ];

    /**
     * reservation_time を安全に Carbon へ変換（読み出し時）
     * - 'H:i:s' でも 'H:i' でもOK
     * - 不正値/空は null
     */
    public function getReservationTimeAttribute($value): ?Carbon
    {
        if (!$value) return null;

        try {
            return Carbon::createFromFormat('H:i:s', $value);
        } catch (\Throwable $e) {
            try {
                return Carbon::createFromFormat('H:i', $value);
            } catch (\Throwable $e2) {
                return null;
            }
        }
    }

    /**
     * reservation_time を 'H:i' に正規化して保存（書き込み時）
     */
    public function setReservationTimeAttribute($value): void
    {
        if (!$value) {
            $this->attributes['reservation_time'] = null;
            return;
        }

        try {
            $t = Carbon::createFromFormat('H:i:s', $value);
        } catch (\Throwable $e) {
            $t = Carbon::createFromFormat('H:i', $value);
        }

        $this->attributes['reservation_time'] = $t->format('H:i');
    }

    /* ===== リレーション ===== */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function review()
    {
        return $this->hasOne(\App\Models\Review::class);
    }

    /* ===== 表示用エイリアス ===== */
    // $reservation->date → "YYYY-MM-DD"
    public function getDateAttribute(): ?string
    {
        return $this->reservation_date?->toDateString();
    }

    // $reservation->time → "HH:MM"
    public function getTimeAttribute(): ?string
    {
        return $this->reservation_time?->format('H:i');
    }

    // $reservation->number → 人数
    public function getNumberAttribute(): ?int
    {
        return $this->number_of_people;
    }
}
