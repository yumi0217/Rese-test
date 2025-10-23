<!doctype html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title','Admin')</title>
    <link rel="stylesheet" href="{{ asset('css/layouts/admin.css') }}">
    @yield('styles')
</head>

<body>
    <div class="admin-wrap">
        <aside class="aside">
            <div class="brand">🛡️ Admin</div>

            <nav class="nav">
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    ダッシュボード
                </a>

                {{-- 店舗代表者作成（画面がまだなら後で有効化） --}}
                <a href="{{ url('/admin/owners/create') }}"
                    class="nav-item {{ request()->is('admin/owners/create') ? 'active' : '' }}">
                    店舗代表者を作成
                </a>
            </nav>

            <form class="logout" method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item logout-btn">ログアウト</button>
            </form>
        </aside>

        <main class="main">
            @yield('content')
        </main>
    </div>
</body>

</html>