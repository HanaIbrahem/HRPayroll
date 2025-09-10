<div class="space-y-4">
    <!-- Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <h2 class="text-2xl font-semibold tracking-tight">{{ $title }}</h2>

        <div class="flex items-center gap-2 flex-wrap">
            <label class="input input-bordered input-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 opacity-70" fill="currentColor"
                    viewBox="0 0 24 24">
                    <path fill-rule="evenodd"
                        d="M10.5 3.75a6.75 6.75 0 1 0 4.2 12.06l3.72 3.72a.75.75 0 1 0 1.06-1.06l-3.72-3.72a6.75 6.75 0 0 0-5.26-11zM5.25 10.5a5.25 5.25 0 1 1 10.5 0 5.25 5.25 0 0 1-10.5 0z"
                        clip-rule="evenodd" />
                </svg>
                <input type="text" class="grow" placeholder="Search…" wire:model.live.debounce.300ms="q" />
            </label>

            <select class="select select-bordered select-sm" wire:model.live.debounce="perPage">
                @foreach ($this->perPageOptions as $n)
                <option value="{{ $n }}">{{ $n }} / page</option>
                @endforeach
            </select>

            <button class="btn btn-sm btn-warning" wire:click="clearFilters">Reset</button>

            <div class="join">
                <button class="btn btn-sm mx-3 btn-success join-item" wire:click="export('xlsx')">Excel</button>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <select class="select select-bordered select-sm" wire:model.live="dateField" title="Filter by which timestamp">
            @foreach ($dateFields as $key => $label)
            <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>

        <input type="date" class="input input-bordered input-sm" wire:model.live="dateFrom" title="From date" />

        <span class="text-sm text-base-content/60">to</span>

        <input type="date" class="input input-bordered input-sm" wire:model.live="dateTo" title="To date" />


        </button>
    </div>
    <div class="card bg-base-100 border border-base-300/60">
        <div class="card-body p-0">
            <div class="overflow-x-auto w-full">
                <table class="table table-sm table-zebra w-full">
                    <thead class="bg-base-100 top-0 z-10">
                        <!-- Header -->
                        <tr>
                            <th class="px-3 py-2 text-xs font-semibold text-base-content/70 w-10">
                                <span class="hidden sm:inline">#</span>
                            </th>
                            @foreach ($columns as $c)
                            @php
                            $active = $sortField === ($c['field'] ?? '');
                            $sortable = $c['sortable'] ?? true;
                            $hideSm = $c['hide_sm'] ?? false;
                            @endphp
                            <th class="px-3 py-2 text-xs {{ $hideSm ? 'hidden sm:table-cell' : '' }}">
                                @if ($sortable)
                                <button class="btn btn-ghost btn-xs" wire:click="sortBy('{{ $c['field'] }}')">
                                    {{ $c['label'] ?? ucfirst($c['field']) }}
                                    @if ($active) <span class="ml-1">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </button>
                                @else
                                <span class="font-semibold text-base-content/70">{{ $c['label'] ?? ucfirst($c['field'])
                                    }}</span>
                                @endif
                            </th>
                            @endforeach
                            <th class="px-3 py-2 text-xs font-semibold text-base-content/70 hidden sm:table-cell">
                                Actions</th>
                        </tr>

                        <!-- Filters (visible from sm breakpoint) -->
                        <tr class="hidden sm:table-row">
                            <th class="px-3 py-2"></th>
                            @foreach ($columns as $c)
                            @php
                            $field = $c['field'];
                            $ctype = $c['type'] ?? 'text';
                            $ftype = $c['filter'] ?? ($ctype === 'boolean' ? 'boolean' : ($ctype === 'date' ? 'none' :
                            'text'));
                            $bind = $c['filter_key'] ?? str_replace('.', '__', $field);
                            $hideSm = $c['hide_sm'] ?? false;
                            @endphp
                            <th class="px-3 py-2 {{ $hideSm ? 'hidden sm:table-cell' : '' }}">
                                @if ($ftype === 'text')
                                <input type="text" class="input input-bordered input-xs w-full"
                                    placeholder="Filter {{ $c['label'] ?? $field }}"
                                    wire:model.live.debounce.300ms="filters.{{ $bind }}" />
                                @elseif ($ftype === 'boolean')
                                <select class="select select-bordered select-xs w-full"
                                    wire:model.live.debounce="filters.{{ $bind }}">
                                    <option value="">All</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                                @elseif ($ftype === 'select')
                                <select class="select select-bordered select-xs w-full"
                                    wire:model.live.debounce.150ms="filters.{{ $bind }}">
                                    <option value="">All</option>
                                    @foreach (($c['options'] ?? []) as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>

                                {{-- NEW: date-range inside the column --}}
                                @elseif ($ftype === 'date-range')
                                <div class="flex items-center gap-1">
                                    <select class="select select-bordered select-xs" wire:model.live="dateField"
                                        title="Filter by field">
                                        @foreach ($dateFields as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>

                                    <input type="date" class="input input-bordered input-xs" wire:model.live="dateFrom"
                                        title="From date" />

                                    <span class="text-[11px] text-base-content/60">→</span>

                                    <input type="date" class="input input-bordered input-xs" wire:model.live="dateTo"
                                        title="To date" />

                                    <button class="btn btn-ghost btn-xs" wire:click="clearDateFilter"
                                        title="Clear date filter">✕</button>
                                </div>
                                @endif
                            </th>
                            @endforeach
                            <th class="px-3 py-2 hidden sm:table-cell"></th>
                        </tr>
                    </thead>

                    @forelse ($rows as $r)
              

                    <!-- One tbody per row (stable scopes) -->
                    <tbody x-data="{ open:false }" wire:key="row-{{ $r->id }}" class="text-sm">
                        <tr class="align-top">
                            <td class="px-2 py-2 w-10 align-top">
                                <!-- Row expander (mobile) -->
                                <button class="sm:hidden btn btn-ghost btn-xs p-0" @click="open = !open"
                                    :aria-expanded="open.toString()">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 transition-transform"
                                        :class="open ? 'rotate-90' : ''" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <span class="hidden sm:inline text-base-content/70">
                                    {{ ($rows->firstItem() ?? 0) + $loop->iteration - 1 }}
                                </span>
                            </td>

                            <!-- Data cells -->
                            @foreach ($columns as $c)
                            @php
                            $field = $c['field'];
                            $type = $c['type'] ?? 'text';
                            $val = data_get($r, $field);
                            $hideSm = $c['hide_sm'] ?? false;
                            $width = $c['width'] ?? 'max-w-xs';
                            @endphp
                            <td
                                class="px-3 py-2 whitespace-normal break-words text-xs {{ $hideSm ? 'hidden sm:table-cell ' : '' }}{{ $width }}">
                                @if ($field === 'status')
                                @php $s = (string)$r->status; @endphp
                                <span
                                    class="font-bold {{ $s==='open' ? 'text-info' : ($s==='pending' ? 'text-warning' : ($s==='approved' ? 'text-success' : 'text-error')) }}">
                                    {{ ucfirst($s) }}
                                </span>
                                @elseif ($type === 'date' && !empty($c['format']) && $val)
                                {{ \Illuminate\Support\Carbon::parse($val)->format($c['format']) }}
                                @else
                                {{ $val }}
                                @endif
                            </td>
                            @endforeach

                            <!-- Actions (desktop) — FIXED ICON BUTTONS -->
                            <td class="px-3 py-2 whitespace-nowrap hidden sm:table-cell">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- View (link) -->
                                    <a wire:navigate href="{{ route('hr.show', $r->id) }}"
                                        class="btn btn-ghost btn-xs" title="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                            fill="currentColor">
                                            <path
                                                d="M12 5C4.367 5 1 12 1 12s3.367 7 11 7 11-7 11-7-3.367-7-11-7Zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10Z" />
                                        </svg>
                                    </a>                                   
                                </div>
                            </td>
                        </tr>

                        <!-- Mobile details + same fixed buttons -->
                        <tr x-show="open" x-cloak x-transition class="sm:hidden">
                            <td colspan="{{ count($columns) + 2 }}" class="px-4 pb-3">
                                <div class="rounded-xl border border-base-300/60 p-3 bg-base-200/40">
                                    <dl class="space-y-2">
                                        @foreach ($columns as $c)
                                        @php $field = $c['field']; $type = $c['type'] ?? 'text'; $val = data_get($r,
                                        $field); @endphp
                                        <div>
                                            <dt class="text-[11px] uppercase tracking-wide text-base-content/60">{{
                                                $c['label'] ?? ucfirst($field) }}</dt>
                                            <dd class="text-sm">
                                                @if ($field === 'status')
                                                @php $s = (string)$r->status; @endphp
                                                <span
                                                    class="font-bold {{ $s==='open' ? 'text-info' : ($s==='pending' ? 'text-warning' : ($s==='approved' ? 'text-success' : 'text-error')) }}">
                                                    {{ ucfirst($s) }}
                                                </span>
                                                @elseif ($type === 'date' && !empty($c['format']) && $val)
                                                {{ \Illuminate\Support\Carbon::parse($val)->format($c['format']) }}
                                                @else
                                                {{ $val }}
                                                @endif
                                            </dd>
                                        </div>
                                        @endforeach
                                    </dl>

                                    <div class="mt-3 flex flex-wrap gap-1">
                                        <a wire:navigate href="{{ route('checklist.show', $r->id) }}"
                                            class="btn btn-ghost btn-xs" title="View">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                                fill="currentColor">
                                                <path
                                                    d="M12 5C4.367 5 1 12 1 12s3.367 7 11 7 11-7 11-7-3.367-7-11-7Zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10Z" />
                                            </svg>
                                        </a>
                                    
            
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    @empty
                    <tbody>
                        <tr>
                            <td colspan="{{ count($columns) + 2 }}">
                                <div class="p-6 text-center text-base-content/60">No results.</div>
                            </td>
                        </tr>
                    </tbody>
                    @endforelse
                </table>
            </div>

            <div class="p-3 border-t  border-base-300/60">
                {{ $rows->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
</div>