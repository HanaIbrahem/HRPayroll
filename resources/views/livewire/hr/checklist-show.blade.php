<div class="">
    <!-- Top bar -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="breadcrumbs text-sm">
                <ul>
                    <li><a wire:navigate href="{{ route('checklist') }}">Checklists</a></li>
                    <li>Show</li>
                </ul>
            </div>
            <h1 class="text-2xl font-semibold">
                Checklist #{{ $checklist->id }}
            </h1>
            <div class="mt-1 flex items-center gap-2 text-sm text-base-content/60">
                <x-status :status="$checklist->status" />
                <span>• Created {{ $checklist->created_at?->format('Y-m-d H:i') }}</span>
                <span>• Updated {{ $checklist->updated_at?->format('Y-m-d H:i') }}</span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">

            

           

            @if ($this->excelPath)
            <a href="{{ \Illuminate\Support\Facades\Storage::url($this->excelPath) }}" target="_blank"
                class="btn btn-outline btn-sm">
                Download Excel
            </a>
            @endif
             <button wire:click="exportPdf" class="btn btn-primary btn-sm">PDF Report</button>
      <button wire:click="exportXlsx" class="btn btn-success btn-sm">Excel Report</button>
        </div>
    </div>

    <!-- Content -->
    <div class="">
        {{-- main content --}}
        <div class="col-12">
            <div class="card bg-base-100 border border-base-300/60">
                <div class="card-body">
                    <h2 class="card-title text-base">Overview</h2>
                    <dl class="mt-2 divide-y divide-base-300/60">
                        <div class="py-2 grid grid-cols-3 gap-2">
                            <dt class="text-xs uppercase tracking-wide text-base-content/60">Employee</dt>
                            <dd class="col-span-2 text-sm">{{ data_get($checklist, 'employee.fullname') ?? '—' }}</dd>
                        </div>
                       
                        <div class="py-2 grid grid-cols-3 gap-2">
                            <dt class="text-xs uppercase tracking-wide text-base-content/60">Employee Code</dt>
                            <dd class="col-span-2 text-sm">{{ $checklist->employee->code ?? '—' }}</dd>
                        </div>
                        <div class="py-2 grid grid-cols-3 gap-2">
                            <dt class="text-xs uppercase tracking-wide text-base-content/60">Employee Location</dt>
                            <dd class="col-span-2 text-sm">{{ $checklist->employee->location->name ?? '—' }}</dd>
                        </div>
                         <div class="py-2 grid grid-cols-3 gap-2">
                            <dt class="text-xs uppercase tracking-wide text-base-content/60">Employee Manager</dt>
                            <dd class="col-span-2 text-sm">{{ $checklist->user->fullname ?? '—' }}</dd>
                        </div>
                          <div class="py-2 grid grid-cols-3 gap-2">
                            <dt class="text-xs uppercase tracking-wide text-base-content/60">Employee Department</dt>
                            <dd class="col-span-2 text-sm">{{ $checklist->employee->department->name ?? '—' }}</dd>
                        </div>
                        <div class="py-2 grid grid-cols-3 gap-2">
                            <dt class="text-xs uppercase tracking-wide text-base-content/60">Created</dt>
                            <dd class="col-span-2 text-sm">{{ $checklist->created_at?->format('Y-m-d H:i') }}</dd>
                        </div>
                        <div class="py-2 grid grid-cols-3 gap-2">
                            <dt class="text-xs uppercase tracking-wide text-base-content/60">Updated</dt>
                            <dd class="col-span-2 text-sm">{{ $checklist->updated_at?->format('Y-m-d H:i') }}</dd>
                        </div>
                         <div class="py-2 grid grid-cols-3 gap-2">
                            <dt class="text-xs uppercase tracking-wide text-base-content/60">Status</dt>
                            <dd class="col-span-2">
                                <x-status :status="$checklist->status" />
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
             <div class="card bg-base-100 border border-base-300/60">
                <div class="card-body">
                    <h2 class="card-title text-base">Notes</h2>
                    <p class="text-sm leading-6 text-base-content/80">
                        {{ $checklist->note ?? '—' }}
                    </p>
                </div>
            </div>

            @if ($checklist->status !== 'open')
            <div class="card bg-base-100 border border-base-300/60">
                <div class="card-body p-0">
                    <div class="px-4 pt-4">
                        <h2 class="card-title text-base">Visited Zones</h2>
                    </div>

                    <div class="overflow-x-auto w-full">
                        <table class="table table-sm table-zebra w-full">
                            <thead class="bg-base-200 top-0 z-10">
                                <tr>
                                    <th class="px-3 py-2 text-xs font-semibold text-base-content/70">#</th>
                                    <th class="px-3 py-2 text-xs font-semibold text-base-content/70">Code</th>
                                    <th class="px-3 py-2 text-xs font-semibold text-base-content/70 ">From</th>
                                    <th class="px-3 py-2 text-xs font-semibold text-base-content/70 ">To</th>
                                    <th class="px-3 py-2 text-xs font-semibold text-base-content/70 text-right">ZoneCount</th>
                                    <th class="px-3 py-2 text-xs font-semibold text-base-content/70 text-right">RepeatZone</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                @forelse ($checklist->visitedZones as $i => $vz)
                                <tr>
                                    <td class="px-3 py-2 text-base-content/70">{{ $i + 1 }}</td>
                                    <td class="px-3 py-2">
                                        {{ data_get($vz, 'zone.code', '—') }}
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ data_get($vz, 'zone.from_zone', '—') }}
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ data_get($vz, 'zone.to_zone', '—') }}
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        {{ number_format((int)$vz->zone_count) }}
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        {{ number_format((int)$vz->repeat_count) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-4 text-center text-base-content/60">
                                        No visited zones.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>

                            @if ($checklist->visitedZones->isNotEmpty())
                            <tfoot>
                                <tr class="bg-base-200/60">
                                    <th class="px-3 py-2 text-xs font-semibold text-base-content/70" colspan="2">Totals
                                    </th>
                                    <th></th>
                                    <th></th>
                                    <th class="px-3 py-2 text-right">
                                        {{ number_format($checklist->visitedZones->sum('zone_count')) }}
                                    </th>
                                    <th class="px-3 py-2 text-right">
                                        {{ number_format($checklist->visitedZones->sum('repeat_count')) }}
                                    </th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
            @endif



           
        </div>
    </div>

  
</div>