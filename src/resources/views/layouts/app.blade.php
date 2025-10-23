<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title','Rese')</title>
    <link rel="stylesheet" href="{{ asset('css/layouts/app.css') }}">
    @yield('styles')
</head>

<body>
    <header class="site-header">
        <div class="header-inner">
            <a href="{{ route('menu') }}" class="logo">
                <img src="{{ asset('images/rogo.png') }}" alt="Rese">
            </a>


            {{-- ★ ここに header-search を必ず付ける --}}
            <form method="GET" action="{{ url('/') }}" class="header-search filter-pill">
                <select name="area" class="pill-select">
                    <option value="">All area</option>
                    @foreach(($areas ?? []) as $area)
                    <option value="{{ $area->id }}" {{ request('area')==$area->id?'selected':'' }}>{{ $area->name }}</option>
                    @endforeach
                </select>

                <select name="genre" class="pill-select">
                    <option value="">All genre</option>
                    @foreach(($genres ?? []) as $genre)
                    <option value="{{ $genre->id }}" {{ request('genre')==$genre->id?'selected':'' }}>{{ $genre->name }}</option>
                    @endforeach
                </select>

                <div class="pill-search">
                    <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Search ..." />
                    <button type="submit" class="pill-btn">🔍</button>
                </div>
            </form>
        </div>
    </header>



    <main class="site-main">
        @yield('content')
    </main>
</body>

</html>