<?php

namespace App\Livewire\Tables;

use App\Models\Checklist;
use App\Models\Department;
use App\Models\Location;
use Illuminate\Support\Collection;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory; 
class ReportTable extends DataTable
{
     public string $title = 'Employee Checklists ';

    public array $dateFields = [
        'created_at' => 'Uploaded at',
        'start_date' => 'Start Date',
        'end_date'   => 'End Date',
        'approved_at'=> 'Approved at',
    ];
    public string $dateField = 'created_at';

    public bool $withVisited = false;

    public ?string $dateFrom = null;
    public ?string $dateTo   = null;
    public ?string $dateApproved = null;

    // Modal & filters
    public bool  $isFilterModalOpen = false;
    public array $departmentIds = [];   // filter by EMPLOYEE department
    public array $locationIds   = [];   // filter by EMPLOYEE location
    public array $employeeIds   = [];   // selected employees (subset of filtered list)
    public string $employeePickerSearch = '';

    public array $departmentOptions = [];
    public array $locationOptions   = [];

    protected $queryString = [
        'q'             => ['except' => ''],
        'sortField'     => ['except' => 'id'],
        'sortDirection' => ['except' => 'asc'],
        'perPage'       => ['except' => 10],
        'dateField'     => ['except' => 'created_at'],
        'dateFrom'      => ['except' => null],
        'dateTo'        => ['except' => null],
        'dateApproved'  => ['except' => null],
    ];

    public function mount(): void
    {
        $this->dateFrom     ??= Carbon::now()->startOfMonth()->toDateString();
        $this->dateTo       ??= Carbon::now()->toDateString();
        $this->dateApproved ??= Carbon::now()->toDateString();

        $this->departmentOptions = Department::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id','name'])
            ->map(fn($d) => ['id'=>(string)$d->id, 'name'=>$d->name])
            ->all();

        $this->locationOptions = Location::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id','name'])
            ->map(fn($l) => ['id'=>(string)$l->id, 'name'=>$l->name])
            ->all();
    }

    protected function exportRows(): Collection
    {
        $q = $this->buildQuery(); // keeps your filters & sorting

        // Always include these relations
        $q->with([
            'employee:id,first_name,last_name,code,department_id,location_id',
            'employee.department:id,name',
            'employee.location:id,name',
            'user:id,first_name,last_name',
            'approver:id,first_name,last_name',
        ]);

        if ($this->withVisited) {
            $q->with(['visitedZones.zone:id,code,from_zone,to_zone']);
        } else {
            // Still need totals for summary
            $q->with(['visitedZones:id,checklist_id,zone_count,repeat_count,calculated_cost']);
        }

        return $q->get();
    }

    /** PDF export */
    public function exportPdf()
    {
        $rows = $this->exportRows();

        $view = $this->withVisited
            ? 'exports.report-detailed'   // with visited zones, one page per checklist
            : 'exports.report-summary';   // one row per checklist

        $html = view($view, [
            'rows'        => $rows,
            'withVisited' => $this->withVisited,
        ])->render();

        $pdf = app('dompdf.wrapper')->setPaper('a4')->loadHTML($html);

        $name = 'checklists-'.now()->format('Ymd-His').'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $name, ['Content-Type' => 'application/pdf']);
    }

    /** Excel export */
    public function exportXlsx()
    {
        $rows = $this->exportRows();
        $name = 'checklists-'.now()->format('Ymd-His').'.xlsx';

        return response()->streamDownload(function () use ($rows) {
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToFile('php://output');

            if ($this->withVisited) {
                // Detailed: one sheet per checklist
                foreach ($rows as $c) {
                    $emp  = data_get($c, 'employee.fullname', 'Employee');
                    $code = data_get($c, 'employee.code');
                    $title = trim(substr(($emp ?: 'Employee').($code ? " ({$code})" : ''), 0, 31)) ?: 'Sheet';

                    $writer->addNewSheetAndMakeItCurrent();
                    $writer->getCurrentSheet()->setName($title);

                    // Title
                    $writer->addRow(WriterEntityFactory::createRow([
                        WriterEntityFactory::createCell("Checklist #{$c->id} — {$emp}".($code ? " ({$code})" : "")),
                    ]));
                    $writer->addRow(WriterEntityFactory::createRow([]));

                    // Overview key/values
                    $pairs = [
                        ['Department',  data_get($c,'employee.department.name','—')],
                        ['Location',    data_get($c,'employee.location.name','—')],
                        ['Manager',     data_get($c,'user.fullname','—')],
                        ['Approved By', data_get($c,'approver.fullname','—')],
                        ['Status',      ucfirst($c->status)],
                        ['From',        (string)$c->start_date],
                        ['To',          (string)$c->end_date],
                        ['Uploaded',    optional($c->created_at)->format('Y-m-d H:i')],
                        ['Approved at', optional($c->approved_at)->format('Y-m-d H:i')],
                    ];
                    foreach ($pairs as $row) {
                        $writer->addRow(WriterEntityFactory::createRow([
                            WriterEntityFactory::createCell($row[0]),
                            WriterEntityFactory::createCell((string)$row[1]),
                        ]));
                    }

                    $writer->addRow(WriterEntityFactory::createRow([]));

                    // Zones table
                    $writer->addRow(WriterEntityFactory::createRow([
                        WriterEntityFactory::createCell('#'),
                        WriterEntityFactory::createCell('Code'),
                        WriterEntityFactory::createCell('From'),
                        WriterEntityFactory::createCell('To'),
                        WriterEntityFactory::createCell('Zone Count'),
                        WriterEntityFactory::createCell('Repeat Zone'),
                        WriterEntityFactory::createCell('Cost'),
                    ]));

                    foreach ($c->visitedZones as $i => $vz) {
                        $writer->addRow(WriterEntityFactory::createRow([
                            WriterEntityFactory::createCell($i + 1),
                            WriterEntityFactory::createCell((string) data_get($vz,'zone.code','—')),
                            WriterEntityFactory::createCell((string) data_get($vz,'zone.from_zone','—')),
                            WriterEntityFactory::createCell((string) data_get($vz,'zone.to_zone','—')),
                            WriterEntityFactory::createCell((int) $vz->zone_count),
                            WriterEntityFactory::createCell((int) $vz->repeat_count),
                            WriterEntityFactory::createCell((int) $vz->calculated_cost),
                        ]));
                    }

                    if ($c->visitedZones->isNotEmpty()) {
                        $writer->addRow(WriterEntityFactory::createRow([]));
                        $writer->addRow(WriterEntityFactory::createRow([
                            WriterEntityFactory::createCell('Totals'),
                            WriterEntityFactory::createCell(''),
                            WriterEntityFactory::createCell(''),
                            WriterEntityFactory::createCell(''),
                            WriterEntityFactory::createCell((int) $c->visitedZones->sum('zone_count')),
                            WriterEntityFactory::createCell((int) $c->visitedZones->sum('repeat_count')),
                            WriterEntityFactory::createCell((int) $c->visitedZones->sum('calculated_cost')),
                        ]));
                    }
                }
            } else {
                // Summary: single sheet, one row per checklist
                $writer->addRow(WriterEntityFactory::createRow([
                    WriterEntityFactory::createCell('Checklist #'),
                    WriterEntityFactory::createCell('Employee'),
                    WriterEntityFactory::createCell('Code'),
                    WriterEntityFactory::createCell('Department'),
                    WriterEntityFactory::createCell('Location'),
                    WriterEntityFactory::createCell('Manager'),
                    WriterEntityFactory::createCell('From'),
                    WriterEntityFactory::createCell('To'),
                    WriterEntityFactory::createCell('Zone Count'),
                    WriterEntityFactory::createCell('Repeat Zone'),
                    WriterEntityFactory::createCell('Cost'),
                    WriterEntityFactory::createCell('Status'),
                    WriterEntityFactory::createCell('Uploaded'),
                    WriterEntityFactory::createCell('Approved at'),
                ]));

                foreach ($rows as $c) {
                    $writer->addRow(WriterEntityFactory::createRow([
                        WriterEntityFactory::createCell((int) $c->id),
                        WriterEntityFactory::createCell((string) data_get($c,'employee.fullname','—')),
                        WriterEntityFactory::createCell((string) data_get($c,'employee.code','—')),
                        WriterEntityFactory::createCell((string) data_get($c,'employee.department.name','—')),
                        WriterEntityFactory::createCell((string) data_get($c,'employee.location.name','—')),
                        WriterEntityFactory::createCell((string) data_get($c,'user.fullname','—')),
                        WriterEntityFactory::createCell((string) $c->start_date),
                        WriterEntityFactory::createCell((string) $c->end_date),
                        WriterEntityFactory::createCell((int) $c->visitedZones->sum('zone_count')),
                        WriterEntityFactory::createCell((int) $c->visitedZones->sum('repeat_count')),
                        WriterEntityFactory::createCell((int) $c->visitedZones->sum('calculated_cost')),
                        WriterEntityFactory::createCell((string) ucfirst($c->status)),
                        WriterEntityFactory::createCell(optional($c->created_at)->format('Y-m-d H:i')),
                        WriterEntityFactory::createCell(optional($c->approved_at)->format('Y-m-d H:i')),
                    ]));
                }
            }

            $writer->close();
        }, $name, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /** Filtered Employees list for the modal (max 50) */
    public function getEmployeeOptionsProperty(): array
    {
        $deptIds = array_map('intval', $this->departmentIds);
        $locIds  = array_map('intval', $this->locationIds);

        $q = Employee::query()
         ->where('is_active', true)
            ->with(['department:id,name', 'location:id,name']);

        // IMPORTANT: filter by EMPLOYEE department/location
        if (!empty($deptIds)) {
            $q->whereIn('department_id', $deptIds);
        }
        if (!empty($locIds)) {
            $q->whereIn('location_id', $locIds);
        }

        if ($this->employeePickerSearch !== '') {
            $needle = trim($this->employeePickerSearch);
            $q->where(function ($w) use ($needle) {
                $w->where('first_name', 'like', "%{$needle}%")
                  ->orWhere('last_name',  'like', "%{$needle}%")
                  ->orWhere('code',       'like', "%{$needle}%");
            });
        }

        return $q->orderBy('first_name')
                 ->orderBy('last_name')
                 ->limit(50)
                 ->get(['id','first_name','last_name','code'])
                 ->map(fn($e) => [
                    'id'   => (string)$e->id,
                    'name' => trim(($e->first_name ?? '').' '.($e->last_name ?? '')) ?: "Employee #{$e->id}",
                    'code' => $e->code,
                 ])
                 ->all();
    }

    /** Keep employeeIds consistent when filters change */
    protected function syncEmployeeIdsWithFilters(): void
    {
        $allowed = collect($this->employeeOptions)->pluck('id')->all();
        if (empty($allowed)) {
            $this->employeeIds = [];
            return;
        }
        $this->employeeIds = array_values(array_intersect($this->employeeIds, $allowed));
    }

    // modal controls
    public function openFilter(): void  { $this->isFilterModalOpen = true; }
    public function closeFilter(): void { $this->isFilterModalOpen = false; }

    public function applyFilters(): void
    {
        $this->syncEmployeeIdsWithFilters();
        $this->resetPage();
        $this->isFilterModalOpen = false;
    }

    public function clearAllFilters(): void
    {
        $this->departmentIds = [];
        $this->locationIds   = [];
        $this->employeeIds   = [];
        $this->employeePickerSearch = '';
        $this->resetPage();
        $this->dispatch('toast', type: 'info', message: 'All filters cleared.');
    }

    // react to filter changes
    public function updatedDepartmentIds(): void { $this->syncEmployeeIdsWithFilters(); $this->resetPage(); }
    public function updatedLocationIds(): void   { $this->syncEmployeeIdsWithFilters(); $this->resetPage(); }
    public function updatedEmployeeIds(): void   { $this->resetPage(); }
    public function updatedDateField(): void     { $this->resetPage(); }
    public function updatedDateFrom(): void      { $this->resetPage(); }
    public function updatedDateTo(): void        { $this->resetPage(); }
    public function updatedDateApproved(): void  { $this->resetPage(); }

    protected function modelClass(): string { return Checklist::class; }

    /** Core query with NEW employee-based filters */
    protected function baseQuery(): Builder
    {
        $q = Checklist::query()->where('status', 'approved');

        // date range
        $column = in_array($this->dateField, array_keys($this->dateFields), true)
            ? $this->dateField
            : 'created_at';

        $from = $this->dateFrom ? Carbon::parse($this->dateFrom)->startOfDay() : null;
        $to   = $this->dateTo   ? Carbon::parse($this->dateTo)->endOfDay()     : null;

        if ($from && $to) {
            $q->whereBetween($column, [$from, $to]);
        } elseif ($from) {
            $q->where($column, '>=', $from);
        } elseif ($to) {
            $q->where($column, '<=', $to);
        }

        // By EMPLOYEE department
        if (!empty($this->departmentIds)) {
            $ids = array_map('intval', $this->departmentIds);
            $q->whereHas('employee', fn(Builder $w) => $w->whereIn('department_id', $ids));
        }

        // By EMPLOYEE location
        if (!empty($this->locationIds)) {
            $ids = array_map('intval', $this->locationIds);
            $q->whereHas('employee', fn(Builder $w) => $w->whereIn('location_id', $ids));
        }

        // Specific employees
        if (!empty($this->employeeIds)) {
            $ids = array_map('intval', $this->employeeIds);
            $q->whereIn('employee_id', $ids);
        }

        return $q;
    }

    protected function columns(): array
    {
        return [
            [
                'field'      => 'employee.fullname',
                'label'      => 'Employee Name',
                'search_on'  => ['employee.first_name', 'employee.last_name'],
                'filter_on'  => ['employee.first_name', 'employee.last_name'],
                'sortable'   => false,
                'hide_sm'    => false,
                'filter'     => 'none',
            ],
            [
                'field'      => 'employee.code',
                'label'      => 'Employee code',
                'search_on'  => ['employee.code'],
                'filter_on'  => ['employee.code'],
                'sortable'   => false,
                'hide_sm'    => false,
                'filter'     => 'none',
            ],
            // CHANGED: show EMPLOYEE department to match the filter
            [
                'field'      => 'employee.department.name',
                'label'      => 'Department',
                'search_on'  => ['employee.department.name'],
                'filter_on'  => ['employee.department.name'],
                'sortable'   => false,
                'hide_sm'    => false,
                'filter'     => 'none',
            ],
            [
                'field'      => 'employee.location.name',
                'label'      => 'Location',
                'search_on'  => ['employee.location.name'],
                'filter_on'  => ['employee.location.name'],
                'sortable'   => false,
                'hide_sm'    => true,
                'filter'     => 'none',
            ],
            [
                'field'      => 'user.fullname',
                'label'      => 'Manager Name',
                'search_on'  => ['user.first_name', 'user.last_name'],
                'filter_on'  => ['user.first_name', 'user.last_name'],
                'sortable'   => false,
                'hide_sm'    => true,
                'filter'     => 'none',
            ],
            [
                'field'      => 'calculated_cost',
                'label'      => 'Totsl Cost',
                'search_on'  => 'calculated_cost',
                'filter_on'  => 'calculated_cost',
                'sortable'   => true,
                'hide_sm'    => true,
                'filter'     => 'none',
            ],
            [
                'field'      => 'approver.fullname',
                'label'      => 'Approved By',
                'search_on'  => ['approver.first_name', 'approver.last_name'],
                'filter_on'  => ['approver.first_name', 'approver.last_name'],
                'sortable'   => true,
                'hide_sm'    => true,
                'filter'     => 'none',
            ],
            [
                'field'   => 'start_date',
                'label'   => 'Start Date',
                'type'    => 'date',
                'format'  => 'Y-m-d',
                'sortable'=> true,
                'hide_sm' => true,
            ],
            [
                'field'   => 'end_date',
                'label'   => 'End Date',
                'type'    => 'date',
                'format'  => 'Y-m-d',
                'sortable'=> true,
                'hide_sm' => true,
            ],
            [
                'field'   => 'approved_at',
                'label'   => 'Approved at',
                'type'    => 'date',
                'format'  => 'Y-m-d',
                'sortable'=> true,
                'hide_sm' => true,
            ],
            [
                'field'   => 'created_at',
                'label'   => 'Uploaded',
                'type'    => 'date',
                'format'  => 'Y-m-d',
                'sortable'=> true,
                'hide_sm' => false,
            ],
        ];
    }
    protected function buildQuery(): Builder
    {
        $query = $this->baseQuery();

        $cols = $this->columns();

        // eager-load based on columns
        $with = $this->relationsFromColumns($cols);
        if (!empty($with)) {
            $query->with($with);
        }

        // global search (unchanged)
        if ($this->q !== '') {
            $needle = trim($this->q);
            $lower = mb_strtolower($needle);
            $searchables = array_values(array_filter($cols, fn($c) => $c['searchable'] ?? true));

            $query->where(function (Builder $sub) use ($searchables, $needle, $lower) {
                foreach ($searchables as $c) {
                    $targets = (array) ($c['search_on'] ?? [$c['field']]);
                    foreach ($targets as $target) {
                        if ($rc = $this->parseRelationField($target)) {
                            [$relation, $column] = $rc;
                            $sub->orWhereHas($relation, function (Builder $q) use ($column, $needle) {
                                $q->where($column, 'like', "%{$needle}%");
                            });
                        } else {
                            $type = $c['type'] ?? 'text';
                            if ($type === 'text') {
                                $sub->orWhere($target, 'like', "%{$needle}%");
                            } elseif ($type === 'number' && is_numeric($needle)) {
                                $sub->orWhere($target, (int) $needle);
                            } elseif ($type === 'boolean') {
                                if (in_array($lower, ['1','true','yes','active','enabled','on','فعال'], true)) {
                                    $sub->orWhere($target, 1);
                                }
                                if (in_array($lower, ['0','false','no','inactive','disabled','off','غير فعال'], true)) {
                                    $sub->orWhere($target, 0);
                                }
                            } elseif ($type === 'date') {
                                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $needle)) {
                                    $sub->orWhereDate($target, $needle);
                                }
                            }
                        }
                    }
                }
            });
        }

        // column filters (unchanged)
        foreach ($cols as $c) {
            $type = $c['type'] ?? 'text';
            $filterT = $c['filter'] ?? $this->defaultFilterForType($type);

            $bindKey = $this->filterBindingKey($c);
            if (!array_key_exists($bindKey, $this->filters)) continue;

            $val = $this->filters[$bindKey];
            if ($val === '' || $val === null) continue;

            $filterOn = (array) ($c['filter_on'] ?? $c['field']);

            if ($filterT === 'text') {
                $query->where(function (Builder $w) use ($filterOn, $val) {
                    foreach ($filterOn as $target) {
                        if ($rc = $this->parseRelationField($target)) {
                            [$relation, $column] = $rc;
                            $w->orWhereHas($relation, fn(Builder $q) => $q->where($column, 'like', "%{$val}%"));
                        } else {
                            $w->orWhere($target, 'like', "%{$val}%");
                        }
                    }
                });
            } elseif ($filterT === 'select') {
                foreach ($filterOn as $target) {
                    if ($rc = $this->parseRelationField($target)) {
                        [$relation, $column] = $rc;
                        $query->whereHas($relation, fn(Builder $q) => $q->where($column, $val));
                    } else {
                        $query->where($target, $val);
                    }
                }
            } elseif ($filterT === 'boolean') {
                $bool = (bool) intval($val);
                foreach ($filterOn as $target) {
                    if ($rc = $this->parseRelationField($target)) {
                        [$relation, $column] = $rc;
                        $query->whereHas($relation, fn(Builder $q) => $q->where($column, $bool));
                    } else {
                        $query->where($target, $bool);
                    }
                }
            } elseif ($filterT === 'date_range') {
                $field = $c['field'];
                $from = \Illuminate\Support\Arr::get($val, 'from');
                $to   = \Illuminate\Support\Arr::get($val, 'to');
                if ($from) $query->whereDate($field, '>=', $from);
                if ($to)   $query->whereDate($field, '<=', $to);
            }
        }

        // sorting (unchanged)
        $sortable = array_column(array_filter($cols, fn($c) => $c['sortable'] ?? true), 'field');
        if (in_array($this->sortField, $sortable, true)) {
            if ($rc = $this->parseRelationField($this->sortField)) {
                [$relation, $column] = $rc;
                if (!$this->tryOrderByBelongsTo($query, $relation, $column, $this->sortDirection)) {
                    $query->latest();
                }
            } else {
                $query->orderBy($this->sortField, $this->sortDirection);
            }
        } else {
            $query->latest();
        }

        return $query;
    }

    public function render()
    {
        $rows = $this->buildQuery()->paginate($this->perPage);

        return view('livewire.tables.report-table', [
            'columns' => $this->columns(),
            'rows'    => $rows,
            'title'   => $this->title ?: class_basename($this->modelClass()),
        ]);
    }
}
