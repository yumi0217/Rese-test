<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title','Rese')</title>
    <link rel="stylesheet" href="{{ asset('css/layouts/menu.css') }}">
    @yield('styles')
</head>

<body>
    <header class="menu-header">
        <a href="{{ url('/') }}">
            <img src="{{ asset('images/戻るボタン.png') }}" alt="閉じる" class="menu-close">
        </a>
    </header>

    <main class="site-main">
        @yield('content')
    </main>
</body>

</html>