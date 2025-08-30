<div>
  <x-form.container title="Edit Employee">
    <form wire:submit.prevent="save" class="grid grid-cols-1 md:grid-cols-12 gap-6">

      <x-form.field class="md:col-span-6" title="First Name" for="first_name" required>
        <input id="first_name" name="first_name" type="text" wire:model="first_name"
               class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('first_name') input-error border-error @enderror"
               aria-invalid="@error('first_name') true @else false @enderror"
               placeholder="Ahmad" />
      </x-form.field>

      <x-form.field class="md:col-span-6" title="Last Name" for="last_name" required>
        <input id="last_name" name="last_name" type="text" wire:model="last_name"
               class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('last_name') input-error border-error @enderror"
               aria-invalid="@error('last_name') true @else false @enderror"
               placeholder="Muhamad" />
      </x-form.field>

      <x-form.field class="md:col-span-6" title="Position" for="position">
        <input id="position" name="position" type="text" wire:model="position"
               class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('position') input-error border-error @enderror"
               aria-invalid="@error('position') true @else false @enderror"
               placeholder="Specialist / Manager / Officer ..." />
      </x-form.field>

      <x-form.field class="md:col-span-6" title="Employee Code" for="code">
        <input id="code" name="code" type="text" wire:model="code"
               class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('code') input-error border-error @enderror"
               aria-invalid="@error('code') true @else false @enderror"
               placeholder="EMP-00001" />
      </x-form.field>

      {{-- Manager (typable select) --}}
      
         <x-form.field class="md:col-span-6" title="Manager" for="user_id" required>
        <div x-data="{ open:false }" x-cloak @click.outside="open=false" @keydown.escape.window="open=false" class="relative">
          <input type="text" id="managerSearch" name="managerSearch"
                 wire:model.live.debounce.300ms="managerSearch"
                 @focus="open=true" autocomplete="off"
                 class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('user_id') input-error border-error @enderror"
                 placeholder="Type to search users...">
          <input type="hidden" wire:model="user_id" id="user_id" name="user_id" />

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
                  @php $label = trim(($m->first_name ?? '').' '.($m->last_name ?? '')); @endphp
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

      <div class="md:col-span-12">
        <x-form.button class="btn-primary btn-block" type="submit">
          Update
        </x-form.button>
      </div>
    </form>
  </x-form.container>
</div>
