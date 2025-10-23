@extends('layouts.auth-layout')

@section('title', 'Menu')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/menu/auth.css') }}">
@endsection

@section('content')
<div class="menu">
    <a href="{{ url('/') }}">Home</a>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit">Logout</button>
    </form>
    <a href="{{ route('mypage') }}">Mypage</a>
</div>
@endsection