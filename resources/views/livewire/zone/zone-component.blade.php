<div>


    {{-- resources/views/livewire/zones/zone-create.blade.php --}}
    <x-form.container title="Create Zone" description="Define distances and rates between zones.">
        <form wire:submit.prevent="save" class="grid grid-cols-1 md:grid-cols-12 gap-6">

            <x-form.field class="md:col-span-6" title="From Zone" for="from_zone" required>
                <input id="from_zone" name="from_zone" type="text" wire:model.debounce.300ms="from_zone"
                    class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('from_zone') input-error border-error @enderror"
                    placeholder="e.g., Erbil" />
            </x-form.field>

            <x-form.field class="md:col-span-6" title="To Zone" for="to_zone" required>
                <input id="to_zone" name="to_zone" type="text" wire:model.debounce.300ms="to_zone"
                    class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('to_zone') input-error border-error @enderror"
                    placeholder="e.g., Duhok" />
            </x-form.field>

            <x-form.field class="md:col-span-6" title="Zone Code" for="code" required>
                <input id="code" name="code" type="text" wire:model.debounce.300ms="code"
                    class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('code') input-error border-error @enderror"
                    placeholder="e.g., Z1205" />
            </x-form.field>

            <x-form.field class="md:col-span-6" title="KM (distance)" for="km">
                <input id="km" name="km" type="number" step="0.1" min="0" wire:model.debounce.300ms="km"
                    class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('km') input-error border-error @enderror"
                    placeholder="e.g., 180.5" />
            </x-form.field>

            <x-form.field class="md:col-span-6" title="Fixed Rate (IQD)" for="fixed_rate">
                <input id="fixed_rate" name="fixed_rate" type="number" min="0" wire:model.debounce.300ms="fixed_rate"
                    class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('fixed_rate') input-error border-error @enderror"
                    placeholder="e.g., 10000" />
            </x-form.field>

            <x-form.field class="md:col-span-6" title="Between Zone (IQD)" for="between_zone">
                <input id="between_zone" name="between_zone" type="number" min="0"
                    wire:model.debounce.300ms="between_zone"
                    class="input input-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('between_zone') input-error border-error @enderror"
                    placeholder="e.g., 15000" />
            </x-form.field>

            <x-form.field class="md:col-span-12" title="Description" for="description" required>
                <textarea id="description" name="description" rows="3" wire:model.debounce.300ms="description"
                    class="textarea textarea-bordered w-full focus:border-primary focus:ring focus:ring-primary/20 focus:outline-none @error('description') textarea-error border-error @enderror"
                    placeholder="Short description..."></textarea>
            </x-form.field>

            <div class="md:col-span-12">
                <x-form.button class="btn-primary btn-block" type="submit">Save</x-form.button>
            </div>
        </form>
    </x-form.container>

    {{-- table --}}
    <livewire:tables.zone-table />
</div>