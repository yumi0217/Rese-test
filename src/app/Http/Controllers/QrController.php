<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use App\Http\Requests\Owner\QrVerifyRequest;

class QrController extends Controller
{
    /** 利用者：自分の予約のQR表示（QRの中身をURLに） */
    public function show(Reservation $reservation)
    {
        abort_unless($reservation->user_id === Auth::id(), 403);

        $qrPayload = sprintf('RSV:%d:%s', $reservation->id, $reservation->qr_token);

        // ★ ここを owners.* に
        $qrUrl = route('owners.qr.verify.get', ['code' => $qrPayload], true);

        $renderer = new ImageRenderer(new RendererStyle(220, 1), new SvgImageBackEnd());
        $writer   = new Writer($renderer);
        $qrSvg    = $writer->writeString($qrUrl);

        return view('reservations.qr', [
            'reservation' => $reservation->load('restaurant'),
            'qrSvg'       => $qrSvg,
            'qrString'    => $qrUrl,
        ]);
    }

    public function verifyForm()
    {
        return view('owners.qr.verify');
    }

    /** QrVerifyRequest と同じ仕様に合わせる（= 許可・桁数ゆるめ） */
    protected function parseAndFind(string $code, int $ownerId): ?Reservation
    {
        if (!preg_match('/^RSV:(\d+):([A-Za-z0-9_\-=]{20,128})$/', $code, $m)) {
            return null;
        }
        [, $id, $token] = $m;

        return Reservation::with(['restaurant', 'user'])
            ->where('id', $id)
            ->where('qr_token', $token)
            ->whereHas('restaurant', fn($q) => $q->where('owner_id', $ownerId))
            ->first();
    }

    /** 店舗側：照合（GET：QRから直アクセス） */
    public function verifyGet(Request $request)
    {
        $ownerId = Auth::id(); // guard('owner') は未定義なので使わない
        if (!$ownerId) {
            return redirect()->route('login')->with('status', 'ログインが必要です。');
        }

        // URLでもRSVでもOKに
        $code = trim((string) $request->query('code', ''));
        if ($code === '' || !str_starts_with($code, 'RSV:')) {
            $full = $request->fullUrl();
            if (preg_match('/[?&]code=([^&]+)/', $full, $m)) {
                $code = urldecode($m[1]);
            }
        }

        $reservation = $this->parseAndFind($code, $ownerId);
        if (!$reservation) {
            return view('owners.qr.verify', [
                'result'  => 'ng',
                'error'   => 'QRコードの形式が不正か、予約が見つかりません。',
                'prefill' => $code,
            ]);
        }

        return view('owners.qr.verify', [
            'result'      => 'ok',
            'reservation' => $reservation,
            'prefill'     => $code,
        ]);
    }

    /** 店舗側：照合（POST：手入力/貼り付け用） */
    public function verify(QrVerifyRequest $request)
    {
        // QrVerifyRequestで URL→RSV 正規化済み
        $code    = trim($request->validated()['code']);
        $ownerId = Auth::id();

        $reservation = $this->parseAndFind($code, $ownerId);
        if (!$reservation) {
            return back()
                ->withErrors(['code' => '予約が見つからないか、権限がありません。'])
                ->withInput();
        }

        return view('owners.qr.verify', [
            'result'      => 'ok',
            'reservation' => $reservation,
        ]);
    }
}
