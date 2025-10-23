@extends('layouts.owner')
@section('title','お知らせメール送信')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/owners/notice/create.css') }}">
@endsection

@section('content')
<div class="page notice-wrap">
    <div class="card">
        <h2 class="card-title">お知らせメール送信</h2>

        @if (session('status')) <p class="muted">{{ session('status') }}</p> @endif

        <form method="POST" action="{{ route('notice.send') }}">
            @csrf

            <div class="field">
                <label>件名</label>
                <input
                    type="text"
                    name="subject"
                    value="{{ old('subject') }}"
                    class="input @error('subject') is-invalid @enderror">
                @error('subject') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label>本文</label>
                <textarea
                    name="body"
                    rows="8"
                    class="textarea @error('body') is-invalid @enderror">{{ old('body') }}</textarea>
                @error('body') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label>送信先（利用者）</label>

                @php
                $oldIds = collect(old('to_user_ids', []))->map(fn($v)=>(int)$v)->all();
                @endphp

                <div class="recipient-box
                    {{ $errors->has('to_user_ids') || $errors->has('to_user_ids.*') ? 'is-invalid' : '' }}">
                    @forelse($users as $u)
                    <label class="recipient-row">
                        <input
                            type="checkbox"
                            name="to_user_ids[]"
                            value="{{ $u->id }}"
                            {{ in_array($u->id, $oldIds, true) ? 'checked' : '' }}>
                        <span class="recipient-name">{{ $u->name }}</span>
                        <span class="recipient-email">({{ $u->email }})</span>
                    </label>
                    @empty
                    <p class="muted">対象ユーザーがいません。</p>
                    @endforelse
                </div>

                @error('to_user_ids') <div class="error">{{ $message }}</div> @enderror
                @error('to_user_ids.*') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="action-row sticky-actions">
                <button class="btn primary" type="submit">送信する</button>
            </div>
        </form>
    </div>
</div>
@endsection