<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;             // ★ 追加：トークン生成
use App\Models\Reservation;
use App\Models\Restaurant;

class ReservationController extends Controller
{
    public function store(Request $request)
    {
        // 未ログイン処理（そのまま）
        if (!Auth::check()) {
            $shopId = (int) $request->input('shop_id');
            if ($shopId) {
                session()->put('url.intended', route('detail', ['shop_id' => $shopId]));
            }
            session()->put('prefill_reservation', [
                'date'   => $request->input('date'),
                'time'   => $request->input('time'),
                'number' => $request->input('number'),
            ]);
            $request->session()->forget(['errors', '_old_input']);
            return redirect()->route('menu')->with('login_required', '予約にはログインが必要です。');
        }

        // バリデーション（そのまま）
        $data = $request->validate([
            'shop_id' => ['required', 'integer', 'exists:restaurants,id'],
            'date'    => ['required', 'date'],
            'time'    => ['required', 'date_format:H:i'],
            'number'  => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        // ★ ここが肝：qr_token を必ず付与して保存
        $reservation = Reservation::create([
            'user_id'          => Auth::id(),
            'restaurant_id'    => $data['shop_id'],
            'reservation_date' => $data['date'],
            'reservation_time' => $data['time'],
            'number_of_people' => $data['number'],
            'qr_token'         => Str::random(40),   // ← これが無いと SQL 1364 が出る
        ]);

        // 完了画面のサマリー（Stripe用の戻り先にも使う）
        $shop = Restaurant::find($data['shop_id']);
        session()->put('reservation', [
            'id'        => $reservation->id,
            'shop_id'   => $shop->id,
            'shop_name' => $shop->name,
            'date'      => $reservation->reservation_date,
            'time'      => $reservation->reservation_time,
            'number'    => $reservation->number_of_people,
        ]);

        // 予約完了直後にQRへ飛ぶ／リンク出す用にIDを保持したいなら（任意）
        session(['last_reservation_id' => $reservation->id]);

        session()->forget('prefill_reservation');

        return redirect()->route('reservations.complete')->with('success', '予約を受け付けました。');
    }

    public function destroy(Reservation $reservation)
    {
        if ($reservation->user_id !== Auth::id()) abort(403);
        $reservation->delete();
        return redirect()->route('mypage')->with('success', '予約をキャンセルしました。');
    }

    public function complete()
    {
        return view('reservations.complete');
    }

    public function edit(Reservation $reservation)
    {
        if ($reservation->user_id !== Auth::id()) abort(403);
        $reservation->load('restaurant');
        return view('reservations.edit', ['reservation' => $reservation]);
    }

    public function update(Request $request, Reservation $reservation)
    {
        if ($reservation->user_id !== Auth::id()) abort(403);

        $data = $request->validate([
            'date'   => ['required', 'date', 'after_or_equal:today'],
            'time'   => ['required', 'date_format:H:i'],
            'number' => ['required', 'integer', 'min:1', 'max:10'],
        ], [
            'date.after_or_equal' => '本日以降の日付を選択してください。',
            'time.date_format'    => '時刻は HH:MM 形式で入力してください。',
        ]);

        $reservation->update([
            'reservation_date' => $data['date'],
            'reservation_time' => $data['time'],
            'number_of_people' => $data['number'],
        ]);

        return redirect()->route('mypage')->with('success', '予約内容を更新しました。');
    }
}
