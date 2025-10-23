@extends('layouts.auth-layout')
@section('title','予約変更')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/reservations/edit.css') }}">
@endsection

@section('content')
<div class="resv-edit">
    <div class="card">
        <h1 class="title">予約内容の変更</h1>

        {{-- 店名のみ表示（画像なし・リンクなし） --}}
        <div class="shop-name">
            店舗：{{ $reservation->restaurant->name ?? '-' }}
        </div>

        <form method="POST" action="{{ route('reservations.update', $reservation) }}" class="form">
            @csrf
            @method('PUT')

            <div class="row">
                <label>日付</label>
                <input type="date" name="date"
                    value="{{ old('date', optional($reservation->reservation_date)->toDateString()) }}"
                    min="{{ now()->toDateString() }}">
                @error('date') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="row">
                <label>時刻</label>
                <input type="time" name="time"
                    value="{{ old('time', optional($reservation->reservation_time)->format('H:i')) }}">
                @error('time') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="row">
                <label>人数</label>
                <input type="number" name="number" min="1" max="10"
                    value="{{ old('number', $reservation->number_of_people) }}">
                @error('number') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="actions">
                <a href="{{ route('mypage') }}" class="btn ghost">戻る</a>
                <button type="submit" class="btn primary">保存する</button>
            </div>
        </form>
    </div>
</div>
@endsection