@if ($paginator->hasPages())
@php($pageName = $paginator->getPageName())
<nav class="flex items-center justify-end" aria-label="Simple pagination">
    <div class="join">
        @if ($paginator->onFirstPage())
            <button class="join-item btn btn-sm btn-disabled">« Prev</button>
        @else
            <button type="button" class="join-item btn btn-sm"
                wire:click="previousPage('{{ $pageName }}')" wire:loading.attr="disabled">« Prev</button>
        @endif

        @if ($paginator->hasMorePages())
            <button type="button" class="join-item btn btn-sm"
                wire:click="nextPage('{{ $pageName }}')" wire:loading.attr="disabled">Next »</button>
        @else
            <button class="join-item btn btn-sm btn-disabled">Next »</button>
        @endif
    </div>
</nav>
@endif
