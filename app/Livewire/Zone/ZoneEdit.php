<?php

namespace App\Livewire\Zone;

use Livewire\Component;
use App\Models\Zone;
class ZoneEdit extends Component
{
     public Zone $zone;

    public $from_zone = '';
    public $to_zone = '';
    public $code = '';
    public $km = null;
    public $fixed_rate = null;
    public $between_zone = null;
    public $description = '';

    public function mount(Zone $zone): void
    {
        $this->zone = $zone;
        $this->from_zone    = $zone->from_zone;
        $this->to_zone      = $zone->to_zone;
        $this->code         = $zone->code;
        $this->km           = $zone->km;
        $this->fixed_rate   = $zone->fixed_rate;
        $this->between_zone = $zone->between_zone;
        $this->description  = $zone->description;
    }

    protected function rules(): array
    {
        return [
            'from_zone'     => ['required','string','max:30'],
            'to_zone'       => ['required','string','max:30'],
            'code'          => ['required','string','max:20'],
            'km'            => ['nullable','numeric','min:0'],
            'fixed_rate'    => ['nullable','integer','min:0'],
            'between_zone'  => ['nullable','integer','min:0'],
            'description'   => ['nullable','string','max:2000'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->km=='' && $this->fixed_rate=='') {

            $this->addError('km', 'KM or Fixd_Rate one if them required ');
            $this->addError('fixed_rate', 'KM or Fixd_Rate one if them required ');

            return;
        }
        $this->zone->update($data);

        $this->dispatch('toast', type:'success', message:'Zone updated.');
    }
    public function render()
    {
        return view('livewire.zone.zone-edit');
    }
}
