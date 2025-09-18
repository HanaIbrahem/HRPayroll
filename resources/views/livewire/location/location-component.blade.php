<div>



    <x-form.container title="Create Location">
        <form wire:submit.prevent="save" class="grid grid-cols-1 md:grid-cols-12 gap-6">

            <x-form.field class="md:col-span-6" title="Name" for="locationname" required>
                <input type="text" wire:model="locationname"
                    class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('locationname') input-error border-error @enderror"
                    aria-invalid="@error('locationname') true @else false @enderror" placeholder="Erbil" />
            </x-form.field>

            <x-form.field class="md:col-span-6" title="IQD per KM" for="iqdperkm" required>
                <input type="text" wire:model="iqdperkm"
                    class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('iqdperkm') input-error border-error @enderror"
                    aria-invalid="@error('iqdperkm') true @else false @enderror" placeholder="300 IQD" />
            </x-form.field>

            <x-form.field class="md:col-span-6" title="Mzximum Price" for="maxprice" required>
                <input type="text" wire:model="maxprice"
                    class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('employeeposition') input-error border-error @enderror"
                    aria-invalid="@error('maxprice') true @else false @enderror"
                    placeholder="200000 / 300000 / 700000 ..." />
            </x-form.field>


            <div class="col-span-full mt-3">
                <x-form.button class="btn-primary w-full">
                    Save
                </x-form.button>
            </div>


        </form>
    </x-form.container>

    <livewire:tables.location-table />
</div>