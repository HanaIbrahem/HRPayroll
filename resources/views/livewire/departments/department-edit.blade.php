<div>
    
     <x-form.container title="Edit Department">
        <form wire:submit='save'>
            <x-form.field title="Name" for="name" required>
                <input type="text" wire:model='name' class="input input-bordered w-full
                       focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none
                       @error('name') input-error border-error @enderror"
                    aria-invalid="@error('name') true @else false @enderror"
                    placeholder="e.g., IT, Finance, HR" />
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
