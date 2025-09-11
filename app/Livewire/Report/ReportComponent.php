<?php

namespace App\Livewire\Report;

use App\Models\Department;
use App\Models\Location;
use App\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;

class ReportComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';
    protected string $pageName        = 'empPage'; // keep page param separate/explicit

    /** Date UI (already there for next steps) */
    public array $dateFields = [
        'created_at' => 'Created at',
        'start_date' => 'From Date',
        'end_date'   => 'To Date',
    ];
    public string $dateField = 'created_at';
    public ?string $dateFrom = null;
    public ?string $dateTo   = null;

    /** Selected IDs */
    public array $departmentIds = [];
    public array $locationIds   = [];

    /** Option lists */
    public array $departmentOptions = [];
    public array $locationOptions   = [];

    /** UI state */
    public string $activeFilter   = 'departments'; // 'departments' | 'locations'
    public bool   $showEmployees  = false;

    /** Table controls */
    public string $employeeSearch = '';
    public int    $perPage        = 10;
    public array  $perPageOptions = [10, 25, 50, 100];

    public function mount(): void
    {
        $this->departmentOptions = Department::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id','name'])
            ->map(fn ($d) => ['id' => (string)$d->id, 'name' => $d->name])
            ->all();

        $this->locationOptions = Location::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id','name'])
            ->map(fn ($l) => ['id' => (string)$l->id, 'name' => $l->name])
            ->all();
    }

    /** Quick selection helpers */
    public function selectAll(string $what): void
    {
        if ($what === 'departments') {
            $this->departmentIds = array_column($this->departmentOptions, 'id');
        } elseif ($what === 'locations') {
            $this->locationIds = array_column($this->locationOptions, 'id');
        }
        $this->resetPage($this->pageName);
    }

    public function clearAll(string $what): void
    {
        if ($what === 'departments') {
            $this->departmentIds = [];
        } elseif ($what === 'locations') {
            $this->locationIds = [];
        }
        $this->resetPage($this->pageName);
    }

    public function invert(string $what): void
    {
        if ($what === 'departments') {
            $all = array_column($this->departmentOptions, 'id');
            $this->departmentIds = array_values(array_diff($all, $this->departmentIds));
        } elseif ($what === 'locations') {
            $all = array_column($this->locationOptions, 'id');
            $this->locationIds = array_values(array_diff($all, $this->locationIds));
        }
        $this->resetPage($this->pageName);
    }

    /** Toggle table */
    public function browseEmployees(): void
    {
        $this->showEmployees = true;
        $this->resetPage($this->pageName);
    }

    /** Reset page on input changes */
    public function updatedDepartmentIds(): void { $this->resetPage($this->pageName); }
    public function updatedLocationIds(): void   { $this->resetPage($this->pageName); }
    public function updatedEmployeeSearch(): void{ $this->resetPage($this->pageName); }
    public function updatedPerPage(): void       { $this->resetPage($this->pageName); }

    /** Base query for employees filtered by current selections */
    protected function employeesQuery()
    {
        $deptIds = array_map('intval', $this->departmentIds);
        $locIds  = array_map('intval', $this->locationIds);

        $q = Employee::query()
            ->with(['department:id,name', 'location:id,name', 'user:id,first_name,last_name'])
            ->where('is_active', true);

        // Filter only if user selected some; empty = "all"
        if (!empty($deptIds)) {
            $q->whereIn('department_id', $deptIds);
        }
        if (!empty($locIds)) {
            $q->whereIn('location_id', $locIds);
        }

        // Search by name / code / manager
        if ($this->employeeSearch !== '') {
            $needle = trim($this->employeeSearch);
            $q->where(function ($w) use ($needle) {
                $w->where('first_name', 'like', "%{$needle}%")
                  ->orWhere('last_name',  'like', "%{$needle}%")
                  ->orWhere('code',       'like', "%{$needle}%")
                  ->orWhereHas('user', function ($uq) use ($needle) {
                      $uq->where('first_name', 'like', "%{$needle}%")
                         ->orWhere('last_name',  'like', "%{$needle}%");
                  });
            });
        }

        // Friendly sort
        $q->orderBy('first_name')->orderBy('last_name');

        return $q;
    }

    public function render()
    {
        $employees = $this->showEmployees
            ? $this->employeesQuery()->paginate($this->perPage, ['*'], $this->pageName)
            : null;

        return view('livewire.report.report-component', [
            'employees' => $employees,
        ]);
    }
}
