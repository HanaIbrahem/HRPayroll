<div>

  <x-form.container title="Create Checklist" description="Choose employee and upload the visits file.">
    <form wire:submit.prevent="save" class="grid grid-cols-1 md:grid-cols-12 gap-6">

      {{-- Employee (typable select restricted to current manager) --}}
      <x-form.field class="md:col-span-6" title="Employee" for="employee_id" required>
        <div x-data="{ open:false }" @click.outside="open=false" class="relative">
          <input type="text" id="employeeSearch" name="employeeSearch" wire:model.live.debounce="employeeSearch"
            @focus="open=true" class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none
                      @error('employee_id') input-error border-error @enderror"
            placeholder="Type to search your employees by name/code...">
          <input type="hidden" wire:model="employee_id" id="employee_id" name="employee_id">

          {{-- employeee Suggestions --}}
          <div x-show="open" x-transition
            class="absolute z-20 mt-1 w-full rounded-xl border border-base-300 bg-base-100 shadow"
            style="display:none;">
            @if($employeeSearch === '')
            <div class="p-2 text-sm text-base-content/60">Start typing...</div>
            @elseif($employees->isEmpty())
            <div class="p-2 text-sm text-base-content/60">No matches</div>
            @else
            <ul class="menu menu-sm">
              @foreach($employees as $e)
              @php
              $label = trim($e->first_name.' '.$e->last_name).($e->code ? " ({$e->code})" : '');
              @endphp
              <li>
                <button type="button" @click="$wire.chooseEmployee({{ $e->id }}, @js($label)); open=false">
                  {{ $label }}
                </button>
              </li>
              @endforeach
            </ul>
            @endif
          </div>
        </div>
      </x-form.field>

      {{-- Excel file --}}
      <x-form.field class="md:col-span-6" title="Excel File" for="file" required>
        <div x-data="{ uploading:false, progress:0 }" x-on:livewire-upload-start="uploading=true"
          x-on:livewire-upload-finish="uploading=false; progress=0" x-on:livewire-upload-error="uploading=false"
          x-on:livewire-upload-progress="progress=$event.detail.progress">

          <input id="file" name="file" type="file" wire:model="file" accept=".xlsx,.xls,.csv" class="file-input file-input-bordered w-full
                      @error('file') file-input-error border-error @enderror" />

          <div class="mt-2" x-show="uploading">
            <progress class="progress w-full" max="100" x-bind:value="progress"></progress>
          </div>
        </div>

      </x-form.field>

      {{-- NEW: Start Date --}}
      <x-form.field class="md:col-span-6" title="Start Date" for="start_date" required>
        <input type="date" id="start_date" name="start_date" wire:model.live="start_date"
          class="input input-bordered w-full @error('start_date') input-error border-error @enderror" />
       
      </x-form.field>

      {{-- NEW: End Date --}}
      <x-form.field class="md:col-span-6" title="End Date" for="end_date" required>
        <input type="date" id="end_date" name="end_date" wire:model.live="end_date"
          class="input input-bordered w-full @error('end_date') input-error border-error @enderror" />
       
      </x-form.field>

      <x-form.field class="md:col-span-12" title="Note" for="note">
        <textarea id="description" name="note" rows="3" wire:model.debounce.300ms="note"
          class="textarea textarea-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('description') textarea-error border-error @enderror"
          placeholder="Short note..."></textarea>
      </x-form.field>

      {{-- Live Preview --}}
      <div class="md:col-span-12">
        <div class="rounded-xl border border-base-300 p-4 bg-base-100">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
              <div class="font-semibold">Selected Employee</div>
              <div class="text-base-content/70">
                {{ $employee_id ? $employeeSearch : '—' }}
              </div>
            </div>
            <div>
              <div class="font-semibold">File</div>
              <div class="text-base-content/70">
                @if($file)
                {{ $file->getClientOriginalName() }}
                <span class="opacity-70">
                  ({{ number_format($file->getSize() / 1024, 1) }} KB)
                </span>
                @else
                —
                @endif
              </div>
            </div>
            <div>
              <div class="font-semibold">Note</div>
              <div class="text-base-content/70">
                {{ $note ? $note : '—' }}
              </div>
            </div>
            <div>
              <div class="font-semibold">Status</div>
              <div class="text-base-content/70">open</div>
            </div>
          </div>
        </div>
      </div>

      {{-- Submit --}}
      <div class="w-50">
        <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled" wire:target="save">
          <span wire:loading.remove wire:target="save">Save</span>
          <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
            <span class="loading loading-spinner loading-xs"></span>
            Validating & saving…
          </span>
        </button>
      </div>
    </form>
  </x-form.container>

</div>