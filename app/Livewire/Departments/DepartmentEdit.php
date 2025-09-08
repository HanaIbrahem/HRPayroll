<?php

namespace App\Livewire\Departments;

use Livewire\Component;
use App\Models\Department;
class DepartmentEdit extends Component
{

    public Department $department;
    public string $name = '';

    public function mount(Department $department): void
    {
        $this->department = $department;
        $this->name = (string) $department->name;
    }

    protected function rules(): array
    {
        return ['name' => ['required','string','min:4','max:50']];
    }

    public function save(): void
    {
        $this->validate();

        $this->department->update([
            'name' => $this->name,
        ]);

        // toast (v3 SPA) – ignored safely in v2
        $this->dispatch('toast', type: 'success', message: 'Department updated.');

       
    }


    public function render()
    {
        return view('livewire.departments.department-edit');
    }
}
