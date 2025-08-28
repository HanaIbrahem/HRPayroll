<div>



  <x-form.container title="Create Employee">
    {{-- 12-col grid gives you control: put two fields on same row (6+6), full width (12), etc. --}}
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

      <x-form.field class="md:col-span-6" title="Position" for="employeeposition">
        <input type="text" wire:model="employeeposition"
          class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('employeeposition') input-error border-error @enderror"
          aria-invalid="@error('employeeposition') true @else false @enderror"
          placeholder="Specialist / Manager / Officer ..." />
      </x-form.field>

      <x-form.field class="md:col-span-6" title="Employee Code" for="code">
        <input type="text" wire:model="code"
          class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('code') input-error border-error @enderror"
          aria-invalid="@error('code') true @else false @enderror" placeholder="EMP-00001" />
      </x-form.field>


      {{-- Department typable select --}}
      <x-form.field class="md:col-span-6" title="Department" for="department_id" required>
        <div x-data="{ open:false }" @click.outside="open=false" class="relative">
          <input type="text" wire:model.live.debounce.300ms="deptSearch" @focus="open=true"
            class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('department_id') input-error border-error @enderror"
            placeholder="Type to search departments...">
          <input type="hidden" wire:model="department_id">

          <div x-show="open" x-transition
            class="absolute z-20 mt-1 w-full rounded-xl border border-base-300 bg-base-100 shadow"
            style="display:none;">
            @if($deptSearch === '')
            <div class="p-2 text-sm text-base-content/60">Start typing...</div>
            @elseif($deptResults->isEmpty())
            <div class="p-2 text-sm text-base-content/60">No matches</div>
            @else
            <ul class="menu menu-sm">
              @foreach($deptResults as $d)
              <li>
                <button type="button" @click="$wire.chooseDepartment({{ $d->id }}, @js($d->name)); open=false">
                  {{ $d->name }}
                </button>
              </li>
              @endforeach
            </ul>
            @endif
          </div>
        </div>
      </x-form.field>


      {{-- Manager typable select --}}
      <x-form.field class="md:col-span-6" title="Manager" for="manager_id" required>
        <div x-data="{ open:false }" @click.outside="open=false" class="relative">
          <input type="text" wire:model.live.debounce.300ms="managerSearch" @focus="open=true"
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
              <li>
                <button type="button" @click="$wire.chooseManager({{ $m->id }}, @js($m->name)); open=false">
                  {{ $m->name }}
                </button>
              </li>
              @endforeach

            </ul>
            @endif
          </div>
        </div>
      </x-form.field>

      {{-- Submit button: full width / block --}}
      <div class="md:col-span-12">
        <x-form.button class="btn-primary btn-block" type="submit">
          Save
        </x-form.button>
      </div>
    </form>
  </x-form.container>



  <livewire:tables.employee-table />
</div>