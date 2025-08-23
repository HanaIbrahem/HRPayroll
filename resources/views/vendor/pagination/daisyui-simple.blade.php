@if ($paginator->hasPages())
<nav role="navigation" aria-label="Simple pagination" class="flex items-center justify-end">
    <div class="join">
        @if ($paginator->onFirstPage())
            <button class="join-item btn btn-sm btn-disabled">« Prev</button>
        @else
            <a class="join-item btn btn-sm" href="{{ $paginator->previousPageUrl() }}" rel="prev">« Prev</a>
        @endif

        @if ($paginator->hasMorePages())
            <a class="join-item btn btn-sm" href="{{ $paginator->nextPageUrl() }}" rel="next">Next »</a>
        @else
            <button class="join-item btn btn-sm btn-disabled">Next »</button>
        @endif
    </div>
</nav>
@endif
