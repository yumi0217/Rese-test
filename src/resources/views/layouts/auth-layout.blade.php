<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Rese')</title>

    {{-- ★ これが必須（AJAX の POST に使う） --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('css/layouts/app.css') }}">
    @yield('styles')
</head>

<body>
    <header class="site-header">
        <div class="header-inner">
            <a href="{{ url('menu') }}" class="logo">
                <img src="{{ asset('images/rogo.png') }}" alt="Rese">
            </a>
        </div>
    </header>

    <main class="site-main">
        @yield('content')
    </main>

    {{-- ★ ページ末尾でJSを読み込む --}}
    @yield('scripts')
</body>

</html>