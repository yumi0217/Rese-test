@extends('layouts.auth-layout')
@section('title','予約完了')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/reservations/complete.css') }}">
@endsection

@section('content')
<div class="complete">
    <div class="card">
        <p class="msg">ご予約ありがとうございます</p>

        @if (session()->has('reservation'))
        @php
        // 予約情報（配列で想定）
        $r = (array) session('reservation');

        // 店舗ID（detail に戻るため）
        $shopId = $r['shop_id'] ?? $r['restaurant_id'] ?? null;
        $returnUrl = $shopId
        ? route('detail', ['shop_id' => $shopId])
        : route('shops.index');

        // 店名
        $shopName = $r['shop_name'] ?? $r['restaurant_name'] ?? '-';

        // 日付
        $rawDate = $r['date'] ?? $r['reservation_date'] ?? null;
        $date = $rawDate
        ? \Illuminate\Support\Carbon::parse($rawDate)->toDateString()
        : '-';

        // 時刻（"H:i" に正規化）
        $rawTime = $r['time'] ?? $r['reservation_time'] ?? null;
        if ($rawTime) {
        $time = preg_match('/^\d{2}:\d{2}$/', (string)$rawTime)
        ? (string)$rawTime
        : \Illuminate\Support\Carbon::parse($rawTime)->format('H:i');
        } else {
        $time = '-';
        }

        // 人数
        $number = $r['number'] ?? $r['number_of_people'] ?? '-';
        @endphp

        <div class="summary">
            <div class="row"><span>Shop</span><strong>{{ $shopName }}</strong></div>
            <div class="row"><span>Date</span><strong>{{ $date }}</strong></div>
            <div class="row"><span>Time</span><strong>{{ $time }}</strong></div>
            <div class="row"><span>Number</span><strong>{{ $number }}人</strong></div>
        </div>

        <div class="actions">
            <a href="{{ route('shops.index') }}" class="btn ghost">戻る</a>

            @if (!empty($r['id']))
            {{-- Stripe（カード登録/決済）へ：/payments/checkout/{reservation}?return=... --}}
            <a class="btn primary"
                href="{{ route('payments.checkout', ['reservation' => $r['id']]) }}?return={{ urlencode($returnUrl) }}">
                Stripeへ
            </a>
            @endif
        </div>
        @else
        <div class="actions">
            <a href="{{ route('shops.index') }}" class="btn ghost">戻る</a>
        </div>
        @endif
    </div>
</div>
@endsection