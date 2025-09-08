<?php

namespace App\Livewire\Location;

use Livewire\Component;
use App\Models\Location;
class LocationEdit extends Component
{

    public Location $location;
    public $locationname;
    public $iqdperkm;
    public $maxprice;

    public function mount(Location $location)
    {
        $this->location=$location;
        $this->locationname= (string) $location->name;
        $this->iqdperkm= (string) $location->iqd_per_km;
        $this->maxprice= (string) $location->maximum_price;


    }
    public function save()
    {

        $this->validate();
        $this->location->update([
            'name'=>$this->locationname,
            'iqd_per_km'=>$this->iqdperkm,
            'maximum_price'=>$this->maxprice,
        ]);
        $this->resetValidation();

        // toast + refresh any table listening
        $this->dispatch('toast', type: 'success', message: 'Location Updated.');
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
        return view('livewire.location.location-edit');
    }
}
