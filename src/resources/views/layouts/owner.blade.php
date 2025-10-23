{{-- resources/views/layouts/owner.blade.php --}}
<!doctype html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title','Rese Owner')</title>
    <link rel="stylesheet" href="{{ asset('css/owner/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/owner.css') }}">
    @yield('styles')
    @yield('scripts')
</head>

<body>
    <div class="owner-wrap">
        <aside class="aside">
            <div class="brand">
                <span class="brand-icon">🏬</span>
                <span class="brand-title">Owner</span>
            </div>

            <nav class="nav">
                <a href="{{ route('owner.dashboard') }}"
                    class="nav-item {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
                    ダッシュボード
                </a>

                {{-- 店舗メニュー --}}
                <a href="{{ route('owner.shops.index') }}"
                    class="nav-item {{ request()->routeIs('owner.shops.index') ? 'active' : '' }}">
                    店舗編集
                </a>
                <a href="{{ route('owner.shops.create') }}"
                    class="nav-item {{ request()->routeIs('owner.shops.create') ? 'active' : '' }}">
                    店舗追加
                </a>

                {{-- ★ QR照合 --}}
                <a href="{{ route('owners.qr.verify') }}"
                    class="nav-item {{ request()->routeIs('owner.qr.verify') ? 'active' : '' }}">
                    QR照合
                </a>

                <a href="{{ route('notice.create') }}"
                    class="nav-item {{ request()->routeIs('notice.create') ? 'active' : '' }}">
                    お知らせメール
                </a>

                {{-- ★ ここへ移動：QR照合の直下にログアウト --}}
                <form class="logout" method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="nav-item logout-btn" type="submit">ログアウト</button>
                </form>

            </nav>
        </aside>

        <main class="main">
            @yield('content')
        </main>
    </div>
</body>

</html>