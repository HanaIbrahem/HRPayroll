<div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Pending Checklists -->
        <div class="card bg-warning/50 shadow-md border border-yellow-300">
            <div class="card-body flex flex-row items-center gap-4">
                <div class="p-3 rounded-full bg-yellow-500/20 text-yellow-600">
                    <!-- Clock Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6l4 2m-4 6a9 9 0 100-18 9 9 0 000 18z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm text-black">Pending Checklists</h2>
                    <p class="text-2xl font-bold text-black">{{ $pendingCount }}</p>
                </div>
            </div>
        </div>

        <!-- Approved Checklists -->
        <div class="card bg-success/50 shadow-md border border-green-300">
            <div class="card-body flex flex-row items-center gap-4">
                <div class="p-3 rounded-full bg-green-300/20 text-green-600">
                    <!-- Check Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2l4-4m5 2a9 9 0 11-18 0a9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm text-black">Approved Checklists</h2>
                    <p class="text-2xl font-bold text-black">{{ $approvedCount }}</p>
                </div>
            </div>
        </div>

        <!-- Total Zones -->
        <div class="card bg-error/50 shadow-md border border-red-300">
            <div class="card-body flex flex-row items-center gap-4">
                <div class="p-3 rounded-full bg-purple-300/20 text-red-900">
                    <!-- Map Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 20l-5.447-2.724A2 2 0 013 15.382V5.618a2 2 0 011.553-1.947L9 2m0 0l6 2m-6-2v18m6-16l5.447 2.724A2 2 0 0121 8.618v9.764a2 2 0 01-1.553 1.947L15 22m0 0V4" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm text-black">Rejected</h2>
                    <p class="text-2xl font-bold text-black">{{ $rejectedCount }}</p>
                </div>
            </div>
        </div>

        <!-- Total Employees -->
        <div class="card bg-info/50 shadow-md border border-blue-300">
            <div class="card-body flex flex-row items-center gap-4">
                <div class="p-3 rounded-full bg-blue-300/20 text-blue-600">
                    <!-- Users Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a4 4 0 00-4-4h-1m-6 6h6m-6 0v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2h5" />
                        <circle cx="9" cy="7" r="4" />
                        <circle cx="17" cy="7" r="4" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm text-black">Employees</h2>
                    <p class="text-2xl font-bold text-black">{{ $employeeCount }}</p>
                </div>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
    <!-- Left: Recent Checklists Table -->
    <div class="card bg-base-100 shadow-md border border-base-300">
        <div class="card-body">
            <h2 class="card-title text-base mb-4">Recent Checklists</h2>
            <div class="overflow-x-auto">
                <table class="table table-sm w-full">
                    <thead class="text-xs uppercase bg-base-200 text-base-content/60">
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Show</th>

                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentChecklists as $a=> $chk)
                            <tr>
                                <td>{{ $a+1 }}</td>
                                <td>{{ $chk->employee->fullname ?? '—' }}</td>
                                <td><x-status :status="$chk->status"/> </td>
                                <td>{{ $chk->created_at->format('Y-m-d H:i') }}</td>
                            

                                <td class="p-4">
                                    <a wire:navigate href="{{ route('checklist.show', $chk->id) }}" class="btn btn-ghost btn-xs"
                                        title="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                            fill="currentColor">
                                            <path
                                                d="M12 5C4.367 5 1 12 1 12s3.367 7 11 7 11-7 11-7-3.367-7-11-7Zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10Z" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-sm text-base-content/60">
                                    No recent checklists found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right: Top Managers Table -->
    <div class="card bg-base-100 shadow-md border border-base-300">
        <div class="card-body">
            <h2 class="card-title text-base mb-4">Top Employee by Uploads</h2>
            <div class="overflow-x-auto">
                <table class="table table-sm w-full">
                    <thead class="text-xs uppercase bg-base-200 text-base-content/60">
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th class="text-right">Uploads</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employeeUploads as $i => $checklistc)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $checklistc->fullname }} </td>
                                <td class="text-right font-semibold">{{ $checklistc->uploads_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-sm text-base-content/60">
                                    No data available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</div>