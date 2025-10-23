@extends('layouts.auth-layout')
@section('title','予約QRコード')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/reservations/qr.css') }}">
@endsection

@section('content')
<div class="qr-page">
    <div class="card">
        <h1 class="title">来店用QRコード</h1>

        <div class="qr-box">
            {!! $qrSvg !!}
        </div>

        {{-- ★ ここを追加：中身をそのまま表示＆コピー --}}
        <div class="qr-raw">
            <code class="qr-text" id="qrText">{{ $qrString }}</code>
            <button class="btn copy" type="button" id="copyBtn">コピー</button>
        </div>

        <div class="summary">
            <div><span>店舗</span> {{ $reservation->restaurant->name }}</div>
            <div><span>日付</span> {{ optional($reservation->reservation_date)->toDateString() }}</div>
            <div><span>時刻</span> {{ optional($reservation->reservation_time)->format('H:i') }}</div>
            <div><span>人数</span> {{ $reservation->number_of_people }}人</div>
        </div>

        <p class="note">※店舗到着時にこの画面を提示するか、上のコードを店舗側で照合してください。</p>
        <a href="{{ route('mypage') }}" class="btn ghost">マイページへ</a>
    </div>
</div>


<script>
    document.getElementById('copyBtn')?.addEventListener('click', () => {
        const text = document.getElementById('qrText')?.textContent || '';
        if (!text) return;
        navigator.clipboard.writeText(text).then(() => {
            const b = document.getElementById('copyBtn');
            const prev = b.textContent;
            b.textContent = 'コピーしました';
            setTimeout(() => b.textContent = prev, 1200);
        });
    });
</script>
@endsection