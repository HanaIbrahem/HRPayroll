<?php

namespace App\Livewire\Departments;

use Livewire\Component;
use App\Models\Department;
class DepartmentComponent extends Component
{

    public $departmentname='';
    public function render()
    {
        return view('livewire.departments.department-component');
    }

   
    protected function rules(): array
    {
        return [
            'departmentname' => ['required','string','min:2','max:150'],
        ];
    }

    public function save()
    {
        $this->validate();
        Department::create([
            'name' => $this->departmentname,   
        ]);

        $this->reset();
        $this->resetValidation();
        $this->dispatch('toast', type: 'success', message: 'Department saved.');
    }
}
