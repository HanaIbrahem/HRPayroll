<div class="space-y-4">
    {{-- Header / Global controls --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <h2 class="text-2xl font-semibold tracking-tight">Departments</h2>

        <div class="flex items-center gap-2">
            {{-- Global search --}}
            <label class="input input-bordered input-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 opacity-70" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 1 0 4.2 12.06l3.72 3.72a.75.75 0 1 0 1.06-1.06l-3.72-3.72a6.75 6.75 0 0 0-5.26-11zM5.25 10.5a5.25 5.25 0 1 1 10.5 0 5.25 5.25 0 0 1-10.5 0z" clip-rule="evenodd"/>
                </svg>
                <input type="text" class="grow" placeholder="Global search: name / id / active"
                       wire:model.debounce.300ms="q" />
            </label>

            <select class="select select-bordered select-sm" wire:model="perPage">
                <option value="5">5 / page</option>
                <option value="10">10 / page</option>
                <option value="25">25 / page</option>
                <option value="50">50 / page</option>
            </select>
        </div>
    </div>

    {{-- Specific filters --}}
    <div class="card bg-base-100 border border-base-300/60">
        <div class="card-body py-3">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-2">
                <label class="form-control w-full">
                    <span class="label-text text-xs">Name</span>
                    <input type="text" class="input input-bordered input-sm" placeholder="Filter by name"
                           wire:model.debounce.300ms="name" />
                </label>

                <label class="form-control w-full">
                    <span class="label-text text-xs">Status</span>
                    <select class="select select-bordered select-sm" wire:model="status">
                        <option value="">All</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </label>

                <label class="form-control w-full">
                    <span class="label-text text-xs">From</span>
                    <input type="date" class="input input-bordered input-sm" wire:model="from" />
                </label>

                <label class="form-control w-full">
                    <span class="label-text text-xs">To</span>
                    <input type="date" class="input input-bordered input-sm" wire:model="to" />
                </label>

                <div class="flex items-end">
                    <button class="btn btn-sm" wire:click="clearFilters">Reset</button>
                </div>
            </div>
        </div>
    </div>

    @if (session('ok'))
        <div class="alert alert-success shadow-sm">
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    {{-- Table --}}
    <div class="card bg-base-100 border border-base-300/60">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-8">#</th>
                            <th>
                                <button class="btn btn-ghost btn-xs" wire:click="sortBy('name')">
                                    Name
                                    @if($sortField === 'name')
                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </button>
                            </th>
                            <th class="text-center">
                                <button class="btn btn-ghost btn-xs" wire:click="sortBy('is_active')">
                                    Status
                                    @if($sortField === 'is_active')
                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </button>
                            </th>
                            <th class="hidden md:table-cell">
                                <button class="btn btn-ghost btn-xs" wire:click="sortBy('created_at')">
                                    Created
                                    @if($sortField === 'created_at')
                                        <span class="ml-1">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </button>
                            </th>
                            <th class="w-40 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($departments as $i => $dep)
                            <tr wire:key="dep-{{ $dep->id }}">
                                <td>{{ $departments->firstItem() + $i }}</td>
                                <td class="font-medium">{{ $dep->name }}</td>
                                <td class="text-center">
                                    <input type="checkbox"
                                           class="toggle toggle-sm"
                                           @checked($dep->is_active)
                                           wire:change="toggleActive({{ $dep->id }})" />
                                </td>
                                <td class="hidden md:table-cell">
                                    <span class="badge badge-ghost">{{ $dep->created_at->format('Y-m-d') }}</span>
                                </td>
                                <td class="text-right space-x-1">
                                    <button class="btn btn-sm btn-outline">Edit</button>
                                    <button class="btn btn-sm btn-error"
                                            onclick="if(confirm('Delete this department?')) { Livewire.dispatch('call', { to: '{{ $this->getId() }}', method: 'delete', params: [{{ $dep->id }}] }); }">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="p-6 text-center text-base-content/60">No departments found.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-t border-base-300/60">
                {{ $departments->onEachSide(1)->links() }}
            </div>
        </div>
    </div>

    {{-- Loading --}}
    <div wire:loading.delay class="fixed bottom-4 right-4">
        <span class="loading loading-spinner loading-md"></span>
    </div>
</div>
