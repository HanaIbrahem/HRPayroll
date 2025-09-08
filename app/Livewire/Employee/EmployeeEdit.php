<?php

namespace App\Livewire\Employee;

use Livewire\Component;
use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use App\Models\Location;
use Illuminate\Validation\Rule;

class EmployeeEdit extends Component
{
    public Employee $employee;

    public string  $first_name    = '';
    public string  $last_name     = '';
    public ?string $position      = '';
    public ?string $code          = '';

    // get it from managers departmant id
    public ?int    $department_id = null; 
    public ?int    $user_id       = null; 
    public string  $managerSearch = '';

    public ?int    $location_id   = null;
    public string  $locationSearch = '';

    // when created component we init form values
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

        $this->location_id    = $employee->location_id;
        $this->locationSearch = optional($employee->location)->name ?? '';
    }

    protected function rules(): array
    {
        return [
            'first_name' => ['required','string','min:3','max:30'],
            'last_name'  => ['required','string','min:3','max:30'],
            'position'   => ['required','string','min:2','max:50'],
            'code'       => ['required','string','max:30', Rule::unique('employees','code')->ignore($this->employee->id)],
            'user_id'    => [
                'required',
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
            'location_id' => [
                'required',
                Rule::exists('locations', 'id')->where('is_active', true),
            ],
        ];
    }

    public function updatedManagerSearch($value): void
    {
        if ($this->user_id) {
            $u = User::find($this->user_id);
            $current = $u ? trim(($u->first_name ?? '').' '.($u->last_name ?? '')) : null;
            if (mb_strtolower((string)$current) !== mb_strtolower(trim((string)$value))) {
                $this->user_id = null;
                $this->department_id = null;
                $this->resetErrorBag('user_id');
            }
        }
    }

    public function updatedLocationSearch($value): void
    {
        if ($this->location_id) {
            $lo = Location::find($this->location_id);
            $current = $lo?->name;
            if (mb_strtolower((string)$current) !== mb_strtolower(trim((string)$value))) {
                $this->location_id = null;
                $this->resetErrorBag('location_id');
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

    public function chooseLocation(int $id, string $name): void
    {
        $this->location_id    = $id;
        $this->locationSearch = $name;
        $this->resetErrorBag('location_id');
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
            'location_id'   => $this->location_id,
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

        $locationResults = Location::query()
            // ->where('is_active', true) // uncomment if you have this column
            ->when($this->locationSearch !== '', function ($q) {
                $s = mb_strtolower(trim($this->locationSearch));
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$s}%"]);
            })
            ->orderBy('name')
            ->limit(8)
            ->get(['id','name']);

        return view('livewire.employee.employee-edit', compact('managerResults','locationResults'));
    }
}
