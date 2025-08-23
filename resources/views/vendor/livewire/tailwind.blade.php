@if ($paginator->hasPages())
@php($pageName = $paginator->getPageName())
<nav class="flex items-center justify-between gap-3" aria-label="Pagination">
    {{-- Info --}}
    @if ($paginator->firstItem())
        <div class="text-sm text-base-content/70">
            Showing <span class="font-medium">{{ $paginator->firstItem() }}</span>
            to <span class="font-medium">{{ $paginator->lastItem() }}</span>
            of <span class="font-medium">{{ $paginator->total() }}</span> results
        </div>
    @endif

    <div class="join">
        {{-- Prev --}}
        @if ($paginator->onFirstPage())
            <button class="join-item btn btn-sm btn-disabled" aria-disabled="true">«</button>
        @else
            <button type="button" class="join-item btn btn-sm"
                wire:click="previousPage('{{ $pageName }}')" wire:loading.attr="disabled">«</button>
        @endif

        {{-- Numbers / dots --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <button class="join-item btn btn-sm btn-ghost" disabled>{{ $element }}</button>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $_url)
                    @if ($page == $paginator->currentPage())
                        <button class="join-item btn btn-sm btn-primary" aria-current="page">{{ $page }}</button>
                    @else
                        <button type="button" class="join-item btn btn-sm"
                            wire:click="gotoPage({{ $page }}, '{{ $pageName }}')"
                            wire:loading.attr="disabled">{{ $page }}</button>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <button type="button" class="join-item btn btn-sm"
                wire:click="nextPage('{{ $pageName }}')" wire:loading.attr="disabled">»</button>
        @else
            <button class="join-item btn btn-sm btn-disabled" aria-disabled="true">»</button>
        @endif
    </div>
</nav>
@endif
