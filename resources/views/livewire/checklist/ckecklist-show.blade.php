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
            <a wire:navigate href="{{ route('checklist') }}" class="btn btn-ghost btn-sm">Back</a>
          
            @if ($checklist->canEdit())
            <a wire:navigate href="{{ route('checklist.edit', $checklist->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                
            @endif

            {{-- Sheet picker --}}
            <select class=" select-sm" wire:model.live.debounce="sheet" title="Choose sheet to preview">
                <option value="" disabled selected>Choose sheet to preview…</option>

                <option value="main">Main</option>
                <option value="Data">Data</option>
            </select>

            @if ($this->excelPath)
                <a href="{{ \Illuminate\Support\Facades\Storage::url($this->excelPath) }}" target="_blank" class="btn btn-outline btn-sm">
                    Download Excel
                </a>
            @endif
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
                            <dt class="text-xs uppercase tracking-wide text-base-content/60">Status</dt>
                            <dd class="col-span-2"><x-status :status="$checklist->status" /></dd>
                        </div>
                        <div class="py-2 grid grid-cols-3 gap-2">
                            <dt class="text-xs uppercase tracking-wide text-base-content/60">Code</dt>
                            <dd class="col-span-2 text-sm">{{ $checklist->employee->code ?? '—' }}</dd>
                        </div>
                        <div class="py-2 grid grid-cols-3 gap-2">
                            <dt class="text-xs uppercase tracking-wide text-base-content/60">Created</dt>
                            <dd class="col-span-2 text-sm">{{ $checklist->created_at?->format('Y-m-d H:i') }}</dd>
                        </div>
                        <div class="py-2 grid grid-cols-3 gap-2">
                            <dt class="text-xs uppercase tracking-wide text-base-content/60">Updated</dt>
                            <dd class="col-span-2 text-sm">{{ $checklist->updated_at?->format('Y-m-d H:i') }}</dd>
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
        </div>
    </div>

    {{-- NEW ROW: Excel preview (full width, below the grid) --}}
    @if ($this->excelPath)
        <div class="w-full">
            <div class="card bg-base-100 border border-base-300/60">
                <div class="card-body">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="card-title text-base">Excel Preview</h2>
                        <div class="text-xs text-base-content/60 truncate">
                            <span class="opacity-70">File:</span>
                            <code class="truncate">{{ $this->excelPath }}</code>
                            <span class="mx-2">·</span>
                            <span class="opacity-70">Sheet:</span>
                            <code>{{ $sheet ?: '—' }}</code>
                        </div>
                    </div>

                    @if ($sheet)
                        {{-- Use kebab-case props and the selected sheet --}}
                        <x-excel.preview :file-path="$checklist->filename" :sheet="$sheet" :max-rows="500" class="mt-3" />
                    @else
                        <div class="alert alert-ghost mt-3">
                            Select a sheet from the dropdown to preview.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
