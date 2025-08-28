<?php

namespace App\Livewire\Checklist;

use Livewire\Component;
use Livewire\WithFileUploads;


class ChecklistComponent extends Component
{
    use WithFileUploads;

    
    public function render()
    {
      
        return view('livewire.checklist.checklist-component');
    }
}
