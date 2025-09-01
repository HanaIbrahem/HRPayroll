<?php

namespace App\Livewire\Employee;

use Livewire\Component;
use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class EmployeeEdit extends Component
{
    public Employee $employee;

    public string  $first_name    = '';
    public string  $last_name     = '';
    public ?string $position      = '';
    public ?string $code          = '';

    public ?int    $department_id = null; // derived from manager
    public ?int    $user_id       = null; // manager id
    public string  $managerSearch = '';

    public function mount(Employee $employee): void
    {
        $this->employee      = $employee;
        $this->first_name    = (string) $employee->first_name;
        $this->last_name     = (string) $employee->last_name;
        $this->position      = (string) ($employee->position ?? '');
        $this->code          = (string) ($employee->code ?? '');

        $this->user_id       = $employee->user_id;
        $this->department_id = $employee->department_id;

        $mgr = $employee->user;
        $this->managerSearch = $mgr ? trim(($mgr->first_name ?? '').' '.($mgr->last_name ?? '')) : '';
    }

    protected function rules(): array
    {
        return [
            'first_name' => ['required','string','min:2','max:100'],
            'last_name'  => ['required','string','min:2','max:100'],
            'position'   => ['nullable','string','max:150'],
            'code'       => ['nullable','string','max:50', Rule::unique('employees','code')->ignore($this->employee->id)],
            'user_id'    => [
                'required',
                // user is active AND belongs to an active department
                Rule::exists('users','id')->where(function ($q) {
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

    public function updatedManagerSearch($value): void
    {
        if ($this->user_id) {
            $u = User::find($this->user_id);
            $current = $u ? trim(($u->first_name ?? '').' '.($u->last_name ?? '')) : null;
            if ($current !== $value) {
                $this->user_id = null;
                $this->department_id = null;
                $this->resetErrorBag('user_id');
            }
        }
    }

    public function chooseManager(int $id, string $name): void
    {
        $this->user_id       = $id;
        $this->managerSearch = $name;

        $dept = Department::select('id','is_active')
            ->find(User::whereKey($id)->value('department_id'));

        $this->department_id = $dept?->id;

        if (!$dept || !$dept->is_active) {
            $this->addError('user_id', 'Selected manager’s department is inactive (or not set).');
        } else {
            $this->resetErrorBag('user_id');
        }
    }

    public function save(): void
    {
        $this->validate();

        // Re-derive & re-check at save time
        $dept = Department::select('id','is_active')
            ->find(User::whereKey($this->user_id)->value('department_id'));

        if (!$dept || !$dept->is_active) {
            $this->addError('user_id', 'Selected manager’s department is inactive (or not set).');
            return;
        }

        $this->employee->update([
            'first_name'    => $this->first_name,
            'last_name'     => $this->last_name,
            'position'      => $this->position,
            'code'          => $this->code,
            'user_id'       => $this->user_id,
            'department_id' => $dept->id,
        ]);

        $this->department_id = $dept->id;

        $this->dispatch('toast', type: 'success', message: 'Employee updated.');
        $this->dispatch('employees:updated');
    }

    public function render()
    {
        $managerResults = User::query()
            ->where('is_active', true)
            ->where('role','manager')
            ->whereNotNull('department_id')
            // Only managers whose department is active
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

        return view('livewire.employee.employee-edit', compact('managerResults'));
    }
}
