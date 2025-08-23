<div class="space-y-4">
    <!-- Top toolbar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <h2 class="text-2xl font-semibold tracking-tight">{{ $title }}</h2>

        <div class="flex items-center gap-2">
            <label class="input input-bordered input-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 opacity-70" viewBox="0 0 24 24"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10.5 3.75a6.75 6.75 0 1 0 4.2 12.06l3.72 3.72a.75.75 0 1 0 1.06-1.06l-3.72-3.72a6.75 6.75 0 0 0-5.26-11zM5.25 10.5a5.25 5.25 0 1 1 10.5 0 5.25 5.25 0 0 1-10.5 0z"
                        clip-rule="evenodd" />
                </svg>
                <input type="text" class="grow" placeholder="Global search..." wire:model.live.debounce="q" />
            </label>

            <!-- Per-page: 10 / 50 / 100 -->
            <select class="select select-bordered select-sm" wire:model.live.debounce="perPage">
                @foreach ($this->perPageOptions as $n)
                <option value="{{ $n }}">{{ $n }} / page</option>
                @endforeach
            </select>

            <button class="btn btn-sm" wire:click="clearFilters">Reset</button>
        </div>
    </div>

    <div class="card bg-base-100 border border-base-300/60">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-sm table-zebra w-full">
                    <thead class="bg-base-100 sticky top-0 z-10">
                        <tr>
                            {{-- Serial header --}}
                            <th class="px-3 py-2 whitespace-nowrap text-xs font-semibold text-base-content/70">#</th>

                            @foreach ($columns as $c)
                            @php
                            $active = $sortField === $c['field'];
                            $sortable = $c['sortable'] ?? true;
                            @endphp
                            <th class="px-3 py-2 whitespace-nowrap">
                                @if ($sortable)
                                <button class="btn btn-ghost btn-xs" wire:click="sortBy('{{ $c['field'] }}')">
                                    {{ $c['label'] ?? ucfirst($c['field']) }}
                                    @if ($active)
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </button>
                                @else
                                <span class="text-xs font-semibold text-base-content/70">
                                    {{ $c['label'] ?? ucfirst($c['field']) }}
                                </span>
                                @endif
                            </th>
                            @endforeach
                            <th class="px-3 py-2 whitespace-nowrap text-xs font-semibold text-base-content/70">Actions
                            </th>
                        </tr>

                        <tr>
                            {{-- Empty filter cell under serial --}}
                            <th class="px-3 py-2"></th>

                            @foreach ($columns as $c)
                            @php
                            $field = $c['field'];
                            $ctype = $c['type'] ?? 'text';
                            $ftype = $c['filter'] ?? ($ctype === 'boolean' ? 'boolean' : ($ctype === 'date' ? 'none' :
                            'text'));
                            @endphp
                            <th class="px-3 py-2 whitespace-nowrap">
                                @if ($ftype === 'text')
                                <input type="text" class="input input-bordered input-xs w-full"
                                    placeholder="Filter {{ $c['label'] ?? $field }}"
                                    wire:model.live.debounce.300ms="filters.{{ $field }}" />
                                @elseif ($ftype === 'boolean')
                                <select class="select select-bordered select-xs w-full"
                                    wire:model.live.debounce="filters.{{ $field }}">
                                    <option value="">All</option>
                                    <option value="1">{{ $c['options'][1] ?? 'Active' }}</option>
                                    <option value="0">{{ $c['options'][0] ?? 'Inactive' }}</option>
                                </select>
                                @elseif ($ftype === 'select')
                                <select class="select select-bordered select-xs w-full"
                                    wire:model="filters.{{ $field }}">
                                    <option value="">All</option>
                                    @foreach (($c['options'] ?? []) as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @elseif ($ftype === 'date_range')
                                {{-- disabled intentionally --}}
                                @endif
                            </th>
                            @endforeach
                            {{-- Empty filter cell under Actions --}}
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>

                    <tbody class="text-sm">
                        @forelse ($rows as $r)
                        <tr>
                            {{-- Serial cell (respects pagination offsets) --}}
                            <td class="px-3 py-2 whitespace-nowrap text-base-content/70">
                                {{ ($rows->firstItem() ?? 0) + $loop->iteration - 1 }}
                            </td>

                            @foreach ($columns as $c)
                            @php
                            $field = $c['field'];
                            $type = $c['type'] ?? 'text';
                            $val = data_get($r, $field);
                            @endphp
                            <td class="px-3 py-2 whitespace-nowrap">
                                @if ($type === 'boolean')
                                <span class="font-bold {{ $val ? 'text-success' : 'text-secondary' }}">
                                    {{ $val ? ($c['options'][1] ?? 'Active') : ($c['options'][0] ?? 'Inactive') }}
                                </span>
                                @elseif ($type === 'date' && !empty($c['format']) && $val)
                                {{ \Illuminate\Support\Carbon::parse($val)->format($c['format']) }}
                                @else
                                {{ $val }}
                                @endif
                            </td>
                            @endforeach

                            {{-- Actions --}}
                            @php
                            $statusField = $this->statusField();
                            $isActive = $statusField ? (bool) data_get($r, $statusField) : null;
                            $editUrl = $this->editUrl($r->id);
                            @endphp
                            <td class="px-3 py-2 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    @if ($editUrl)
                                    {{-- SPA on v3, normal link on v2 --}}
                                    <a href="{{ $editUrl }}" wire:navigate class="btn btn-ghost btn-xs">
                                        Edit
                                    </a>
                                    @else
                                    {{-- fallback (if you don’t return a URL) --}}
                                    <button class="btn btn-info btn-xs" wire:click="edit({{ $r->id }})">Edit</button>
                                    @endif

                                    @if (!is_null($isActive))
                                    <button class="btn btn-xs {{ $isActive ? 'btn-error' : 'btn-success' }}"
                                        wire:click="toggleStatus({{ $r->id }})"
                                        title="{{ $isActive ? 'Deactivate' : 'Activate' }}">
                                        {{ $isActive ? 'Deactivate' : 'Activate' }}
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            {{-- +2 for Serial + Actions --}}
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