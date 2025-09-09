<?php

namespace App\Livewire\Tables;

use App\Models\Checklist;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;

class PendingTable extends DataTable
{
     

     
    public string $title = 'Pending ';

   public array $dateFields = [
        'created_at' => 'Created at',
        'updated_at' => 'Updated at',
    ];
    public string $dateField = 'created_at';

    /** UI: from/to (YYYY-MM-DD) */
    public ?string $dateFrom = null;
    public ?string $dateTo   = null;
    protected $queryString = [
        'q'          => ['except' => ''],
        'sortField'  => ['except' => 'id'],
        'sortDirection' => ['except' => 'asc'],
        'perPage'    => ['except' => 10],
     
    ];

   

    public function updatedDateField(): void   { $this->resetPage(); }
    public function updatedDateFrom(): void    { $this->resetPage(); }
    public function updatedDateTo(): void      { $this->resetPage(); }

  
 

    protected function modelClass(): string
    {
        return Checklist::class;
    }

    protected function baseQuery(): Builder
    {
        $q = Checklist::query()->where('status','pending');
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
                'sort_on'    => 'employee.first_name',
                'hide_sm'    => false,
                'filter'      => 'text',

            ],
             [
                'field'      => 'employee.code',
                'label'      => 'Employee code',
                'search_on'  => ['employee.code'],
                'filter_on'  => ['employee.code'],
                'sortable'   => false,
                'sort_on'    => 'employee.code',
                'hide_sm'    => false,
                'filter'      => 'text',

            ],
              [
                'field'      => 'user.department.name',
                'label'      => 'Department',
                'search_on'  => ['user.department.name'],
                'filter_on'  => ['user.department.name'],
                'sortable'   => false,
                'sort_on'    => 'user.department.name',
                'hide_sm'    => false,
                'filter'      => 'text',

            ],
             [
                'field'      => 'employee.location.name',
                'label'      => 'Location',
                'search_on'  => ['employee.location.name'],
                'filter_on'  => ['employee.location.name'],
                'sortable'   => false,
                'sort_on'    => 'employee.location.name',
                'hide_sm'    => true,
                'filter'      => 'text',

            ],
              [
                'field'      => 'user.fullname',
                'label'      => 'Manager Name',
                'search_on'  => ['user.first_name', 'user.last_name'],
                'filter_on'  => ['user.first_name', 'user.last_name'],
                'sortable'   => false,
                'sort_on'    => 'user.first_name',
                'hide_sm'    => true,
                'filter'      => 'text',

            ],
            [
                'field'       => 'status',
                'label'       => 'Status',
                'type'        => 'text',
                'hide_sm'     => true,
            ],
            [
                'field'   => 'created_at',
                'label'   => 'Uploaded',
                'type'    => 'date',
                'format'  => 'Y-m-d',
                'sortable'=> true,
                'hide_sm' => true,
            ],
        ];
    }

    protected function canApproveRow($row): bool   { return $row->status === 'pending'; }
    protected function canRejectRow($row): bool  { return $row->status === 'pending'; }

    
    protected function canEditRow($row): bool
    {
        return false;
    }
    protected function canDeleteRow($row): bool
    {
        return false;
    }
  
    protected function canCloseRow($row): bool
    {
        return false;
    }



   

    public function approve(int $id): void
    {
        $row = $this->baseQuery()->findOrFail($id);

        if (!$this->canApproveRow($row)) {
             $this->dispatch('toast', type: 'error', message: 'You cannot approve this checklist.');
            return;
        }

        if ($row->status !== 'pending') {
            $this->dispatch('toast', type: 'error', message: 'Only PENDING checklists can be approved.');
            return;
        }

        $row->status = 'approved';
        $row->save();
        $this->dispatch('toast', type: 'success', message: 'Checklist APPROVED.');
    }

    public function reject(int $id): void
    {

 
        $row = $this->baseQuery()->findOrFail($id);

        if (!$this->canRejectRow($row)) {
            $this->dispatch('toast', type: 'error', message: 'You cannot reject this checklist.');
            return;
        }

        if (!in_array($row->status, ['open', 'pending', 'approved', 'rejected'], true)) {
            $this->dispatch('toast', type: 'error', message:  'Invalid state.');
            return;
        }

        $row->status = 'rejected';
        $row->save();
        $this->dispatch('toast', type: 'success', message: 'Checklist REJECTED.');
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

        return view('livewire.tables.pending-table', [
            'columns' => $this->columns(),
            'rows' => $rows,
            'title' => $this->title ?: class_basename($this->modelClass()),
        ]);
    }

}
