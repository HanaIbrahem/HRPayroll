<?php

namespace App\Livewire\Employee;

use Livewire\Component;
use App\Models\Employee;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Str;

class EmployeeComponent extends Component
{
    // Text fields
    public $employeefname = '';
    public $employeelname = '';
    public $employeeposition = '';
    public $code = '';

    // Typable selects (display text + selected id)
    public $deptSearch = '';
    public $department_id = null;

    public $managerSearch = '';
    public $manager_id = null;

    protected function rules(): array
    {
        return [
            'employeefname' => ['required', 'string', 'min:2', 'max:100'],
            'employeelname' => ['required', 'string', 'min:2', 'max:100'],
            'employeeposition' => ['required', 'string', 'max:150'],
            'department_id' => ['required', 'exists:departments,id'],
            'manager_id' => ['required', 'exists:users,id'],
            'code' => ['required', 'string', 'max:50', 'unique:employees,code'],
        ];
    }

    // used for clearing the result
    public function updatedDeptSearch($value): void
{
    // If user is typing something that doesn't equal the picked label, clear the id
    if ($this->department_id) {
        $current = optional(\App\Models\Department::find($this->department_id))->name;
        if ($current !== $value) $this->department_id = null;
    }
}

public function updatedManagerSearch($value): void
{
    if ($this->manager_id) {
        $u = User::find($this->manager_id);
        $current = $u ? trim(($u->first_name ?? '').' '.($u->last_name ?? '')) : null;
        if ($current !== $value) {
            $this->manager_id = null;
        }
    }
}



    public function save(): void
    {
        $this->validate();


        Employee::create([
            'first_name' => $this->employeefname,
            'last_name' => $this->employeelname,
            'position' => $this->employeeposition,
            'department_id' => $this->department_id,
            'user_id' => $this->manager_id,
            'code' => $this->code,

        ]);

        // reset form + errors
        $this->reset([
            'employeefname',
            'employeelname',
            'employeeposition',
            'code',
            'deptSearch',
            'department_id',
            'managerSearch',
            'manager_id'
        ]);
        $this->resetValidation();

        // toast + refresh any table listening
        $this->dispatch('toast', type: 'success', message: 'Employee created.');
        $this->dispatch('employees:updated');
    }

    public function chooseDepartment(int $id, string $name): void
    {
        $this->department_id = $id;
        $this->deptSearch = $name;
    }

    public function chooseManager(int $id, string $name): void
    {
        $this->manager_id = $id;
        $this->managerSearch = $name;
    }

    public function render()
{
    $deptResults = Department::query()
        ->when($this->deptSearch !== '', fn($q) =>
            $q->where('name', 'like', '%'.$this->deptSearch.'%'))
        ->orderBy('name')
        ->limit(8)
        ->get(['id','name']);

    // 🔧 FIX: search + order + select using first_name/last_name
    $managerResults = User::query()
        ->when($this->managerSearch !== '', function ($q) {
            $s = $this->managerSearch;
            $q->where(function ($qq) use ($s) {
                $qq->where('first_name', 'like', "%{$s}%")
                   ->orWhere('last_name', 'like', "%{$s}%")
                   ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$s}%"]);
            });
        })
        ->orderBy('first_name')
        ->orderBy('last_name')
        ->limit(8)
        ->get(['id','first_name','last_name']); // <- no `name`

    return view('livewire.employee.employee-component', compact('deptResults','managerResults'));
}
}
