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

            <x-form.button>
                Update
            </x-form.button>
        </form>
    </x-form.container>

</div>
