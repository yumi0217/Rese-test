@extends('layouts.auth-layout')

@section('title','Login')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
<style>
    /* 追加：タブ＆色テーマ */
    .role-tabs {
        display: flex;
        gap: 6px;
        padding: 10px 12px;
        background: #fff;
        border-bottom: 1px solid #e5e7eb
    }

    .role-tab {
        flex: 1;
        padding: 8px 10px;
        border: 1px solid #e5e7eb;
        background: #f8fafc;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600
    }

    .role-tab.is-active {
        color: #fff;
        border-color: transparent
    }

    .auth-card.role-user .card-head {
        background: #2962ff;
    }

    .auth-card.role-owner .card-head {
        background: #10b981;
    }

    .auth-card.role-admin .card-head {
        background: #111827;
    }

    .auth-card.role-user .role-tab.is-active {
        background: #2962ff;
    }

    .auth-card.role-owner .role-tab.is-active {
        background: #10b981;
    }

    .auth-card.role-admin .role-tab.is-active {
        background: #111827;
    }
</style>
@endsection

@section('content')
<div class="auth-wrap">
    {{-- 初期は user テーマ --}}
    <div class="auth-card role-user">

        {{-- ▼ 追加：役割タブ --}}
        <div class="role-tabs" role="tablist" aria-label="Login Role Tabs">
            <button type="button" class="role-tab is-active" data-role="user">User</button>
            <button type="button" class="role-tab" data-role="owner">Owner</button>
            <button type="button" class="role-tab" data-role="admin">Admin</button>
        </div>

        <div class="card-head">Login</div>

        {{-- ★ HTML5の自動バリデーションを無効化 --}}
        <form method="POST" action="{{ route('login') }}" class="form" novalidate>
            @csrf
            {{-- ▼ 追加：選択ロールを送る --}}
            <input type="hidden" name="expected_role" id="expected_role" value="user">

            <label class="field">
                <span class="icon">
                    <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                        <path fill="currentColor" d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5L4 8V6l8 5 8-5Z" />
                    </svg>
                </span>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" autofocus autocomplete="username">
            </label>
            @error('email') <p class="field-error">{{ $message }}</p> @enderror

            <label class="field">
                <span class="icon">
                    <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                        <path fill="currentColor" d="M17 8h-1V6a4 4 0 0 0-8 0v2H7a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2Zm-6 0V6a2 2 0 0 1 4 0v2Z" />
                    </svg>
                </span>
                <input type="password" name="password" placeholder="Password" autocomplete="current-password">
            </label>
            @error('password') <p class="field-error">{{ $message }}</p> @enderror

            <div class="actions">
                <button type="submit" class="btn primary">ログイン</button>
            </div>
        </form>
    </div>
</div>

{{-- 追加：タブ切替JS（数行） --}}
<script>
    const tabs = document.querySelectorAll('.role-tab');
    const card = document.querySelector('.auth-card');
    const hidden = document.getElementById('expected_role');
    tabs.forEach(btn => btn.addEventListener('click', () => {
        tabs.forEach(b => b.classList.remove('is-active'));
        btn.classList.add('is-active');
        const role = btn.dataset.role; // user | owner | admin
        hidden.value = role;
        card.classList.remove('role-user', 'role-owner', 'role-admin');
        card.classList.add('role-' + role);
    }));
</script>
@endsection