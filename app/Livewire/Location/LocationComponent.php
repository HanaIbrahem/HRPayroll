<?php

namespace App\Livewire\Location;

use Livewire\Component;
use App\Models\Location;
class LocationComponent extends Component
{

    public $locationname;
    public $iqdperkm;
    public $maxprice;
    public function save()
    {

        $this->validate();
        Location::create([
            'name'=>$this->locationname,
            'iqd_per_km'=>$this->iqdperkm,
            'maximum_price'=>$this->maxprice,
        ]);
        $this->reset([
            'locationname',
            'maxprice',
            'iqdperkm'
        ]);
        $this->resetValidation();

        // toast + refresh any table listening
        $this->dispatch('toast', type: 'success', message: 'Location created.');
        $this->dispatch('locations:updated');

    }

    public function rules(): array
    {
       return [
        'locationname' => ['required','string','min:4','max:50'],

        'iqdperkm'     => ['required','integer','min:100','max:2000'],

        'maxprice'     => ['required','integer','between:50000,1000000'],
    ];

    }
    public function render()
    {
        return view('livewire.location.location-component');
    }
}
