<div>


    <x-form.container title="Create Department">
        <form wire:submit='save'>
            <x-form.field title="Name" for="departmentname" required>
                <input type="text" wire:model='departmentname' class="input input-bordered w-full
                       focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none
                       @error('departmentname') input-error border-error @enderror"
                    aria-invalid="@error('departmentname') true @else false @enderror"
                    placeholder="e.g., IT, Finance, HR" />
            </x-form.field>

            <x-form.button>
                Save
            </x-form.button>
        </form>
    </x-form.container>




    <livewire:tables.department-table />
</div>