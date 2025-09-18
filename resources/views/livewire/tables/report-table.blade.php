<div class="space-y-4">
    
    <!-- Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <h2 class="text-2xl font-semibold tracking-tight">{{ $title }}</h2>

        
        <div class="flex items-center gap-2 flex-wrap">
            <label class="input input-bordered input-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 opacity-70" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 1 0 4.2 12.06l3.72 3.72a.75.75 0 1 0 1.06-1.06l-3.72-3.72a6.75 6.75 0 0 0-5.26-11zM5.25 10.5a5.25 5.25 0 1 1 10.5 0 5.25 5.25 0 0 1-10.5 0z" clip-rule="evenodd" />
                </svg>
                <input type="text" class="grow" placeholder="Search…" wire:model.live.debounce.300ms="q" />
            </label>

            <select class="select select-bordered select-sm" wire:model.live.debounce="perPage">
                @foreach ($this->perPageOptions as $n)
                <option value="{{ $n }}">{{ $n }} / page</option>
                @endforeach
            </select>

            <!-- NEW: Filter button -->
            <button type="button" class="btn btn-sm btn-outline" wire:click="openFilter">
                Filter
            </button>

            <button class="btn btn-sm btn-warning" wire:click="clearFilters">Reset</button>

            
        </div>
        
    </div>

    <!-- Selected chips summary (optional) -->
    @if (count($departmentIds) || count($locationIds) || count($employeeIds))
        <div class="flex flex-wrap items-center gap-2 text-xs">
            @if (count($departmentIds))
                <span class="badge badge-outline">Departments: {{ count($departmentIds) }}</span>
            @endif
            @if (count($locationIds))
                <span class="badge badge-outline">Locations: {{ count($locationIds) }}</span>
            @endif
            @if (count($employeeIds))
                <span class="badge badge-outline">Employees: {{ count($employeeIds) }}</span>
            @endif
        </div>
    @endif

    <div class="flex items-center gap-2">
        <select class="select select-bordered select-sm" wire:model.live="dateField" title="Filter by which timestamp">
            @foreach ($dateFields as $key => $label)
            <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>

        <input type="date" class="input input-bordered input-sm" wire:model.live="dateFrom" title="From date" />
        <span class="text-sm text-base-content/60">to</span>
        <input type="date" class="input input-bordered input-sm" wire:model.live="dateTo" title="To date" />
    </div>

    <div class="join">
  <label class="label cursor-pointer flex items-center gap-2 mr-2">
    <input type="checkbox" class="toggle toggle-sm" wire:model="withVisited">
    <span class="text-sm">Detailed (with visited zones)</span>
  </label>

  <button class="btn px-8 btn-primary btn-sm join-item m-3"
          wire:click="exportPdf"
          wire:loading.attr="disabled"
          wire:target="exportPdf">
    <span wire:loading.remove wire:target="exportPdf">PDF Report</span>
    <span wire:loading wire:target="exportPdf" class="inline-flex items-center gap-2">
      <span class="loading loading-spinner loading-xs"></span>
      Generating…
    </span>
  </button>

  <button class="btn btn-success  px-8 btn-sm join-item m-3"
          wire:click="exportXlsx"
          wire:loading.attr="disabled"
          wire:target="exportXlsx">
    <span wire:loading.remove wire:target="exportXlsx">Excel Report</span>
    <span wire:loading wire:target="exportXlsx" class="inline-flex items-center gap-2">
      <span class="loading loading-spinner loading-xs"></span>
      Generating…
    </span>
  </button>
</div>

    <div class="card bg-base-100 border border-base-300/60">
        <div class="card-body p-0">
            <div class="overflow-x-auto w-full">
                <table class="table table-sm table-zebra w-full">
                    <thead class="bg-base-100 top-0 z-10">
                        <tr>
                            <th class="px-3 py-2 text-xs font-semibold text-base-content/70 w-10">
                                <span class="hidden sm:inline">#</span>
                            </th>
                            @foreach ($columns as $c)
                                @php
                                    $active   = $sortField === ($c['field'] ?? '');
                                    $sortable = $c['sortable'] ?? true;
                                    $hideSm   = $c['hide_sm'] ?? false;
                                @endphp
                                <th class="px-3 py-2 text-xs {{ $hideSm ? 'hidden sm:table-cell' : '' }}">
                                    @if ($sortable)
                                        <button class="btn btn-ghost btn-xs" wire:click="sortBy('{{ $c['field'] }}')">
                                            {{ $c['label'] ?? ucfirst($c['field']) }}
                                            @if ($active)
                                                <span class="ml-1">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                            @endif
                                        </button>
                                    @else
                                        <span class="font-semibold text-base-content/70">{{ $c['label'] ?? ucfirst($c['field']) }}</span>
                                    @endif
                                </th>
                            @endforeach
                            <th class="px-3 py-2 text-xs font-semibold text-base-content/70 hidden sm:table-cell">Actions</th>
                        </tr>

                        <!-- Filters row (kept) -->
                        <tr class="hidden sm:table-row">
                            <th class="px-3 py-2"></th>
                            @foreach ($columns as $c)
                                @php
                                    $field  = $c['field'];
                                    $ctype  = $c['type'] ?? 'text';
                                    $ftype  = $c['filter'] ?? ($ctype === 'boolean' ? 'boolean' : ($ctype === 'date' ? 'none' : 'text'));
                                    $bind   = $c['filter_key'] ?? str_replace('.', '__', $field);
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
                                    @elseif ($ftype === 'date-range')
                                        <div class="flex items-center gap-1">
                                            <select class="select select-bordered select-xs" wire:model.live="dateField" title="Filter by field">
                                                @foreach ($dateFields as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <input type="date" class="input input-bordered input-xs" wire:model.live="dateFrom" title="From date" />
                                            <span class="text-[11px] text-base-content/60">→</span>
                                            <input type="date" class="input input-bordered input-xs" wire:model.live="dateTo" title="To date" />
                                            <button class="btn btn-ghost btn-xs" wire:click="clearDateFilter" title="Clear date filter">✕</button>
                                        </div>
                                    @endif
                                </th>
                            @endforeach
                            <th class="px-3 py-2 hidden sm:table-cell"></th>
                        </tr>
                    </thead>

                    @forelse ($rows as $r)
                        @php $canEdit=false; @endphp
                        <tbody x-data="{ open:false }" wire:key="row-{{ $r->id }}" class="text-sm">
                            <tr class="align-top">
                                <td class="px-2 py-2 w-10 align-top">
                                    <button class="sm:hidden btn btn-ghost btn-xs p-0" @click="open = !open" :aria-expanded="open.toString()">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 transition-transform" :class="open ? 'rotate-90' : ''" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <span class="hidden sm:inline text-base-content/70">{{ ($rows->firstItem() ?? 0) + $loop->iteration - 1 }}</span>
                                </td>

                                @foreach ($columns as $c)
                                    @php
                                        $field = $c['field'];
                                        $type  = $c['type'] ?? 'text';
                                        $val   = data_get($r, $field);
                                        $hideSm= $c['hide_sm'] ?? false;
                                        $width = $c['width'] ?? 'max-w-xs';
                                    @endphp
                                    <td class="px-3 py-2 whitespace-normal break-words text-xs {{ $hideSm ? 'hidden sm:table-cell ' : '' }}{{ $width }}">
                                        @if ($field === 'status')
                                            @php $s = (string)$r->status; @endphp
                                            <span class="font-bold {{ $s==='open' ? 'text-info' : ($s==='pending' ? 'text-warning' : ($s==='approved' ? 'text-success' : 'text-error')) }}">
                                                {{ ucfirst($s) }}
                                            </span>
                                        @elseif ($type === 'date' && !empty($c['format']) && $val)
                                            {{ \Illuminate\Support\Carbon::parse($val)->format($c['format']) }}
                                        @else
                                            {{ $val }}
                                        @endif
                                    </td>
                                @endforeach

                                <td class="px-3 py-2 whitespace-nowrap hidden sm:table-cell">
                                    <div class="flex items-center justify-end gap-1">
                                        <a wire:navigate href="{{ route('hr.show', $r->id) }}" class="btn btn-ghost btn-xs" title="View">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12 5C4.367 5 1 12 1 12s3.367 7 11 7 11-7 11-7-3.367-7-11-7Zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10Z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <tr x-show="open" x-cloak x-transition class="sm:hidden">
                                <td colspan="{{ count($columns) + 2 }}" class="px-4 pb-3">
                                    <div class="rounded-xl border border-base-300/60 p-3 bg-base-200/40">
                                        <dl class="space-y-2">
                                            @foreach ($columns as $c)
                                                @php $field = $c['field']; $type = $c['type'] ?? 'text'; $val = data_get($r, $field); @endphp
                                                <div>
                                                    <dt class="text-[11px] uppercase tracking-wide text-base-content/60">{{ $c['label'] ?? ucfirst($field) }}</dt>
                                                    <dd class="text-sm">
                                                        @if ($field === 'status')
                                                            @php $s = (string)$r->status; @endphp
                                                            <span class="font-bold {{ $s==='open' ? 'text-info' : ($s==='pending' ? 'text-warning' : ($s==='approved' ? 'text-success' : 'text-error')) }}">
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
                                            <a wire:navigate href="{{ route('hr.show', $r->id) }}" class="btn btn-ghost btn-xs" title="View">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M12 5C4.367 5 1 12 1 12s3.367 7 11 7 11-7 11-7-3.367-7-11-7Zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10Z" />
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

    <!-- ========= FILTER MODAL ========= -->
    <div x-data x-cloak @keydown.escape.window="$wire.closeFilter()">
        <div class="modal {{ $isFilterModalOpen ? 'modal-open' : '' }}">
            <div class="modal-box max-w-4xl">
                <h3 class="font-semibold text-lg">Advanced Filters</h3>
                <p class="text-xs opacity-70 mt-1">Pick multiple departments, locations and employees. Apply to filter the table.</p>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Departments -->
                    <div class="border rounded-xl p-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="font-medium">Departments</div>
                            <div class="join">
                                <button class="btn btn-ghost btn-xs join-item" wire:click="$set('departmentIds', {{ json_encode(array_column($departmentOptions,'id')) }})">All</button>
                                <button class="btn btn-ghost btn-xs join-item" wire:click="$set('departmentIds', [])">Clear</button>
                            </div>
                        </div>
                        <div class="max-h-60 overflow-auto space-y-2 pr-1">
                            @foreach ($departmentOptions as $opt)
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" class="checkbox checkbox-xs"
                                           wire:model.live.debounce="departmentIds" value="{{ $opt['id'] }}">
                                    <span class="text-sm">{{ $opt['name'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Locations -->
                    <div class="border rounded-xl p-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="font-medium">Locations</div>
                            <div class="join">
                                <button class="btn btn-ghost btn-xs join-item" wire:click="$set('locationIds', {{ json_encode(array_column($locationOptions,'id')) }})">All</button>
                                <button class="btn btn-ghost btn-xs join-item" wire:click="$set('locationIds', [])">Clear</button>
                            </div>
                        </div>
                        <div class="max-h-60 overflow-auto space-y-2 pr-1">
                            @foreach ($locationOptions as $opt)
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" class="checkbox checkbox-xs"
                                           wire:model.live.debounce="locationIds" value="{{ $opt['id'] }}">
                                    <span class="text-sm">{{ $opt['name'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Employees -->
                   
                    <div class="border rounded-xl p-3">
  <div class="flex items-center justify-between mb-2">
    <div class="font-medium">Employees</div>
    <div class="join">
      <button class="btn btn-ghost btn-xs join-item"
              wire:click="$set('employeeIds', {{ json_encode(collect($this->employeeOptions)->pluck('id')->values()) }})"
              wire:loading.attr="disabled"
              wire:target="departmentIds,locationIds,employeePickerSearch">
        Page
      </button>
      <button class="btn btn-ghost btn-xs join-item" wire:click="$set('employeeIds', [])">Clear</button>
    </div>
  </div>

  <div class="text-[11px] opacity-70 mb-2">
    Filtered by selected Departments / Locations.
  </div>

  <label class="input input-bordered input-xs mb-2 flex items-center gap-2">
    <svg class="w-3.5 h-3.5 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>
    </svg>
    <input type="text" class="grow" placeholder="Search employees…"
           wire:model.live.debounce.300ms="employeePickerSearch">
  </label>

  <div class="flex items-center justify-between text-[11px] opacity-60 mb-1">
    <span>Showing {{ count($this->employeeOptions) }} (max 50)</span>
    <span wire:loading wire:target="departmentIds,locationIds,employeePickerSearch"
          class="inline-flex items-center gap-1">
      <span class="loading loading-spinner loading-xs"></span> updating…
    </span>
  </div>

  <div class="max-h-60 overflow-auto space-y-2 pr-1"
       wire:loading.class="opacity-60 pointer-events-none"
       wire:target="departmentIds,locationIds,employeePickerSearch">
    @forelse ($this->employeeOptions as $opt)
      <label class="flex items-center gap-2">
        <input type="checkbox" class="checkbox checkbox-xs"
               wire:model.live.debounce="employeeIds" value="{{ $opt['id'] }}">
        <span class="text-sm">
          {{ $opt['name'] }}
          @if (!empty($opt['code'])) <span class="opacity-60">({{ $opt['code'] }})</span> @endif
        </span>
      </label>
    @empty
      <div class="text-xs opacity-70">No employees found.</div>
    @endforelse
  </div>
</div>
                </div>

                <div class="modal-action">
                    <button class="btn btn-ghost" wire:click="clearAllFilters">Clear all</button>
                    <button class="btn" wire:click="closeFilter">Cancel</button>
                    <button class="btn btn-primary" wire:click="applyFilters" wire:loading.attr="disabled" wire:target="applyFilters,departmentIds,locationIds,employeeIds">
                        <span wire:loading.remove wire:target="applyFilters,departmentIds,locationIds,employeeIds">Apply</span>
                        <span class="inline-flex items-center gap-2" wire:loading wire:target="applyFilters,departmentIds,locationIds,employeeIds">
                            <span class="loading loading-spinner loading-xs"></span> Applying…
                        </span>
                    </button>
                </div>
            </div>
            <button class="modal-backdrop" wire:click="closeFilter">close</button>
        </div>
    </div>
    <!-- ========= /FILTER MODAL ========= -->
</div>
