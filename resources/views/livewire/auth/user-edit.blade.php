<div>

  <x-form.container title="Edit User">
    <form wire:submit.prevent="save" class="grid grid-cols-1 md:grid-cols-12 gap-6">

      <x-form.field class="md:col-span-6" title="First Name" for="fname" required>
        <input type="text" wire:model="fname"
          class="input input-bordered w-full @error('fname') input-error border-error @enderror" placeholder="Ahmad" />
      </x-form.field>

      <x-form.field class="md:col-span-6" title="Last Name" for="lname" required>
        <input type="text" wire:model="lname"
          class="input input-bordered w-full @error('lname') input-error border-error @enderror"
          placeholder="Muhamad" />
      </x-form.field>

      <x-form.field class="md:col-span-6" title="User_name" for="user_name" required>
        <input type="text" wire:model="user_name"
          class="input input-bordered w-full @error('user_name') input-error border-error @enderror"
          placeholder="username" />
      </x-form.field>

      <x-form.field class="md:col-span-6" title="Role" for="user_role" required>
        <select wire:model="user_role"
          class="select select-bordered w-full @error('user_role') select-error border-error @enderror">
          <option value="">Choose a role…</option>
          @foreach($this->roleOptions as $r)
          <option value="{{ $r }}">{{ ucfirst($r) }}</option>
          @endforeach
        </select>
      </x-form.field>

      {{-- Optional password change --}}
      <x-form.field class="md:col-span-6" title="New Password (optional)" for="password">
        <input type="password" wire:model="password"
          class="input input-bordered w-full @error('password') input-error border-error @enderror"
          placeholder="••••••••" autocomplete="new-password" />
      </x-form.field>

      <x-form.field class="md:col-span-6" title="Confirm Password" for="password_confirmation">
        <input type="password" wire:model="password_confirmation" class="input input-bordered w-full"
          placeholder="••••••••" autocomplete="new-password" />
      </x-form.field>

      {{-- Department typable select --}}
      <x-form.field class="md:col-span-6" title="Department" for="department_id">
        <div x-data="{ open:false }" @click.outside="open=false" class="relative">
          <input type="text" wire:model.live.debounce.300ms="deptSearch" @focus="open=true"
            class="input input-bordered w-full @error('department_id') input-error border-error @enderror"
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

      <div class="md:col-span-12">

        <x-form.button class="btn-primary btn-block" type="submit" wire:loading.attr="disabled" wire:target="save">
          <span wire:loading.remove wire:target="save">Update</span>
          <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
            <span class="loading loading-spinner loading-xs"></span> Saving…
          </span>
        </x-form.button>
      </div>
    </form>
  </x-form.container>
</div>