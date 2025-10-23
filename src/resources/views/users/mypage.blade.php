@extends('layouts.auth-layout')

@section('title', 'マイページ')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/users/mypage.css') }}">
@endsection

@section('content')
<div class="mypage">
    <div class="mypage__head">
        <h1 class="mypage__title">{{ $user->name }}さん</h1>
    </div>

    <div class="mypage__grid">
        {{-- 左：予約状況 --}}
        <section class="mypage__left">
            <h2 class="section-title">予約状況</h2>

            @forelse($reservations as $reservation)
            <article class="reserve-card">
                <header class="reserve-card__head">
                    <div class="reserve-card__icon">🕒</div>
                    <div class="reserve-card__title">予約{{ $loop->iteration }}</div>

                    @if (\Illuminate\Support\Facades\Route::has('reservations.destroy'))
                    <form class="reserve-card__close"
                        method="POST"
                        action="{{ route('reservations.destroy', $reservation->id) }}"
                        onsubmit="return confirm('この予約をキャンセルしますか？');">
                        @csrf @method('DELETE')
                        <button type="submit" aria-label="予約をキャンセル">✕</button>
                    </form>
                    @else
                    <div class="reserve-card__close reserve-card__close--dummy" title="キャンセル機能は未設定">✕</div>
                    @endif
                </header>

                <dl class="reserve-card__list">
                    <div class="row">
                        <dt>Shop</dt>
                        <dd>{{ $reservation->restaurant->name ?? '-' }}</dd>
                    </div>
                    <div class="row">
                        <dt>Date</dt>
                        <dd>{{ \Carbon\Carbon::parse($reservation->reservation_date)->toDateString() }}</dd>
                    </div>
                    <div class="row">
                        <dt>Time</dt>
                        <dd>{{ $reservation->reservation_time }}</dd>
                    </div>
                    <div class="row">
                        <dt>Number</dt>
                        <dd>{{ $reservation->number_of_people }}人</dd>
                    </div>
                </dl>

                {{-- ▼ 追加：アクション（QR表示／予約変更） --}}
                <div class="reserve-card__actions">
                    <a class="btn secondary" href="{{ route('reservations.qr.show', $reservation) }}">
                        QRを表示
                    </a>
                    <a class="btn primary" href="{{ route('reservations.edit', $reservation) }}">
                        予約を変更する
                    </a>
                </div>
                {{-- ▲ ここまで --}}
            </article>
            @empty
            <p class="empty">現在、予約はありません。</p>
            @endforelse
        </section>

        {{-- 右：お気に入り店舗 --}}
        <section class="mypage__right">
            <h2 class="section-title">お気に入り店舗</h2>

            @if($favoriteRestaurants->isEmpty())
            <p class="empty">まだお気に入りがありません。</p>
            @else
            <div class="fav-grid">
                @foreach($favoriteRestaurants as $shop)
                <div class="shop-card">
                    <a href="{{ route('detail', ['shop_id' => $shop->id]) }}" class="thumb">
                        <img src="{{ $shop->image_url ? Storage::url($shop->image_url) : asset('images/noimage.png') }}"
                            alt="{{ $shop->name }}">
                    </a>

                    <div class="body">
                        <div class="title">{{ $shop->name }}</div>
                        <div class="meta">
                            <span>#{{ $shop->area->name ?? '' }}</span>
                            <span>#{{ $shop->genre->name ?? '' }}</span>
                        </div>

                        <div class="actions">
                            <a href="{{ route('detail', ['shop_id' => $shop->id]) }}" class="btn primary">詳しくみる</a>
                            <form action="{{ route('favorites.toggle') }}" method="POST"
                                onsubmit="return confirm('お気に入りを解除しますか？');" style="display:inline">
                                @csrf
                                <input type="hidden" name="restaurant_id" value="{{ $shop->id }}">
                                <button type="submit" class="heart on" aria-pressed="true" aria-label="お気に入り解除">❤</button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </section>
    </div>
</div>
@endsection