<?php

namespace App\Livewire\Employee;

use Livewire\Component;
use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use Illuminate\Validation\Rule;

class EmployeeComponent extends Component
{
    // Text fields
    public string $employeefname     = '';
    public string $employeelname     = '';
    public string $employeeposition  = '';
    public string $code              = '';

    // Auto-selected from manager
    public ?int $department_id = null;

    // Typable select for manager
    public string $managerSearch = '';
    public ?int   $manager_id    = null;

    protected function rules(): array
    {
        return [
            'employeefname'    => ['required', 'string', 'min:2', 'max:100'],
            'employeelname'    => ['required', 'string', 'min:2', 'max:100'],
            'employeeposition' => ['required', 'string', 'max:150'],
            'code'             => ['required', 'string', 'max:50', 'unique:employees,code'],
            'manager_id'       => [
                'required',
                // User is active AND has an active department
                Rule::exists('users', 'id')->where(function ($q) {
                    $q->where('is_active', true)
                      ->whereNotNull('department_id')
                      ->whereExists(function ($s) {
                          $s->selectRaw('1')
                            ->from('departments')
                            ->whereColumn('departments.id', 'users.department_id')
                            ->where('departments.is_active', true);
                      });
                }),
            ],
        ];
    }

    /** Clear selection if the typed text no longer matches chosen manager’s name */
    public function updatedManagerSearch($value): void
    {
        if ($this->manager_id) {
            $u = User::find($this->manager_id);
            $current = $u ? trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) : null;
            if ($current !== $value) {
                $this->manager_id = null;
                $this->department_id = null;
                $this->resetErrorBag('manager_id');
            }
        }
    }

    /** Called by suggestion list */
    public function chooseManager(int $id, string $name): void
    {
        $this->manager_id    = $id;
        $this->managerSearch = $name;

        $dept = Department::select('id','is_active')
            ->find(User::whereKey($id)->value('department_id'));

        $this->department_id = $dept?->id;

        if (!$dept || !$dept->is_active) {
            $this->addError('manager_id', 'Selected manager’s department is inactive (or not set).');
        } else {
            $this->resetErrorBag('manager_id');
        }
    }

    public function save(): void
    {
        $this->validate();

        // Re-derive & re-check at save time to avoid stale state
        $dept = Department::select('id','is_active')
            ->find(User::whereKey($this->manager_id)->value('department_id'));

        if (!$dept || !$dept->is_active) {
            $this->addError('manager_id', 'Selected manager’s department is inactive (or not set).');
            return;
        }

        Employee::create([
            'first_name'    => $this->employeefname,
            'last_name'     => $this->employeelname,
            'position'      => $this->employeeposition,
            'department_id' => $dept->id,
            'user_id'       => $this->manager_id, // manager
            'code'          => $this->code,
        ]);

        // reset form + errors
        $this->reset([
            'employeefname','employeelname','employeeposition','code',
            'managerSearch','manager_id','department_id'
        ]);
        $this->resetValidation();

        // toast + refresh any table listening
        $this->dispatch('toast', type: 'success', message: 'Employee created.');
        $this->dispatch('employees:updated');
    }

    public function render()
    {
        $managerResults = User::query()
            ->where('is_active', true)
            ->whereNotNull('department_id')
            // Only show managers whose department is active
            ->whereExists(function ($s) {
                $s->selectRaw('1')
                  ->from('departments')
                  ->whereColumn('departments.id', 'users.department_id')
                  ->where('departments.is_active', true);
            })
            ->when($this->managerSearch !== '', function ($q) {
                $s = mb_strtolower(trim($this->managerSearch));
                $q->where(function ($qq) use ($s) {
                    $qq->whereRaw('LOWER(first_name) LIKE ?', ["%{$s}%"])
                       ->orWhereRaw('LOWER(last_name) LIKE ?',  ["%{$s}%"])
                       ->orWhereRaw("LOWER(CONCAT(COALESCE(first_name,''),' ',COALESCE(last_name,''))) LIKE ?", ["%{$s}%"]);
                });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(8)
            ->get(['id','first_name','last_name']);

        return view('livewire.employee.employee-component', compact('managerResults'));
    }
}
