<?php

namespace App\Livewire\Zone;

use Livewire\Component;
use App\Models\Zone;
class ZoneComponent extends Component
{
    public $from_zone = '';
    public $to_zone = '';
    public $code = '';
    public $km = null;
    public $fixed_rate = null;
    public $between_zone = null;
    public $description = '';

    protected function rules(): array
    {
        return [
            'from_zone'     => ['required','string','max:20'],
            'to_zone'       => ['required','string','max:20'],
            'code'          => ['required','string','max:20'],
            'km'            => ['nullable','numeric','min:0'],
            'fixed_rate'    => ['nullable','integer','min:0'],
            'between_zone'  => ['nullable','integer','min:0'],
            'description'   => ['required','string','max:2000'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        Zone::create($data);

        // reset + toast + notify any listening table
        $this->reset(['from_zone','to_zone','code','km','fixed_rate','between_zone','description']);
        $this->resetValidation();
        $this->dispatch('toast', type:'success', message:'Zone created.');
        $this->dispatch('zones:updated');
    }

    public function render()
    {
        return view('livewire.zone.zone-component');
    }
}
