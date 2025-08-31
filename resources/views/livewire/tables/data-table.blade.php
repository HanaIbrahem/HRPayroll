<div class="space-y-4" x-data>
    <!-- Top toolbar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <h2 class="text-2xl font-semibold tracking-tight">{{ $title }}</h2>

        <div class="flex items-center gap-2 flex-wrap">
            <label class="input input-bordered input-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 opacity-70" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 1 0 4.2 12.06l3.72 3.72a.75.75 0 1 0 1.06-1.06l-3.72-3.72a6.75 6.75 0 0 0-5.26-11zM5.25 10.5a5.25 5.25 0 1 1 10.5 0 5.25 5.25 0 0 1-10.5 0z" clip-rule="evenodd" />
                </svg>
                <input type="text" class="grow" placeholder="Global search..." wire:model.live.debounce="q" />
            </label>

            <select class="select select-bordered select-sm" wire:model.live.debounce="perPage">
                @foreach ($this->perPageOptions as $n)
                    <option value="{{ $n }}">{{ $n }} / page</option>
                @endforeach
            </select>

            <button class="btn btn-sm btn-warning" wire:click="clearFilters">Reset</button>

            <div class="join">
                <button class="btn btn-sm mx-3 btn-success join-item" wire:click="export('xlsx')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M4 4a2 2 0 0 1 2-2h7l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4z"/><path d="M14 2v4a1 1 0 0 0 1 1h4"/>
                    </svg> Excel
                </button>

                <button class="btn btn-error mx-2 btn-sm join-item" wire:click="export('pdf')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M6 2h7l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/>
                        <path d="M14 2v4a1 1 0 0 0 1 1h4"/>
                        <path d="M8 14h2a2 2 0 0 0 0-4H8v4zM13 10h2a2 2 0 1 1 0 4h-2v-4zM13 10v4M8 10v4"/>
                    </svg> PDF
                </button>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 border border-base-300/60">
        <div class="card-body p-0">
            <div class="overflow-x-auto w-full">
                <table class="table table-sm table-zebra w-full">
                    <thead class="bg-base-100 top-0 z-10">
                        <tr>
                            <th class="px-3 py-2 text-xs font-semibold text-base-content/70">#</th>
                            @foreach ($columns as $c)
                                @php $active = $sortField === ($c['field'] ?? ''); $sortable = $c['sortable'] ?? true; @endphp
                                <th class="px-3 py-2 text-xs">
                                    @if ($sortable)
                                        <button class="btn btn-ghost btn-xs" wire:click="sortBy('{{ $c['field'] }}')">
                                            {{ $c['label'] ?? ucfirst($c['field']) }}
                                            @if ($active) <span class="ml-1">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span> @endif
                                        </button>
                                    @else
                                        <span class="font-semibold text-base-content/70">{{ $c['label'] ?? ucfirst($c['field']) }}</span>
                                    @endif
                                </th>
                            @endforeach
                            <th class="px-3 py-2 text-xs font-semibold text-base-content/70">Actions</th>
                        </tr>
                        <tr>
                            <th class="px-3 py-2"></th>
                            @foreach ($columns as $c)
                                @php
                                    $field = $c['field'];
                                    $ctype = $c['type'] ?? 'text';
                                    $ftype = $c['filter'] ?? ($ctype === 'boolean' ? 'boolean' : ($ctype === 'date' ? 'none' : 'text'));
                                    $bind  = $c['filter_key'] ?? str_replace('.', '__', $field);
                                @endphp
                                <th class="px-3 py-2">
                                    @if ($ftype === 'text')
                                        <input type="text" class="input input-bordered input-xs w-full" placeholder="Filter {{ $c['label'] ?? $field }}" wire:model.live.debounce.300ms="filters.{{ $bind }}" />
                                    @elseif ($ftype === 'boolean')
                                        <select class="select select-bordered select-xs w-full" wire:model.live.debounce="filters.{{ $bind }}">
                                            <option value="">All</option>
                                            <option value="1">{{ $c['options'][1] ?? 'Active' }}</option>
                                            <option value="0">{{ $c['options'][0] ?? 'Inactive' }}</option>
                                        </select>
                                    @elseif ($ftype === 'select')
                                        <select class="select select-bordered select-xs w-full" wire:model="filters.{{ $bind }}">
                                            <option value="">All</option>
                                            @foreach (($c['options'] ?? []) as $val => $label)
                                                <option value="{{ $val }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </th>
                            @endforeach
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse ($rows as $r)
                            <tr>
                                <td class="px-3 py-2 text-base-content/70">{{ ($rows->firstItem() ?? 0) + $loop->iteration - 1 }}</td>
                                @foreach ($columns as $c)
                                    @php
                                        $field = $c['field'];
                                        $type  = $c['type'] ?? 'text';
                                        $val   = data_get($r, $field);
                                        $maxLength = 100;
                                        $width = $c['width'] ?? 'max-w-xs';
                                    @endphp
                                    <td class="px-3 py-2 align-top whitespace-normal break-words text-xs {{ $width }}">
                                        @if ($type === 'boolean')
                                            <span class="font-bold {{ $val ? 'text-success' : 'text-secondary' }}">
                                                {{ $val ? ($c['options'][1] ?? 'Active') : ($c['options'][0] ?? 'Inactive') }}
                                            </span>
                                        @elseif ($type === 'date' && !empty($c['format']) && $val)
                                            {{ \Illuminate\Support\Carbon::parse($val)->format($c['format']) }}
                                        @elseif (is_string($val) && strlen($val) > $maxLength)
                                            <div x-data="{ expanded: false }">
                                                <span x-show="!expanded">{{ \Illuminate\Support\Str::limit($val, $maxLength) }}</span>
                                                <span x-show="expanded">{{ $val }}</span>
                                                <button class="text-blue-500 text-xs ml-1" @click="expanded = !expanded" x-text="expanded ? 'Show less' : 'Show more'"></button>
                                            </div>
                                        @else
                                            {{ $val }}
                                        @endif
                                    </td>
                                @endforeach

                                @php
                                    $statusField = $this->statusField();
                                    $isActive    = $statusField ? (bool) data_get($r, $statusField) : null;
                                    $editUrl     = $this->editUrl($r->id);
                                @endphp
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="flex items-center gap-1">
                                        @if ($editUrl)
                                            <a href="{{ $editUrl }}" wire:navigate class="btn btn-xs btn-secondary">Edit</a>
                                        @else
                                            <button class="btn btn-info btn-xs" wire:click="edit({{ $r->id }})">Edit</button>
                                        @endif

                                        @if (!is_null($isActive))
                                            <button class="btn btn-xs {{ $isActive ? 'btn-error' : 'btn-success' }}" wire:click="toggleStatus({{ $r->id }})">
                                                {{ $isActive ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) + 2 }}">
                                    <div class="p-6 text-center text-base-content/60">No results.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-t border-base-300/60">
                {{ $rows->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
</div>
