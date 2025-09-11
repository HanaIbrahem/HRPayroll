<?php

namespace App\Livewire\Tables;

use App\Models\Checklist;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;
class EmployeeChecklits extends DataTable
{
    public string $title = 'Employee Checklists ';

   public array $dateFields = [
        'created_at' => 'Uploaded at',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'approved_at' => 'Approved at',

    ];
    public string $dateField = 'created_at';

    /** UI: from/to (YYYY-MM-DD) */
    public ?string $dateFrom = null;
    public ?string $dateTo   = null;
    public ?string $dateApproved   = null;

    
    protected $queryString = [
        'q'          => ['except' => ''],
        'sortField'  => ['except' => 'id'],
        'sortDirection' => ['except' => 'asc'],
        'perPage'    => ['except' => 10],
        'dateField'  => ['except' => 'created_at'],
        'dateFrom'   => ['except' => null],
        'dateTo'     => ['except' => null],
        'dateApproved'     => ['except' => null],

    ];

    public function mount(): void
    {
        // Defaults: current month
        $this->dateFrom ??= Carbon::now()->startOfMonth()->toDateString();
        $this->dateTo   ??= Carbon::now()->toDateString();
        $this->dateApproved   ??= Carbon::now()->toDateString();

    }
    public function updatedDateField(): void   { $this->resetPage(); }
    public function updatedDateFrom(): void    { $this->resetPage(); }
    public function updatedDateTo(): void      { $this->resetPage(); }
    public function updatedDateApproved(): void   { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->dateField = 'created_at';
        $this->dateFrom  = Carbon::now()->startOfMonth()->toDateString();
        $this->dateTo    = Carbon::now()->toDateString();
        $this->dateApproved    = Carbon::now()->toDateString();

        $this->q = '';
        foreach ($this->columns() as $c) {
            $type = $c['filter'] ?? $this->defaultFilterForType($c['type'] ?? 'text');
            if ($type !== 'none') {
                $this->filters[$this->filterBindingKey($c)] = '';
            }
        }
        $this->resetPage();
    }

    protected function modelClass(): string
    {
        return Checklist::class;
    }

    protected function baseQuery(): Builder
    {
        $q = Checklist::query()->where('status','approved');
        
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
                'filter'     => 'text',
            ],
            [
                'field'      => 'employee.code',
                'label'      => 'Employee code',
                'search_on'  => ['employee.code'],
                'filter_on'  => ['employee.code'],
                'sortable'   => false,
                'hide_sm'    => false,
                'filter'     => 'text',
            ],
            [
                'field'      => 'user.department.name',
                'label'      => 'Department',
                'search_on'  => ['user.department.name'],
                'filter_on'  => ['user.department.name'],
                'sortable'   => false,
                'hide_sm'    => false,
                'filter'     => 'text',
            ],
            [
                'field'      => 'employee.location.name',
                'label'      => 'Location',
                'search_on'  => ['employee.location.name'],
                'filter_on'  => ['employee.location.name'],
                'sortable'   => false,
                'hide_sm'    => true,
                'filter'     => 'text',
            ],
            [
                'field'      => 'user.fullname',
                'label'      => 'Manager Name',
                'search_on'  => ['user.first_name', 'user.last_name'],
                'filter_on'  => ['user.first_name', 'user.last_name'],
                'sortable'   => false,
                'hide_sm'    => true,
                'filter'     => 'text',
            ],

          [
                'field'      => 'calculated_cost',
                'label'      => 'Totsl Cost',
                'search_on'  => 'calculated_cost',
                'filter_on'  => 'calculated_cost',
                'sortable'   => true,
                'hide_sm'    => true,
                'filter'     => 'text',
            ],
            [
                'field'      => 'approver.fullname',
                'label'      => 'Approved By',
                'search_on'  => ['approver.first_name', 'approver.last_name'],
                'filter_on'  => ['approver.first_name', 'approver.last_name'],
                'sortable'   => true, 
                'hide_sm'    => true,
                'filter'     => 'text',
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
        // Start from child-provided scope
        $query = $this->baseQuery();

        $cols = $this->columns();

        // 1) Eager-load relations referenced in field/filter_on/search_on
        $with = $this->relationsFromColumns($cols);
        if (!empty($with))
            $query->with($with);

        // 2) Global search
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
                                if (in_array($lower, ['1', 'true', 'yes', 'active', 'enabled', 'on', 'فعال'], true)) {
                                    $sub->orWhere($target, 1);
                                }
                                if (in_array($lower, ['0', 'false', 'no', 'inactive', 'disabled', 'off', 'غير فعال'], true)) {
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

        // 3) Column filters
        foreach ($cols as $c) {
            $type = $c['type'] ?? 'text';
            $filterT = $c['filter'] ?? $this->defaultFilterForType($type);

            $bindKey = $this->filterBindingKey($c);
            if (!array_key_exists($bindKey, $this->filters))
                continue;

            $val = $this->filters[$bindKey];
            if ($val === '' || $val === null)
                continue;

            $filterOn = (array) ($c['filter_on'] ?? $c['field']);

            if ($filterT === 'text') {
                $query->where(function (Builder $w) use ($filterOn, $val) {
                    foreach ($filterOn as $target) {
                        if ($rc = $this->parseRelationField($target)) {
                            [$relation, $column] = $rc;
                            $w->orWhereHas($relation, function (Builder $q) use ($column, $val) {
                                $q->where($column, 'like', "%{$val}%");
                            });
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
                $to = \Illuminate\Support\Arr::get($val, 'to');
                if ($from)
                    $query->whereDate($field, '>=', $from);
                if ($to)
                    $query->whereDate($field, '<=', $to);
            }
        }

        // 4) Sorting (supports single-hop belongsTo relation)
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

        return view('livewire.tables.employee-checklits', [
            'columns' => $this->columns(),
            'rows' => $rows,
            'title' => $this->title ?: class_basename($this->modelClass()),
        ]);
    }
}
