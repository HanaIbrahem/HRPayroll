<div>
    
    
    <x-form.container title="Create Employee">
        <form wire:submit='save'>
            <x-form.field title="Name" for="employeefname" required>
                <input type="text" wire:model='employeefname' class="input input-bordered w-full
                       focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none
                       @error('employeefname') input-error border-error @enderror"
                    aria-invalid="@error('employeefname') true @else false @enderror"
                    placeholder="e.g., IT, Finance, HR" />
            </x-form.field>

             <x-form.field title="Name" for="employeelname" required>
                <input type="text" wire:model='employeelname' class="input input-bordered w-full
                       focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none
                       @error('employeelname') input-error border-error @enderror"
                    aria-invalid="@error('employeelname') true @else false @enderror"
                    placeholder="e.g., IT, Finance, HR" />
            </x-form.field>

             <x-form.field title="Name" for="employeeposition" required>
                <input type="text" wire:model='employeeposition' class="input input-bordered w-full
                       focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none
                       @error('employeeposition') input-error border-error @enderror"
                    aria-invalid="@error('employeeposition') true @else false @enderror"
                    placeholder="e.g., IT, Finance, HR" />
            </x-form.field>
            <x-form.button>
                Save
            </x-form.button>
        </form>
    </x-form.container>


    <livewire:tables.employee-table/>
</div>
