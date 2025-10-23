@extends('layouts.auth-layout')
@section('title','レビュー投稿')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/reviews/create.css') }}">
@endsection

@section('content')
<div class="review-create">
    <div class="card">
        <h1 class="title">レビューを投稿</h1>

        <p class="shop">
            店舗：<strong>{{ $reservation->restaurant->name }}</strong><br>
            予約日時：{{ optional($reservation->reservation_date)->toDateString() }}
            {{ optional($reservation->reservation_time)->format('H:i') }}
        </p>

        <form method="POST" action="{{ route('reviews.store') }}" class="form">
            @csrf
            <input type="hidden" name="reservation_id" value="{{ $reservation->id }}">

            <div class="row">
                <label>評価</label>
                <div class="stars">
                    @for($i=5;$i>=1;$i--)
                    <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" @checked(old('rating',5)==$i)>
                    <label for="star{{ $i }}">★</label>
                    @endfor
                </div>
                @error('rating')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="row">
                <label>コメント（任意）</label>
                <textarea name="comment" rows="5" placeholder="良かった点・気になった点など">{{ old('comment') }}</textarea>
                @error('comment')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="actions">
                <a href="{{ route('detail',['shop_id'=>$reservation->restaurant_id]) }}" class="btn ghost">戻る</a>
                <button type="submit" class="btn primary">投稿する</button>
            </div>
        </form>
    </div>
</div>
@endsection