@extends('layouts.auth-layout')
@section('title', 'メール認証のお願い')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection

@section('content')
<main class="verify-page">
    <section class="verify-card">
        <p class="lead">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        @php
        $mailViewer = app()->environment('local') ? 'http://localhost:8025' : '#';
        @endphp
        <a class="btn-primary" href="{{ $mailViewer }}">認証はこちらから</a>


        <form method="POST" action="{{ route('verification.send') }}" class="resend">
            @csrf
            <button type="submit" class="resend__link">認証メールを再送する</button>
        </form>
    </section>
</main>
@endsection