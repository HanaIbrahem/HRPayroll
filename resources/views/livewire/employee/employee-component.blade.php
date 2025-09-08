<div>



  <x-form.container title="Create Employee">
    <form wire:submit.prevent="save" class="grid grid-cols-1 md:grid-cols-12 gap-6">

      <x-form.field class="md:col-span-6" title="First Name" for="employeefname" required>
        <input type="text" wire:model="employeefname"
          class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('employeefname') input-error border-error @enderror"
          aria-invalid="@error('employeefname') true @else false @enderror" placeholder="Ahmad" />
      </x-form.field>

      <x-form.field class="md:col-span-6" title="Last Name" for="employeelname" required>
        <input type="text" wire:model="employeelname"
          class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('employeelname') input-error border-error @enderror"
          aria-invalid="@error('employeelname') true @else false @enderror" placeholder="Muhamad" />
      </x-form.field>

      <x-form.field class="md:col-span-6" title="Position" for="employeeposition" required>
        <input type="text" wire:model="employeeposition"
          class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('employeeposition') input-error border-error @enderror"
          aria-invalid="@error('employeeposition') true @else false @enderror"
          placeholder="Specialist / Manager / Officer ..." />
      </x-form.field>

      <x-form.field class="md:col-span-6" title="Employee Code" for="code" required>
        <input type="text" wire:model="code"
          class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('code') input-error border-error @enderror"
          aria-invalid="@error('code') true @else false @enderror" placeholder="EMP-00001" />
      </x-form.field>

      {{-- Manager typable select --}}
      <x-form.field class="md:col-span-6" title="Manager" for="manager_id" required>
        <div x-data="{ open:false }" x-cloak @click.outside="open=false" @keydown.escape.window="open=false"
          class="relative">
          <input type="text" wire:model.live.debounce.300ms="managerSearch" @focus="open=true" autocomplete="off"
            class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('manager_id') input-error border-error @enderror"
            placeholder="Type to search users...">
          <input type="hidden" wire:model="manager_id">

          <div x-show="open" x-transition
            class="absolute z-20 mt-1 w-full rounded-xl border border-base-300 bg-base-100 shadow"
            style="display:none;">
            @if($managerSearch === '')
            <div class="p-2 text-sm text-base-content/60">Start typing...</div>
            @elseif($managerResults->isEmpty())
            <div class="p-2 text-sm text-base-content/60">No matches</div>
            @else
            <ul class="menu menu-sm">
              @foreach($managerResults as $m)
              @php $label = trim(($m->first_name ?? '') . ' ' . ($m->last_name ?? '')); @endphp
              <li wire:key="mgr-{{ $m->id }}">
                <button type="button" @click="$wire.chooseManager({{ $m->id }}, @js($label)); open=false">
                  {{ $label }}
                </button>
              </li>
              @endforeach
            </ul>
            @endif
          </div>
        </div>
      </x-form.field>

      <x-form.field class="md:col-span-6" title="Location" for="location_id" required>
        <div x-data="{ open:false }" x-cloak @click.outside="open=false" @keydown.escape.window="open=false"
          class="relative">
          <input type="text" wire:model.live.debounce.300ms="locationSearch" @focus="open=true" autocomplete="off"
            class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('location_id') input-error border-error @enderror"
            placeholder="Type to search locations...">
          <input type="hidden" wire:model="location_id">

          <div x-show="open" x-transition
            class="absolute z-20 mt-1 w-full rounded-xl border border-base-300 bg-base-100 shadow"
            style="display:none;">
            @if($locationSearch === '')
            <div class="p-2 text-sm text-base-content/60">Start typing...</div>
            @elseif($locationResults->isEmpty())
            <div class="p-2 text-sm text-base-content/60">No matches</div>
            @else
            <ul class="menu menu-sm">
              @foreach($locationResults as $loc)
              @php $label = $loc->name ?? ''; @endphp
              <li wire:key="loc-{{ $loc->id }}"> {{-- ✅ unique key prefix --}}
                <button type="button" @click="$wire.chooseLocation({{ $loc->id }}, @js($label)); open=false">
                  {{ $label }}
                </button>
              </li>
              @endforeach
            </ul>
            @endif
          </div>
        </div>
      </x-form.field>

      <div class="md:col-span-12">
        <x-form.button class="btn-primary btn-block" type="submit">
          Save
        </x-form.button>
      </div>
    </form>
  </x-form.container>




  <livewire:tables.employee-table />
</div>