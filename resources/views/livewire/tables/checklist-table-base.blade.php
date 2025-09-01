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
                <button class="btn btn-error mx-2 btn-sm join-item" wire:click="export('pdf')">PDF</button>
            </div>
        </div>
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

                        <!-- Filters -->
                        <tr class="hidden xs:table-row">
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
                                    <option value="1">{{ $c['options'][1] ?? 'Active' }}</option>
                                    <option value="0">{{ $c['options'][0] ?? 'Inactive' }}</option>
                                </select>
                                @elseif ($ftype === 'select')
                                <select class="select select-bordered select-xs w-full"
                                    wire:model.live.debounce.150ms="filters.{{ $bind }}">
                                    <option value="">All</option>
                                    @foreach (($c['options'] ?? []) as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @endif
                            </th>
                            @endforeach
                            <th class="px-3 py-2 hidden sm:table-cell"></th>
                        </tr>
                    </thead>

                    @forelse ($rows as $r)
                    @php
                    $canEdit = method_exists($this, 'canEditRow') ? $this->canEditRow($r) : false;
                    $canDelete = method_exists($this, 'canDeleteRow') ? $this->canDeleteRow($r) : false;
                    $canClose = method_exists($this, 'canCloseRow') ? $this->canCloseRow($r) : false;
                    $canApprove = method_exists($this, 'canApproveRow') ? $this->canApproveRow($r) : false;
                    $canReject = method_exists($this, 'canRejectRow') ? $this->canRejectRow($r) : false;
                    @endphp

                    <!-- Scope per row -->
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
                                <span class="font-bold
                                                {{ $s==='open' ? 'text-info' :
                                                   ($s==='pending' ? 'text-warning' :
                                                   ($s==='approved' ? 'text-success' : 'text-error')) }}">
                                    {{ ucfirst($s) }}
                                </span>
                                @elseif ($type === 'date' && !empty($c['format']) && $val)
                                {{ \Illuminate\Support\Carbon::parse($val)->format($c['format']) }}
                                @else
                                {{ $val }}
                                @endif
                            </td>
                            @endforeach

                            <!-- Actions (desktop) -->
                            <td class="px-3 py-2 whitespace-nowrap hidden sm:table-cell">
                                <div x-data="{ openMenu:false }" class="relative inline-block">
                                    <!-- icon-only trigger -->
                                    <button class="btn btn-ghost btn-xs px-2 h-7 min-h-0 rounded-lg hover:bg-base-200"
                                        @click.stop="openMenu = !openMenu" @keydown.escape.window="openMenu = false"
                                        :aria-expanded="openMenu.toString()" aria-haspopup="menu" title="Actions">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path
                                                d="M6 10a2 2 0 11-4.001-.001A2 2 0 016 10zm6 0a2 2 0 11-4.001-.001A2 2 0 0112 10zm6 0a2 2 0 11-4.001-.001A2 2 0 0118 10z" />
                                        </svg>
                                    </button>

                                    <!-- Click-away -->
                                    <div x-show="openMenu" x-cloak class="fixed inset-0 z-10" @click="openMenu=false">
                                    </div>

                                    <!-- Menu card -->
                                    <div x-show="openMenu" x-cloak x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                        class="z-20 absolute right-0 mt-1 w-52 rounded-xl border border-base-300/60 bg-base-100 shadow-xl"
                                        role="menu">
                                        <div class="p-1">

                                            <button role="menuitem"
                                                class="w-full btn btn-ghost btn-xs justify-start gap-2 h-8 min-h-0 rounded-lg"
                                                wire:navigate
                                                onclick="window.Livewire?.navigate('{{ route('checklist.show', $r->id) }}'); openMenu=false">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M12 7a5 5 0 100 10 5 5 0 000-10zm0-5C6.477 2 2 6.477 
                 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z" />
                                                </svg>
                                                View
                                            </button>

                                            <!-- Edit -->
                                            <button role="menuitem"
                                                class="w-full btn btn-ghost btn-xs justify-start gap-2 h-8 min-h-0 rounded-lg {{ $canEdit ? '' : 'pointer-events-none opacity-50' }}"
                                                wire:navigate
                                                onclick="if({{ $canEdit ? 'true' : 'false' }}){ window.Livewire?.navigate('{{ route('checklist.edit', $r->id) }}'); openMenu=false }">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M4 17.25V21h3.75L17.81 10.94l-3.75-3.75L4 17.25zM20.71 
                 7.04a1.003 1.003 0 000-1.42l-2.34-2.34a1.003 1.003 
                 0 00-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.82z" />
                                                </svg>
                                                Edit
                                            </button>

                                            <button role="menuitem"
                                                class="w-full btn btn-ghost btn-xs justify-start gap-2 h-8 min-h-0 rounded-lg {{ $canClose ? '' : 'pointer-events-none opacity-50' }}"
                                                @click.prevent="if({{ $canClose ? 'true' : 'false' }}){ $wire.close({{ $r->id }}); openMenu=false }">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M5 12h14v2H5zM5 8h14v2H5z" />
                                                </svg>
                                                Close (→ Pending)
                                            </button>


                                            <div class="my-1 border-t border-base-300/60"></div>

                                            <!-- HR section -->
                                            <div
                                                class="px-2 py-1 text-[10px] tracking-wide uppercase text-base-content/60">
                                                HR</div>

                                            <button role="menuitem"
                                                class="w-full btn btn-ghost btn-xs justify-start gap-2 h-8 min-h-0 rounded-lg {{ $canApprove ? '' : 'pointer-events-none opacity-50' }}"
                                                @click.prevent="if({{ $canApprove ? 'true' : 'false' }}){ $wire.approve({{ $r->id }}); openMenu=false }">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                                                </svg>
                                                Approve
                                            </button>

                                            <button role="menuitem"
                                                class="w-full btn btn-ghost btn-xs justify-start gap-2 h-8 min-h-0 rounded-lg {{ $canReject ? '' : 'pointer-events-none opacity-50' }}"
                                                @click.prevent="if({{ $canReject ? 'true' : 'false' }}){ $wire.reject({{ $r->id }}); openMenu=false }">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19
                                                    12 13.41 17.59 19 19 17.59 13.41 12z" />
                                                </svg>
                                                Reject
                                            </button>

                                            <div class="my-1 border-t border-base-300/60"></div>

                                            <!-- Danger -->
                                            <button role="menuitem"
                                                class="w-full btn btn-ghost btn-xs justify-start gap-2 h-8 min-h-0 rounded-lg text-error {{ $canDelete ? '' : 'pointer-events-none opacity-50' }}"
                                                @click.prevent="if({{ $canDelete ? 'true' : 'false' }}){ if(confirm('Delete this checklist?')){ $wire.delete({{ $r->id }}); openMenu=false } }">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                                    <path
                                                        d="M6 7h12v12a2 2 0 01-2 2H8a2 2 0 01-2-2V7zm3-3h6l1 1h4v2H4V5h4l1-1z" />
                                                </svg>
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Mobile details -->
                        <tr x-show="open" x-cloak x-transition class="sm:hidden">
                            <td colspan="{{ count($columns) + 2 }}" class="px-4 pb-3">
                                <div class="rounded-xl border border-base-300/60 p-3 bg-base-200/40">
                                    <dl class="space-y-2">
                                        @foreach ($columns as $c)
                                        @php
                                        $field = $c['field']; $type = $c['type'] ?? 'text';
                                        $val = data_get($r, $field);
                                        $show = $c['hide_sm'] ?? false;
                                        @endphp
                                        @if ($show)
                                        <div>
                                            <dt class="text-[11px] uppercase tracking-wide text-base-content/60">
                                                {{ $c['label'] ?? ucfirst($field) }}
                                            </dt>
                                            <dd class="text-sm">
                                                @if ($field === 'status')
                                                @php $s = (string)$r->status; @endphp
                                                <span
                                                    class="badge
                                                                    {{ $s==='open' ? 'badge-ghost' :
                                                                       ($s==='pending' ? 'badge-warning' :
                                                                       ($s==='approved' ? 'badge-success' : 'badge-error')) }}">
                                                    {{ ucfirst($s) }}
                                                </span>
                                                @elseif ($type === 'date' && !empty($c['format']) && $val)
                                                {{ \Illuminate\Support\Carbon::parse($val)->format($c['format']) }}
                                                @else
                                                {{ $val }}
                                                @endif
                                            </dd>
                                        </div>
                                        @endif
                                        @endforeach

                                        <!-- Mobile action sheet -->
                                        <div class="pt-2">
                                            <div x-data="{ openSheet:false }" class="relative">
                                                <button class="btn btn-outline btn-xs rounded-lg"
                                                    @click.stop="openSheet = !openSheet"
                                                    @keydown.escape.window="openSheet=false">
                                                    Actions
                                                </button>

                                                <div x-show="openSheet" x-cloak class="fixed inset-0 z-10"
                                                    @click="openSheet=false"></div>

                                                <div x-show="openSheet" x-cloak
                                                    x-transition:enter="transition ease-out duration-100"
                                                    x-transition:enter-start="opacity-0 translate-y-2"
                                                    x-transition:enter-end="opacity-100 translate-y-0"
                                                    x-transition:leave="transition ease-in duration-75"
                                                    x-transition:leave-start="opacity-100 translate-y-0"
                                                    x-transition:leave-end="opacity-0 translate-y-2"
                                                    class="z-20 absolute left-0 mt-2 w-56 rounded-xl border border-base-300/60 bg-base-100 shadow-xl p-2">
                                                    <div class="grid gap-1">

                                                        <button role="menuitem"
                                                            class="w-full btn btn-ghost btn-xs justify-start gap-2 h-8 min-h-0 rounded-lg"
                                                            wire:navigate
                                                            onclick="window.Livewire?.navigate('{{ route('checklist.show', $r->id) }}'); openMenu=false">
                                                            <svg class="w-4 h-4" viewBox="0 0 24 24"
                                                                fill="currentColor">
                                                                <path d="M12 7a5 5 0 100 10 5 5 0 000-10zm0-5C6.477 2 2 6.477 
                 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z" />
                                                            </svg>
                                                            View
                                                        </button>

                                                        <!-- Edit -->
                                                        <button role="menuitem"
                                                            class="w-full btn btn-ghost btn-xs justify-start gap-2 h-8 min-h-0 rounded-lg {{ $canEdit ? '' : 'pointer-events-none opacity-50' }}"
                                                            wire:navigate
                                                            onclick="if({{ $canEdit ? 'true' : 'false' }}){ window.Livewire?.navigate('{{ route('checklist.edit', $r->id) }}'); openMenu=false }">
                                                            <svg class="w-4 h-4" viewBox="0 0 24 24"
                                                                fill="currentColor">
                                                                <path d="M4 17.25V21h3.75L17.81 10.94l-3.75-3.75L4 17.25zM20.71 
                 7.04a1.003 1.003 0 000-1.42l-2.34-2.34a1.003 1.003 
                 0 00-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.82z" />
                                                            </svg>
                                                            Edit
                                                        </button>
                                                        <button
                                                            class="btn btn-ghost btn-xs justify-start h-8 min-h-0 {{ $canClose ? '' : 'pointer-events-none opacity-50' }}"
                                                            @click.prevent="if({{ $canClose ? 'true' : 'false' }}){ $wire.close({{ $r->id }}); openSheet=false }">
                                                            Close (→ Pending)
                                                        </button>

                                                        <div class="border-t border-base-300/60 my-1"></div>
                                                        <div
                                                            class="text-[10px] uppercase tracking-wide text-base-content/60 px-1">
                                                            HR</div>

                                                        <button
                                                            class="btn btn-ghost btn-xs justify-start h-8 min-h-0 {{ $canApprove ? '' : 'pointer-events-none opacity-50' }}"
                                                            @click.prevent="if({{ $canApprove ? 'true' : 'false' }}){ $wire.approve({{ $r->id }}); openSheet=false }">
                                                            Approve
                                                        </button>
                                                        <button
                                                            class="btn btn-ghost btn-xs justify-start h-8 min-h-0 {{ $canReject ? '' : 'pointer-events-none opacity-50' }}"
                                                            @click.prevent="if({{ $canReject ? 'true' : 'false' }}){ $wire.reject({{ $r->id }}); openSheet=false }">
                                                            Reject
                                                        </button>

                                                        <div class="border-t border-base-300/60 my-1"></div>
                                                        <button
                                                            class="btn btn-ghost btn-xs justify-start h-8 min-h-0 text-error {{ $canDelete ? '' : 'pointer-events-none opacity-50' }}"
                                                            @click.prevent="if({{ $canDelete ? 'true' : 'false' }}){ if(confirm('Delete this checklist?')){ $wire.delete({{ $r->id }}); openSheet=false } }">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </dl>
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

            <div class="p-3 border-t border-base-300/60">
                {{ $rows->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
</div>