@extends('layouts.menu')

@section('title', 'Menu')

{{-- guest.css を読み込み --}}
@section('styles')
<link rel="stylesheet" href="{{ asset('css/menu/guest.css') }}">
@endsection

@section('content')
<div class="menu">
    <a href="{{ url('/') }}">Home</a>
    <a href="{{ route('register') }}">Registration</a>
    <a href="{{ route('login') }}">Login</a>
</div>
@endsection