<?php

namespace App\Livewire\Employee;

use Livewire\Component;
use App\Models\Employee;
use App\Models\Department;
use App\Models\User;

class EmployeeEdit extends Component
{
    public Employee $employee;

    // form fields
    public string $first_name = '';
    public string $last_name  = '';
    public ?string $position  = '';
    public ?string $code      = '';

    public ?int $department_id = null; // FK
    public ?int $user_id       = null; // manager/user FK

    // typable select search boxes (visible text)
    public string $deptSearch    = '';
    public string $managerSearch = '';

    public function mount(Employee $employee): void
    {
        $this->employee      = $employee;
        $this->first_name    = (string) $employee->first_name;
        $this->last_name     = (string) $employee->last_name;
        $this->position      = (string) ($employee->position ?? '');
        $this->code          = (string) ($employee->code ?? '');

        $this->department_id = $employee->department_id;
        $this->user_id       = $employee->user_id;

        // prefill visible search text
        $this->deptSearch    = optional($employee->department)->name ?? '';
        $this->managerSearch = optional($employee->user)->name ?? '';
    }

    protected function rules(): array
    {
        return [
            'first_name'    => ['required', 'string', 'min:2', 'max:100'],
            'last_name'     => ['required', 'string', 'min:2', 'max:100'],
            'position'      => ['nullable', 'string', 'max:150'],
            'code'          => ['nullable', 'string', 'max:50', 'unique:employees,code,' . $this->employee->id],
            'department_id' => ['required', 'exists:departments,id'],
            'user_id'       => ['required', 'exists:users,id'],
        ];
    }

    /** Called from the dropdown (atomic set of id + label) */
    public function chooseDepartment(int $id, string $name): void
    {
        $this->department_id = $id;
        $this->deptSearch    = $name;
        // If you use the frontend error component, this is enough.
        // Otherwise you can: $this->resetErrorBag('department_id');
    }

    public function chooseManager(int $id, string $name): void
    {
        $this->user_id       = $id;
        $this->managerSearch = $name;
        // $this->resetErrorBag('user_id');
    }

    public function save(): void
    {
        $data = $this->validate();

        $this->employee->update($data);

        $this->dispatch('toast', type: 'success', message: 'Employee updated.');
        
    }

    public function render()
    {
        // live suggestions
        $deptResults = Department::query()
            ->when($this->deptSearch !== '', fn($q) => $q->where('name', 'like', '%' . $this->deptSearch . '%'))
            ->orderBy('name')->limit(8)->get(['id','name']);

        $managerResults = User::query()
            ->when($this->managerSearch !== '', fn($q) => $q->where('name', 'like', '%' . $this->managerSearch . '%'))
            ->orderBy('name')->limit(8)->get(['id','name']);

        return view('livewire.employee.employee-edit', compact('deptResults', 'managerResults'));
    }
}
