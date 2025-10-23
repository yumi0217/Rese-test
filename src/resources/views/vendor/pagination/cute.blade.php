@if ($paginator->hasPages())
<nav class="cute-pager" role="navigation" aria-label="Pagination">

    {{-- First --}}
    @if ($paginator->currentPage() === 1)
    <span class="pg-btn is-disabled" aria-disabled="true" aria-label="最初のページ" tabindex="-1">
        <svg viewBox="0 0 20 20" aria-hidden="true">
            <path d="M12.5 15L7.5 10l5-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="lbl">最初</span>
    </span>
    @else
    <a class="pg-btn" href="{{ $paginator->url(1) }}" rel="first" aria-label="最初のページ">
        <svg viewBox="0 0 20 20" aria-hidden="true">
            <path d="M12.5 15L7.5 10l5-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="lbl">最初</span>
    </a>
    @endif

    {{-- Prev --}}
    @if ($paginator->onFirstPage())
    <span class="pg-btn is-disabled" aria-disabled="true" aria-label="前へ" tabindex="-1">
        <svg viewBox="0 0 20 20" aria-hidden="true">
            <path d="M12.5 15L7.5 10l5-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="lbl">前へ</span>
    </span>
    @else
    <a class="pg-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="前へ">
        <svg viewBox="0 0 20 20" aria-hidden="true">
            <path d="M12.5 15L7.5 10l5-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="lbl">前へ</span>
    </a>
    @endif

    {{-- Numbers --}}
    <ul class="pg-list" role="list">
        @foreach ($elements as $element)
        @if (is_string($element))
        <li class="pg-ellipsis" aria-hidden="true">{{ $element }}</li>
        @endif

        @if (is_array($element))
        @foreach ($element as $page => $url)
        @if ($page == $paginator->currentPage())
        <li class="pg-item is-current" aria-current="page"><span>{{ $page }}</span></li>
        @else
        <li class="pg-item"><a href="{{ $url }}" aria-label="ページ {{ $page }}">{{ $page }}</a></li>
        @endif
        @endforeach
        @endif
        @endforeach
    </ul>

    {{-- Next --}}
    @if ($paginator->hasMorePages())
    <a class="pg-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="次へ">
        <span class="lbl">次へ</span>
        <svg viewBox="0 0 20 20" aria-hidden="true">
            <path d="M7.5 5l5 5-5 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </a>
    @else
    <span class="pg-btn is-disabled" aria-disabled="true" aria-label="次へ" tabindex="-1">
        <span class="lbl">次へ</span>
        <svg viewBox="0 0 20 20" aria-hidden="true">
            <path d="M7.5 5l5 5-5 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </span>
    @endif

    {{-- Last --}}
    @if ($paginator->currentPage() === $paginator->lastPage())
    <span class="pg-btn is-disabled" aria-disabled="true" aria-label="最後のページ" tabindex="-1">
        <span class="lbl">最後</span>
        <svg viewBox="0 0 20 20" aria-hidden="true">
            <path d="M7.5 5l5 5-5 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </span>
    @else
    <a class="pg-btn" href="{{ $paginator->url($paginator->lastPage()) }}" rel="last" aria-label="最後のページ">
        <span class="lbl">最後</span>
        <svg viewBox="0 0 20 20" aria-hidden="true">
            <path d="M7.5 5l5 5-5 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </a>
    @endif
</nav>
@endif