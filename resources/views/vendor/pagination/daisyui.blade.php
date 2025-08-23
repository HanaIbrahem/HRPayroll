@if ($paginator->hasPages())
<nav role="navigation" aria-label="Pagination" class="flex items-center justify-between gap-3">
    {{-- Info (left) --}}
    @if ($paginator->firstItem())
        <div class="text-sm text-base-content/70">
            Showing
            <span class="font-medium">{{ $paginator->firstItem() }}</span>
            to
            <span class="font-medium">{{ $paginator->lastItem() }}</span>
            of
            <span class="font-medium">{{ $paginator->total() }}</span>
            results
        </div>
    @else
        <div class="text-sm text-base-content/70">
            Showing <span class="font-medium">{{ $paginator->count() }}</span> results
        </div>
    @endif

    {{-- Pager (right) --}}
    <div class="join">
        {{-- Prev --}}
        @if ($paginator->onFirstPage())
            <button class="join-item btn btn-sm btn-disabled" aria-disabled="true" aria-label="Previous">«</button>
        @else
            <a class="join-item btn btn-sm" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">«</a>
        @endif

        {{-- Numbers / dots --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <button class="join-item btn btn-sm btn-ghost" disabled>{{ $element }}</button>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="join-item btn btn-sm btn-primary" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="join-item btn btn-sm" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a class="join-item btn btn-sm" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">»</a>
        @else
            <button class="join-item btn btn-sm btn-disabled" aria-disabled="true" aria-label="Next">»</button>
        @endif
    </div>
</nav>
@endif
