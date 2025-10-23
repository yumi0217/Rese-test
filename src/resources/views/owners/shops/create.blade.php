@extends('layouts.owner')
@section('title','店舗を追加')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/owners/shops/create.css') }}">
@endsection

@section('content')
<div class="page">
    <h1 class="page-title">店舗を追加</h1>

    <nav class="breadcrumb">
        <a href="{{ route('owner.shops.index') }}" class="crumb">店舗一覧</a>
        <span class="sep">›</span>
        <span class="crumb current">新規作成</span>
    </nav>

    <div class="card">
        <form class="shop-form" method="POST" action="{{ route('owner.shops.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="columns">
                {{-- 左：プレビュー＆画像アップロード --}}
                <div class="col media">
                    @php
                    $noimg = asset('images/ノーイメージ.png');
                    @endphp
                    <img id="preview" class="preview" src="{{ $noimg }}" alt="no image">
                    <label class="file">
                        <input type="file" name="image" id="image" accept="image/*">
                        画像を選択
                    </label>
                    @error('image') <p class="error">{{ $message }}</p> @enderror
                </div>

                {{-- 右：基本情報 --}}
                <div class="col fields">
                    <div class="field">
                        <label>店名</label>
                        <input name="name" value="{{ old('name') }}" placeholder="店名を入力">
                        @error('name') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div class="field grid-2">
                        <div>
                            <label>エリア</label>
                            <select name="area_id">
                                <option value="" disabled {{ old('area_id') ? '' : 'selected' }}>選択してください</option>
                                @foreach ($areas as $a)
                                <option value="{{ $a->id }}" {{ old('area_id') == $a->id ? 'selected' : '' }}>
                                    {{ $a->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('area_id') <p class="error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label>ジャンル</label>
                            <select name="genre_id">
                                <option value="" disabled {{ old('genre_id') ? '' : 'selected' }}>選択してください</option>
                                @foreach ($genres as $g)
                                <option value="{{ $g->id }}" {{ old('genre_id') == $g->id ? 'selected' : '' }}>
                                    {{ $g->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('genre_id') <p class="error">{{ $message }}</p> @enderror
                        </div>
                    </div>


                    <div class="field">
                        <label>説明</label>
                        <textarea name="description" rows="8" placeholder="お店の説明">{{ old('description') }}</textarea>
                        @error('description') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div class="actions">
                        <a class="btn ghost" href="{{ route('owner.shops.index') }}">戻る</a>
                        <button class="btn primary" type="submit">登録する</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- 画像プレビュー --}}
<script>
    document.getElementById('image')?.addEventListener('change', (e) => {
        const file = e.target.files?.[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        document.getElementById('preview').src = url;
    });
</script>
@endsection