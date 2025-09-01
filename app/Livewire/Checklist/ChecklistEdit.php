<?php

namespace App\Livewire\Checklist;

use App\Models\Checklist;
use Livewire\Component;
use Illuminate\Support\Facades\Gate;

class ChecklistEdit extends Component
{
    public Checklist $checklist;

    public function mount(Checklist $checklist)
    {

        $this->checklist=$checklist;
    }
    public function render()
    {
        Gate::authorize('edit-checklist-for-employee', $this->checklist);

        return view('livewire.checklist.checklist-edit');
    }
}
