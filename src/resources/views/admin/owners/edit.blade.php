{{-- resources/views/admin/owners/edit.blade.php --}}
@extends('layouts.admin')
@section('title','店舗代表者を編集')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/owners/create.css') }}">
<style>
    .shops-toolbar {
        display: flex;
        gap: .5rem;
        margin: .5rem 0;
        align-items: center
    }

    .shops-toolbar .spacer {
        flex: 1
    }

    #shop-filter {
        flex: 1;
        min-width: 240px;
        padding: .55rem .7rem;
        border: 1px solid #d1d5db;
        border-radius: .6rem
    }

    .btn-lite {
        padding: .45rem .75rem;
        border: 1px solid #e5e7eb;
        border-radius: .6rem;
        background: #f9fafb
    }

    .shop-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: .6rem
    }

    .shop-card {
        display: flex;
        gap: .6rem;
        align-items: center;
        padding: .65rem .8rem;
        border: 1px solid #e5e7eb;
        border-radius: .7rem;
        background: #fff
    }

    .shop-card.is-hidden {
        display: none
    }

    .shop-badge {
        font-size: .8rem;
        color: #2563eb;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 999px;
        padding: .1rem .5rem
    }

    .shop-name {
        font-weight: 700
    }

    .shop-sub {
        color: #6b7280;
        font-size: .85rem
    }
</style>
@endsection

@section('content')
<div class="page">
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('admin.dashboard') }}" class="crumb">ダッシュボード</a>
        <span class="sep">›</span>
        <span class="crumb current">店舗代表者を編集</span>
    </nav>

    @if (session('status'))
    <div class="alert success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <h2 class="card-title">店舗代表者を編集</h2>

        <form class="owner-form" method="POST" action="{{ route('admin.owners.update', $owner) }}">
            @csrf
            @method('PUT')

            <div class="field">
                <label>氏名</label>
                <input name="name" value="{{ old('name', $owner->name) }}" autocomplete="name" autofocus>
                @error('name')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label>メールアドレス</label>
                <input name="email" value="{{ old('email', $owner->email) }}" autocomplete="email">
                @error('email')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label>パスワード <span class="hint">（変更しない場合は空欄のまま）</span></label>
                <input name="password" type="password" placeholder="8文字以上" autocomplete="new-password">
                @error('password')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label>パスワード（確認）</label>
                <input name="password_confirmation" type="password" placeholder="もう一度入力" autocomplete="new-password">
            </div>

            {{-- ▼ 担当店舗 --------------------------------------------------- --}}
            <div class="field">
                <label>担当店舗</label>

                <div class="shops-toolbar">
                    <input id="shop-filter" type="text" placeholder="店舗名で絞り込み…">
                    <div class="spacer"></div>
                    <button type="button" class="btn-lite" id="shop-select-all">全選択</button>
                    <button type="button" class="btn-lite" id="shop-clear">クリア</button>
                </div>

                <div class="shop-grid" id="shop-grid">
                    @php
                    // 既に紐づいている店舗ID（コントローラから $assignedShopIds で受取）または old()
                    $checked = collect(old('shop_ids', $assignedShopIds ?? []))
                    ->map(fn($v)=>(int)$v)->all();
                    @endphp

                    @foreach($restaurants as $r)
                    <label class="shop-card" data-name="{{ $r->name }}">
                        <input
                            type="checkbox"
                            class="ckb"
                            name="shop_ids[]"
                            value="{{ $r->id }}"
                            {{ in_array($r->id, $checked, true) ? 'checked' : '' }}>
                        <span class="shop-badge">#{{ $r->area->name ?? '-' }}</span>
                        <span class="shop-name">{{ $r->name }}</span>
                        <span class="shop-sub">#{{ $r->genre->name ?? '-' }}</span>
                    </label>

                    @endforeach
                </div>
                @error('shop_ids')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div style="display:flex; gap:10px;">
                <button class="btn-primary" type="submit">更新</button>
                <a href="{{ route('admin.dashboard') }}" class="btn-secondary" style="text-decoration:none;align-self:center;">キャンセル</a>
            </div>
        </form>
    </div>
</div>

<script>
    (() => {
        const filter = document.getElementById('shop-filter');
        const grid = document.getElementById('shop-grid');
        if (!filter || !grid) return;

        const items = Array.from(grid.querySelectorAll('.shop-card'));

        // フィルタ（店舗名で部分一致）
        const doFilter = () => {
            const kw = (filter.value || '').toLowerCase();
            items.forEach(el => {
                const name = (el.dataset.name || '').toLowerCase();
                el.classList.toggle('is-hidden', kw && !name.includes(kw));
            });
        };
        filter.addEventListener('input', doFilter);

        // 表示中のみ 全選択/クリア
        document.getElementById('shop-select-all').addEventListener('click', () => {
            items.filter(el => !el.classList.contains('is-hidden'))
                .forEach(el => {
                    const c = el.querySelector('.ckb');
                    if (c) c.checked = true;
                });
        });
        document.getElementById('shop-clear').addEventListener('click', () => {
            items.filter(el => !el.classList.contains('is-hidden'))
                .forEach(el => {
                    const c = el.querySelector('.ckb');
                    if (c) c.checked = false;
                });
        });
    })();
</script>
@endsection