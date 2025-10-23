{{-- resources/views/owners/dashboard.blade.php --}}
@extends('layouts.owner')
@section('title','ダッシュボード')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/owners/dashboard.css') }}">
<style>
    /* ちょい足し（星とレビューカード） */
    .stars {
        display: inline-flex;
        gap: 2px;
        vertical-align: middle
    }

    .star {
        font-size: 14px;
        color: #fbbf24
    }

    /* amber-400 */
    .star.off {
        color: #e5e7eb
    }

    /* gray-200 */
    .review-list {
        display: grid;
        gap: 12px
    }

    .review-item {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 12px;
        background: #fff
    }

    .review-head {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center
    }

    .review-meta {
        color: #6b7280;
        font-size: 12px
    }

    .avg-chip {
        font-weight: 700
    }

    .table-sm th,
    .table-sm td {
        padding: .55rem .7rem
    }
</style>
@endsection

@section('content')
<div class="page">
    <h1 class="page-title">ダッシュボード</h1>

    {{-- KPI（代表者名 / 今日の予約 / 総予約） --}}
    <div class="card owner-header">
        <div class="owner-field">
            <div class="owner-field-label">店舗代表者名</div>
            <div class="owner-field-value">{{ $owner->name }}</div>
        </div>
        <div class="owner-meta">
            <div class="metric">
                <div class="metric-label">本日の予約</div>
                <div class="metric-value">{{ $todayCount }}</div>
            </div>
            <div class="metric">
                <div class="metric-label">総予約</div>
                <div class="metric-value">{{ $totalCount }}</div>
            </div>
        </div>
    </div>

    {{-- 予約一覧（店舗名追加＋フィルタ） --}}
    <div class="card">
        {{-- タイトル＋QR照合ショートカット --}}
        <div class="card-title-row" style="display:flex;align-items:center;gap:12px;justify-content:space-between;">
            <h2 class="card-title" style="margin:0;">予約一覧</h2>
            <a href="{{ route('owners.qr.verify') }}" class="btn primary small">QR照合</a>
        </div>

        {{-- フィルタ --}}
        <form method="GET" action="{{ route('owner.dashboard') }}" class="resv-filter">
            <label>日付
                <input type="date" name="date_from" value="{{ $dateFrom }}">
                〜
                <input type="date" name="date_to" value="{{ $dateTo }}">
            </label>
            <label>時刻
                <input type="time" name="time_from" value="{{ $timeFrom }}">
                〜
                <input type="time" name="time_to" value="{{ $timeTo }}">
            </label>
            <button type="submit" class="btn small">絞り込む</button>
            <a class="btn text" href="{{ route('owner.dashboard') }}">リセット</a>
        </form>

        @if($reservations->isEmpty())
        <p class="muted">予約はまだありません。</p>
        @else
        <table class="resv-table">
            <thead>
                <tr>
                    <th>店舗名</th>
                    <th>名前</th>
                    <th>日付</th>
                    <th>時刻</th>
                    <th>人数</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reservations as $r)
                <tr>
                    <td>{{ $r->restaurant->name ?? '—' }}</td>
                    <td>{{ $r->user->name ?? '—' }}</td>
                    <td>{{ optional($r->reservation_date)->format('Y-m-d') }}</td>
                    <td>{{ optional($r->reservation_time)->format('H:i') }}</td>
                    <td>{{ $r->number_of_people ?? '—' }}人</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pager">
            {{ $reservations->links() }}
        </div>
        @endif
    </div>

    {{-- ★ 追加：レビュー概要（店舗ごとの平均/件数） --}}
    <div class="card collapsible is-collapsed" id="reviews-summary">
        <div class="card-title-row" style="display:flex;align-items:center;gap:12px;justify-content:space-between;">
            <h2 class="card-title" style="margin:0;">レビュー概要</h2>
            <button type="button"
                class="btn small text toggle-collapsible"
                aria-expanded="false"
                aria-controls="reviews-summary-body">
                もっと見る
            </button>
        </div>

        @if($restaurants->isEmpty())
        <p class="muted">レビューはまだありません。</p>
        @else
        <div id="reviews-summary-body">
            <table class="resv-table table-sm collapse-target">
                <thead>
                    <tr>
                        <th>店舗名</th>
                        <th>平均評価</th>
                        <th>件数</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($restaurants as $shop)
                    @php
                    $avg = round(($shop->reviews_avg_rating ?? 0), 1);
                    $stars = (int)round($avg);
                    @endphp
                    <tr>
                        <td>{{ $shop->name }}</td>
                        <td>
                            <span class="avg-chip">{{ number_format($avg,1) }}</span>
                            <span class="stars" aria-label="平均 {{ $avg }} / 5">
                                @for($i=1;$i<=5;$i++)
                                    <span class="star {{ $i <= $stars ? '' : 'off' }}">★</span>
                            @endfor
                            </span>
                        </td>
                        <td>{{ $shop->reviews_count }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>


    {{-- ★ 追加：最新レビュー（直近10件） --}}
    <div class="card collapsible is-collapsed" id="latest-reviews">
        <div class="card-title-row" style="display:flex;align-items:center;gap:12px;justify-content:space-between;">
            <h2 class="card-title" style="margin:0;">レビュー内容</h2>

            {{-- トグルボタン --}}
            <button type="button" class="btn small text toggle-collapsible" aria-expanded="false" aria-controls="latest-reviews-body">
                もっと見る
            </button>
        </div>

        @if($latestReviews->isEmpty())
        <p class="muted">最新レビューはありません。</p>
        @else
        <div class="review-list-wrap" id="latest-reviews-body">
            <div class="review-list">
                @foreach($latestReviews as $rev)
                <div class="review-item">
                    <div class="review-head">
                        <strong>{{ $rev->restaurant->name }}</strong>
                        <span class="stars" aria-label="評価 {{ $rev->rating }} / 5">
                            @for($i=1;$i<=5;$i++)
                                <span class="star {{ $i <= $rev->rating ? '' : 'off' }}">★</span>
                        @endfor
                        </span>
                        <span class="review-meta">by {{ $rev->user->name ?? '—' }}・{{ $rev->created_at->format('Y-m-d') }}</span>
                    </div>
                    @if($rev->comment)
                    <div class="review-body" style="margin-top:6px;white-space:pre-wrap;">
                        {{ $rev->comment }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- フェード（閉じている時だけ表示） --}}
            <div class="fade-mask" aria-hidden="true"></div>
        </div>
        @endif
    </div>
</div>


@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {

        // 汎用：折り畳み初期化
        function initCollapsible(box, getItems, threshold) {
            if (!box) return;
            const btn = box.querySelector('.toggle-collapsible');
            const items = getItems(box);

            // 要素数が閾値以下ならボタン不要＆常時全表示
            if (!items || items.length <= threshold) {
                if (btn) {
                    btn.style.display = 'none';
                    btn.setAttribute('aria-expanded', 'true');
                }
                box.classList.remove('is-collapsed');
                const mask = box.querySelector('.fade-mask');
                if (mask) mask.style.display = 'none';
                return;
            }

            // 初期状態（.is-collapsed の有無で決定）
            const setState = (expanded) => {
                if (expanded) {
                    box.classList.remove('is-collapsed');
                    if (btn) {
                        btn.setAttribute('aria-expanded', 'true');
                        btn.textContent = '閉じる';
                    }
                } else {
                    box.classList.add('is-collapsed');
                    if (btn) {
                        btn.setAttribute('aria-expanded', 'false');
                        btn.textContent = 'もっと見る';
                    }
                    const body = document.getElementById(btn?.getAttribute('aria-controls') || '');
                    if (body) body.scrollIntoView({
                        block: 'nearest'
                    });
                }
            };
            setState(!box.classList.contains('is-collapsed') ? true : false);

            // クリックで開閉
            if (btn) {
                btn.addEventListener('click', () => {
                    const expanded = btn.getAttribute('aria-expanded') === 'true';
                    setState(!expanded);
                });
            }
        }

        // ① 最新レビュー（最初の2件だけ表示）
        initCollapsible(
            document.getElementById('latest-reviews'),
            (box) => Array.from(box.querySelectorAll('.review-list .review-item')),
            2
        );

        // ② レビュー概要テーブル（最初の4行だけ表示）
        initCollapsible(
            document.getElementById('reviews-summary'),
            (box) => Array.from(box.querySelectorAll('.collapse-target tbody tr')),
            4
        );

    });
</script>
@endsection