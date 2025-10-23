@extends('layouts.auth-layout')
@section('title','会員登録完了')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/auth/complete.css') }}">
@endsection

@section('content')
<main class="complete-wrap">
    <section class="card">
        <p class="lead">会員登録ありがとうございます</p>
        <a href="{{ route('login') }}" class="btn-primary">ログインする</a>
    </section>
</main>
@endsection