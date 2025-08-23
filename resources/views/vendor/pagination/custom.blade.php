@if ($paginator->hasPages())
    <nav aria-label="Page navigation" class="mt-4">
        <ul id="pagination" class="flex flex-wrap justify-center gap-2 text-sm">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li>
                    <span class="px-3 py-1 rounded-md bg-gray-200 text-gray-500 cursor-not-allowed">

                        prev
                    </span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}"
                       class="px-3 py-1 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
                        prev
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li>
                        <span class="px-3 py-1 text-gray-500">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li>
                                <span class="px-3 py-1 rounded-md bg-blue-500 text-white font-semibold">
                                    {{ $page }}
                                </span>
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}"
                                   class="px-3 py-1 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-100">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}"
                       class="px-3 py-1 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-100">
                        next
                    </a>
                </li>
            @else
                <li>
                    <span class="px-3 py-1 rounded-md bg-gray-200 text-gray-500 cursor-not-allowed">
                       next
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
