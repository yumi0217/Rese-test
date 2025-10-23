<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\Reservation;

class QrReservationController extends Controller
{
    public function verify(Request $request)
    {
        // 1) 入力ガード（空文字や空白でのアクセスを早期リターン）
        $token = trim((string) $request->query('t', ''));
        if ($token === '') {
            return response('MISSING_TOKEN', 400);
        }

        // 2) ワンタイム取り出し（2回目以降は無効）
        $reservationId = Cache::pull("qr:rsv:{$token}");
        if (!$reservationId) {
            return response('INVALID_OR_EXPIRED', 410);
        }

        // 3) 予約＋店舗の取得
        $reservation = Reservation::with('restaurant')->find($reservationId);
        if (!$reservation) {
            return response('NOT_FOUND', 404);
        }

        // 4) オーナー認証（未ログイン → ログイン後に戻す）
        if (!Auth::guard('owner')->check()) {
            return redirect()->guest(
                route('owner.login', ['redirect' => $request->fullUrl()])
            );
        }

        // 5) 権限チェック（自店舗のみ）
        $owner = Auth::guard('owner')->user();
        if ((int)$owner->id !== (int)$reservation->restaurant->owner_id) {
            return response('FORBIDDEN', 403);
        }

        // 6) 既存のオーナー用「予約詳細」へ
        return redirect()->route('owners.reservations.show', $reservation->id);
    }
}
