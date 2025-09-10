<div>
  <x-form.container title="Edit Checklist" description="Update the employee, date range, note, or replace the file if needed.">
    <form wire:submit.prevent="update" class="grid grid-cols-1 md:grid-cols-12 gap-6">

      {{-- Employee --}}
      <x-form.field class="md:col-span-6" title="Employee" for="employee_id" required>
        <div x-data="{ open:false }" @click.outside="open=false" class="relative">
          <input
            type="text"
            id="employeeSearch"
            name="employeeSearch"
            wire:model.live.debounce="employeeSearch"
            @focus="open=true"
            class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('employee_id') input-error border-error @enderror"
            placeholder="Type to search your employees by name/code..."
          >
          <input type="hidden" wire:model="employee_id" id="employee_id" name="employee_id">

          {{-- Suggestions --}}
          <div x-show="open" x-transition class="absolute z-20 mt-1 w-full rounded-xl border border-base-300 bg-base-100 shadow" style="display:none;">
            @if ($employeeSearch === '')
              <div class="p-2 text-sm text-base-content/60">Start typing...</div>
            @elseif ($employees->isEmpty())
              <div class="p-2 text-sm text-base-content/60">No matches</div>
            @else
              <ul class="menu menu-sm">
                @foreach ($employees as $e)
                  @php $label = trim($e->first_name.' '.$e->last_name).($e->code ? " ({$e->code})" : ''); @endphp
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

  
      {{-- Replace file (optional) --}}
      <x-form.field class="md:col-span-6" title="Replace File (optional)" for="file">
        <div
          x-data="{ uploading:false, progress:0 }"
          x-on:livewire-upload-start="uploading=true"
          x-on:livewire-upload-finish="uploading=false; progress=0"
          x-on:livewire-upload-error="uploading=false"
          x-on:livewire-upload-progress="progress=$event.detail.progress"
        >
          <input
            id="file"
            name="file"
            type="file"
            wire:model="file"
            accept=".xlsx,.xls,.csv"
            class="file-input file-input-bordered w-full @error('file') file-input-error border-error @enderror"
          />

          <div class="mt-2" x-show="uploading">
            <progress class="progress w-full" max="100" x-bind:value="progress"></progress>
          </div>

          @if ($file)
            <div class="mt-2 text-sm text-base-content/70">
              New file: {{ $file->getClientOriginalName() }}
              <span class="opacity-70">({{ number_format($file->getSize() / 1024, 1) }} KB)</span>
            </div>
          @endif
        </div>
      </x-form.field>

      {{-- NEW: Start Date --}}
      <x-form.field class="md:col-span-6" title="Start Date" for="start_date" required>
        <input
          type="date"
          id="start_date"
          name="start_date"
          wire:model.live="start_date"
          class="input input-bordered w-full @error('start_date') input-error border-error @enderror"
        />
      </x-form.field>

      {{-- NEW: End Date --}}
      <x-form.field class="md:col-span-6" title="End Date" for="end_date" required>
        <input
          type="date"
          id="end_date"
          name="end_date"
          wire:model.live="end_date"
          class="input input-bordered w-full @error('end_date') input-error border-error @enderror"
        />
      </x-form.field>

      {{-- Note --}}
      <x-form.field class="md:col-span-12" title="Note" for="note">
        <textarea
          id="note"
          name="note"
          rows="3"
          wire:model.live.debounce.300ms="note"
          class="textarea textarea-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('note') textarea-error border-error @enderror"
          placeholder="Short note..."
        ></textarea>
      
      </x-form.field>

      {{-- Live summary --}}
      <div class="md:col-span-12">
        <div class="rounded-xl border border-base-300 p-4 bg-base-100">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
            <div>
              <div class="font-semibold">Selected Employee</div>
              <div class="text-base-content/70">
                {{ $employee_id ? $employeeSearch : '—' }}
              </div>
            </div>
            <div>
              <div class="font-semibold">New File</div>
              <div class="text-base-content/70">
                @if ($file)
                  {{ $file->getClientOriginalName() }}
                  <span class="opacity-70">({{ number_format($file->getSize() / 1024, 1) }} KB)</span>
                @else
                  —
                @endif
              </div>
            </div>
            <div>
             
            </div>
            <div>
              <div class="font-semibold">Note</div>
              <div class="text-base-content/70">
                {{ $note !== '' ? $note : '—' }}
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Actions --}}
      <div class="md:col-span-12 flex flex-col sm:flex-row gap-3">
        <div class="w-50">
          <button
            type="submit"
            class="btn btn-primary btn-block"
            wire:loading.attr="disabled"
            wire:target="update"
          >
            <span wire:loading.remove wire:target="update">Update</span>
            <span wire:loading wire:target="update" class="inline-flex items-center gap-2">
              <span class="loading loading-spinner loading-xs"></span>
              Validating &amp; saving…
            </span>
          </button>
        </div>

        <a class="btn btn-ghost" wire:navigate href="{{ route('checklist.show', $checklist) }}">
          Cancel
        </a>
      </div>

    </form>
  </x-form.container>
</div>
