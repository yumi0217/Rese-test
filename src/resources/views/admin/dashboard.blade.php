{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'ダッシュボード')
@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
@endsection

@section('content')
<div class="page dashboard">
    <h1 class="page-title">ダッシュボード</h1>

    @if (session('status'))
    <div class="alert success">{{ session('status') }}</div>
    @endif
    @if (session('generated_password'))
    <div class="alert info">自動生成パスワード：<code>{{ session('generated_password') }}</code></div>
    @endif

    {{-- 代表者がいない場合 --}}
    @if ($owners->isEmpty())
    <div class="card empty-box">
        <p class="muted center">店舗代表者はまだ作成されておりません</p>
    </div>
    @else
    {{-- 代表者一覧（複数） --}}
    <div class="owner-list">
        @foreach ($owners as $o)
        <div class="owner-card">
            <div>
                <div class="owner-tag">店舗代表者</div>
                <div class="owner-name">{{ $o->name }}</div>
                <div class="owner-email">{{ $o->email }}</div>
            </div>

            {{-- 編集リンク（ルートがある場合のみ表示） --}}
            @if (Route::has('admin.owners.edit'))
            <a class="icon-button" href="{{ route('admin.owners.edit', $o) }}" aria-label="編集">✏</a>
            @endif
        </div>
        @endforeach
    </div>

    {{-- ページネーション --}}
    <div class="pager" style="margin-top:16px;">
        {{ $owners->links() }}
    </div>
    @endif
</div>
@endsection