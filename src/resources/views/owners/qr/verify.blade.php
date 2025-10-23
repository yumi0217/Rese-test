@extends('layouts.owner')
@section('title','QRコード照合')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/owners/qr/verify.css') }}">
@endsection

@section('content')
<div class="verify-page">
    <h1 class="title">QRコード照合</h1>

    <form method="POST" action="{{ route('owners.qr.verify') }}" class="verify-form">
        @csrf
        <label>読み取り結果（スキャナの出力／手入力）</label>
        <input type="text" name="code" placeholder="RSV:123:xxxxxxxx..."
            value="{{ old('code') }}" autofocus
            class="{{ $errors->has('code') ? 'is-invalid' : '' }}">

        @error('code') <p class="error">{{ $message }}</p> @enderror

        <button type="submit" class="btn primary">照合する</button>
    </form>

    @isset($result)
    @if($result === 'ok')
    <div class="result ok">
        <h2>予約情報</h2>
        <ul>
            <li><span>店舗</span>{{ $reservation->restaurant->name }}</li>
            <li><span>利用者</span>{{ $reservation->user->name }}（ID: {{ $reservation->user_id }}）</li>
            <li><span>日付</span>{{ optional($reservation->reservation_date)->toDateString() }}</li>
            <li><span>時刻</span>{{ optional($reservation->reservation_time)->format('H:i') }}</li>
            <li><span>人数</span>{{ $reservation->number_of_people }}人</li>
            <li><span>予約ID</span>{{ $reservation->id }}</li>
        </ul>
    </div>
    @endif
    @endisset
</div>
@endsection