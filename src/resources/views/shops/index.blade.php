@extends('layouts.app')

@section('title', '飲食店一覧')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/shops/index.css') }}">
@endsection

@section('content')
<div class="shops-page">
    <div class="grid">
        @forelse($shops as $shop)
        <div class="card">
            <a href="{{ url('detail/'.$shop->id) }}" class="thumb">
                <img src="{{ $shop->image_url ? Storage::url($shop->image_url) : asset('images/noimage.png') }}"
                    alt="{{ $shop->name }}">
            </a>

            <div class="body">
                <div class="title">{{ $shop->name }}</div>
                <div class="meta">
                    <span>#{{ $shop->area->name }}</span>
                    <span>#{{ $shop->genre->name }}</span>
                </div>

                <div class="actions">
                    <a href="{{ url('detail/'.$shop->id) }}" class="btn primary">詳しくみる</a>

                    @auth
                    @php
                    $isFav = isset($shop->is_favorite)
                    ? (bool)$shop->is_favorite
                    : $shop->favorites->where('user_id', auth()->id())->isNotEmpty();
                    @endphp
                    <form action="{{ route('favorites.toggle') }}" method="POST" style="display:inline">
                        @csrf
                        <input type="hidden" name="restaurant_id" value="{{ $shop->id }}">
                        <button type="submit"
                            class="heart {{ $isFav ? 'on' : '' }}"
                            aria-pressed="{{ $isFav ? 'true' : 'false' }}"
                            aria-label="お気に入りに追加/解除">❤</button>
                    </form>
                    @endauth

                    @guest
                    <button type="button" class="heart is-disabled" aria-disabled="true"
                        title="ログインでお気に入り機能が使えます" disabled>❤</button>
                    @endguest
                </div>
            </div>
        </div>
        @empty
        <p class="empty">該当する店舗はありません。</p>
        @endforelse
    </div>

    {{-- ★ 追加：ページネーション（検索条件を引き継ぐ） --}}
    {{-- ▼ ページネーション＆件数表示をまとめてレイアウト --}}
    <div class="pager">
        <div class="pager-info">
            Showing <strong>{{ $shops->firstItem() }}</strong>–<strong>{{ $shops->lastItem() }}</strong>
            of <strong>{{ $shops->total() }}</strong> results
        </div>

        {{-- rese テンプレを使う（作ってあるならそのまま） --}}
        {{ $shops->withQueryString()->onEachSide(1)->links('vendor.pagination.cute') }}
    </div>

</div>
@endsection