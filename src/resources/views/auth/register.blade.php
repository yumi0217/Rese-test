@extends('layouts.auth-layout')

@section('title','Registration')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="card-head">Registration</div>

        <form method="POST" action="{{ url('/register') }}" class="form" novalidate>
            @csrf

            {{-- Username --}}
            <label class="field @error('name') field-error @enderror">
                <span class="icon">
                    <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                        <path fill="currentColor" d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z" />
                    </svg>
                </span>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Username"
                    required
                    @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                @error('name')
                <p class="field-error__msg" id="name-error">{{ $message }}</p>
                @enderror
            </label>

            {{-- Email --}}
            <label class="field @error('email') field-error @enderror">
                <span class="icon">
                    <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                        <path fill="currentColor" d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5L4 8V6l8 5 8-5Z" />
                    </svg>
                </span>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Email"
                    required
                    @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email')
                <p class="field-error__msg" id="email-error">{{ $message }}</p>
                @enderror
            </label>

            {{-- Password --}}
            <label class="field @error('password') field-error @enderror">
                <span class="icon">
                    <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                        <path fill="currentColor" d="M17 8h-1V6a4 4 0 0 0-8 0v2H7a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2Zm-6 0V6a2 2 0 0 1 4 0v2Z" />
                    </svg>
                </span>
                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    required
                    minlength="8"
                    @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                @error('password')
                <p class="field-error__msg" id="password-error">{{ $message }}</p>
                @enderror
            </label>

            <div class="actions">
                <button type="submit" class="btn primary">登録</button>
            </div>
        </form>
    </div>
</div>
@endsection