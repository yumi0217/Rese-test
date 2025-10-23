@extends('layouts.owner')
@section('title','店舗編集')

@section('styles')
@php
$cssRel = 'css/owners/shops/index.css'; // public/ 以下の相対
$cssAbs = public_path($cssRel); // 絶対パス
$ver = file_exists($cssAbs) ? filemtime($cssAbs) : time(); // 無ければ暫定値
@endphp
<link rel="stylesheet" href="{{ asset($cssRel) }}?v={{ $ver }}">
@endsection

@section('content')
<div class="shops-page">
    <h1 class="page-title">店舗編集</h1>

    @if(session('status'))
    <div class="alert success">{{ session('status') }}</div>
    @endif

    <div class="grid">
        @forelse($shops as $shop)
        <div class="card">
            <a href="{{ route('owner.shops.edit', $shop) }}" class="thumb" aria-label="{{ $shop->name }}を編集">
                <img src="{{ $shop->image_url ? Storage::url($shop->image_url) : asset('images/noimage.png') }}"
                    alt="{{ $shop->name }}">
            </a>
            <div class="body">
                <div class="title">{{ $shop->name }}</div>
                <div class="meta">
                    <span>#{{ $shop->area->name ?? '-' }}</span>
                    <span>#{{ $shop->genre->name ?? '-' }}</span>
                </div>
                <div class="actions">
                    <a href="{{ route('owner.shops.edit', $shop) }}" class="btn primary">編集</a>
                </div>
            </div>
        </div>
        @empty
        <p class="empty">あなたに割り当てられている店舗はありません。</p>
        @endforelse
    </div>

    <div class="pagination-wrap">
        {{ $shops->withQueryString()->links('vendor.pagination.cute') }}
    </div>
</div>
@endsection