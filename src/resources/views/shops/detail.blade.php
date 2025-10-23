@extends('layouts.auth-layout')
@section('title', $restaurant->name)

@section('styles')
<link rel="stylesheet" href="{{ asset('css/shops/detail.css') }}">
@endsection

@section('content')
<div class="detail">
    <a href="{{ route('shops.index') }}" class="back">
        <img src="{{ asset('images/戻る2ボタン.png') }}" alt="飲食店一覧に戻る" class="back__icon">
        <span class="back__text">{{ $restaurant->name }}</span>
    </a>

    <div class="detail__grid">
        {{-- 左：店舗情報 --}}
        <section class="info">
            @php
            $path = $restaurant->image_url;
            $src = \Illuminate\Support\Facades\Storage::url($path);
            @endphp
            <img class="info__image" src="{{ $src }}" alt="{{ $restaurant->name }}">

            <div class="info__meta">
                <div class="info__tags">
                    <span>#{{ $restaurant->area->name ?? '' }}</span>
                    <span>#{{ $restaurant->genre->name ?? '' }}</span>
                </div>
                <div class="info__desc-card">
                    {!! nl2br(e($restaurant->description)) !!}
                </div>
            </div>
        </section>

        {{-- 右：予約カード --}}
        <section class="reserve card">
            <h2 class="card__title">予約</h2>

            <form class="reserve__form" action="{{ route('reservations.store') }}" method="POST" id="reserve-form">
                @csrf
                <input type="hidden" name="shop_id" value="{{ $restaurant->id }}">
                <input type="hidden" name="shop_name" value="{{ $restaurant->name }}">

                <label class="field">
                    <input type="date" name="date" class="input" id="reserve-date" value="{{ $initial['date'] }}">
                </label>

                <label class="field">
                    <select name="time" class="select" id="reserve-time">
                        @foreach ($timeOptions as $time)
                        {{-- 厳密比較だと弾かれるケースがあるため緩める --}}
                        <option value="{{ $time }}" @selected($time==$initial['time'])>{{ $time }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="field">
                    <select name="number" class="select" id="reserve-number">
                        @foreach ($numbers as $n)
                        <option value="{{ $n }}" @selected($n==$initial['number'])>{{ $n }}人</option>
                        @endforeach
                    </select>
                </label>

                <div class="summary">
                    <div class="row"><span>Shop</span><strong>{{ $restaurant->name }}</strong></div>
                    <div class="row"><span>Date</span><strong id="sum-date">{{ $initial['date'] }}</strong></div>
                    <div class="row"><span>Time</span><strong id="sum-time">{{ $initial['time'] }}</strong></div>
                    <div class="row"><span>Number</span><strong id="sum-number">{{ $initial['number'] }}人</strong></div>
                </div>

                <button type="submit" class="submit">予約する</button>

                @if(!empty($canReview) && !empty($reviewReservationId))
                <div class="review-cta">
                    <a class="btn-review" href="{{ route('reviews.create', $reviewReservationId) }}">
                        このお店をレビューする
                    </a>
                </div>
                @endif
            </form>
        </section>
    </div>
</div>

{{-- サーバ初期値（JSON） --}}
<div id="initial-data"
    data-initial='@json($initial ?? [])'
    data-shop-id='{{ $restaurant->id }}'
    data-user-id='{{ $userId ?? 0 }}'></div>

{{-- 初期表示はサーバ値を“強制反映”。localStorageは保存のみ（復元しない） --}}
<script>
    (function() {
        const holder = document.getElementById('initial-data');
        let init = {};
        try {
            init = JSON.parse(holder.dataset.initial || '{}');
        } catch (_) {}

        const shopId = holder.dataset.shopId;
        const userId = Number(holder.dataset.userId || '0');
        const KEY = `rese:reservation:${userId}:${shopId}`;

        const dateEl = document.getElementById('reserve-date');
        const timeEl = document.getElementById('reserve-time');
        const numEl = document.getElementById('reserve-number');

        const sumDate = document.getElementById('sum-date');
        const sumTime = document.getElementById('sum-time');
        const sumNum = document.getElementById('sum-number');

        // 1) 初期表示：サーバ値を必ず反映（option存在チェック）
        if (init.date) dateEl.value = init.date;
        if (init.time && [...timeEl.options].some(o => o.value === init.time)) timeEl.value = init.time;
        if (init.number && [...numEl.options].some(o => o.value == init.number)) numEl.value = String(init.number);

        // 2) 復元はしない。以後の変更だけ保存する
        const canSave = userId > 0;

        const sync = () => {
            sumDate.textContent = dateEl.value || '—';
            sumTime.textContent = timeEl.value || '—';
            sumNum.textContent = (numEl.value ? numEl.value + '人' : '—');
        };

        const save = () => {
            if (canSave) {
                try {
                    localStorage.setItem(KEY, JSON.stringify({
                        date: dateEl.value,
                        time: timeEl.value,
                        number: numEl.value
                    }));
                } catch (_) {}
            }
            sync();
        };

        ['change', 'input'].forEach(ev => {
            dateEl.addEventListener(ev, save);
            timeEl.addEventListener(ev, save);
            numEl.addEventListener(ev, save);
        });

        document.getElementById('reserve-form')?.addEventListener('submit', save);

        // 初回同期
        sync();
    })();
</script>
@endsection