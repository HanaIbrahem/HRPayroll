<?php

namespace App\Livewire\Tables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Livewire\Component;
use Livewire\WithPagination;

abstract class DataTable extends Component
{
    use WithPagination;

    protected $queryString = [
        'q'             => ['except' => ''],
        'sortField'     => ['except' => 'id'],
        'sortDirection' => ['except' => 'asc'],
        'perPage'       => ['except' => 10],
    ];

    public int $perPage = 10;
    public array $perPageOptions = [10, 50, 100];

    public string $title = '';
    public string $q = '';
    public string $sortField = 'id';
    public string $sortDirection = 'asc';
    public array $filters = [];

    abstract protected function modelClass(): string;
    abstract protected function columns(): array;

    protected $paginationTheme = 'tailwind';

    public function paginationView()       { return 'vendor.livewire.tailwind'; }
    public function paginationSimpleView() { return 'vendor.livewire.simple-tailwind'; }

    public function mount(): void
    {
        foreach ($this->columns() as $c) {
            $key  = $c['field'];
            $type = $c['filter'] ?? $this->defaultFilterForType($c['type'] ?? 'text');
            if ($type !== 'none') {
                // Keep original keys; if you use filter_key in children, bind to that instead
                $this->filters[$key] = '';
            }
        }
    }

    public function updated($name): void
    {
        if (
            $name === 'q' || $name === 'perPage' ||
            strpos($name, 'filters.') === 0 ||
            $name === 'sortField' || $name === 'sortDirection'
        ) {
            $this->resetPage();
        }
    }

    public function updatedPerPage($value): void
    {
        $v = (int) $value;
        if (!in_array($v, $this->perPageOptions, true)) {
            $v = 10;
        }
        $this->perPage = $v;
    }

    public function clearFilters(): void
    {
        $this->q = '';
        foreach ($this->columns() as $c) {
            $key  = $c['field'];
            $type = $c['filter'] ?? $this->defaultFilterForType($c['type'] ?? 'text');
            if ($type !== 'none') {
                $this->filters[$key] = '';
            }
        }
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $cols = collect($this->columns())->keyBy('field');
        if (!$cols->has($field) || !($cols[$field]['sortable'] ?? true)) return;

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    /** Toggle used by Actions column */
    public function toggle(string $field, int $id): void
    {
        $model = $this->modelClass();
        $row = $model::query()->findOrFail($id);
        $row->{$field} = !(bool) $row->{$field};
        $row->save();
        session()->flash('ok', 'Updated.');
    }

    /** ===== Lazy-loading prevention helpers ===== */

    /** Return [relationPath, column] for "relation.path.column" or null if not relational */
    protected function parseRelationField(string $field): ?array
    {
        if (strpos($field, '.') === false) return null;
        $parts  = explode('.', $field);
        $column = array_pop($parts);
        $relation = implode('.', $parts);
        return [$relation, $column];
    }

    /** Collect relations referenced by columns (field + optional filter_on) */
    protected function relationsFromColumns(array $cols): array
    {
        $with = [];
        foreach ($cols as $c) {
            if ($rc = $this->parseRelationField($c['field'])) {
                $with[] = $rc[0];
            }
            if (isset($c['filter_on']) && ($rc = $this->parseRelationField($c['filter_on']))) {
                $with[] = $rc[0];
            }
        }
        return array_values(array_unique($with));
    }

    /** Identify boolean "status" field */
    protected function statusField(): ?string
    {
        foreach ($this->columns() as $c) {
            if (($c['type'] ?? 'text') === 'boolean' && ($c['status'] ?? false)) {
                return $c['field'];
            }
        }
        foreach ($this->columns() as $c) {
            if (($c['type'] ?? 'text') === 'boolean') return $c['field'];
        }
        return null;
    }

    public function toggleStatus(int $id): void
    {
        if ($field = $this->statusField()) $this->toggle($field, $id);
    }

    public function edit(int $id): void
    {
        $this->dispatch('edit', id: $id);
    }

    public function editUrl(int $id): ?string
    {
        return null;
    }

    public function render()
    {
        $rows = $this->buildQuery()->paginate($this->perPage);

        return view('livewire.tables.data-table', [
            'columns' => $this->columns(),
            'rows'    => $rows,
            'title'   => $this->title ?: class_basename($this->modelClass()),
        ]);
    }

    protected function buildQuery(): Builder
    {
        $model = $this->modelClass();
        $query = $model::query();
        $cols  = $this->columns();

        /** 1) Auto-eager-load relations used by columns/filters */
        $with = $this->relationsFromColumns($cols);
        if (!empty($with)) {
            $query->with($with);
        }

        /** 2) Global search (skip relational fields to avoid SQL errors) */
        if ($this->q !== '') {
            $needle = trim($this->q);
            $lower  = mb_strtolower($needle);
            $searchables = array_values(array_filter($cols, fn($c) => $c['searchable'] ?? true));

            $query->where(function (Builder $sub) use ($searchables, $needle, $lower) {
                foreach ($searchables as $c) {
                    $f = $c['field'];
                    $t = $c['type'] ?? 'text';

                    // skip relation fields for global search
                    if ($this->parseRelationField($f)) continue;

                    if ($t === 'text') {
                        $sub->orWhere($f, 'like', '%' . $needle . '%');
                    } elseif ($t === 'number' && is_numeric($needle)) {
                        $sub->orWhere($f, (int) $needle);
                    } elseif ($t === 'boolean') {
                        if (in_array($lower, ['1','true','yes','active','enabled','on','فعال'], true)) {
                            $sub->orWhere($f, 1);
                        }
                        if (in_array($lower, ['0','false','no','inactive','disabled','off','غير فعال'], true)) {
                            $sub->orWhere($f, 0);
                        }
                    } elseif ($t === 'date') {
                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $needle)) {
                            $sub->orWhereDate($f, $needle);
                        }
                    }
                }
            });
        }

        /** 3) Column filters (supports relation filters via whereHas) */
        foreach ($cols as $c) {
            $field   = $c['field'];
            $type    = $c['type'] ?? 'text';
            $filterT = $c['filter'] ?? $this->defaultFilterForType($type);

            if (!array_key_exists($field, $this->filters)) continue;

            $val = $this->filters[$field];

            if ($filterT === 'text' && $val !== '') {
                if ($rc = $this->parseRelationField($field)) {
                    [$relation, $column] = $rc;
                    $query->whereHas($relation, function (Builder $q) use ($column, $val) {
                        $q->where($column, 'like', '%' . $val . '%');
                    });
                } else {
                    $query->where($field, 'like', '%' . $val . '%');
                }
            } elseif ($filterT === 'select' && $val !== '') {
                if ($rc = $this->parseRelationField($field)) {
                    [$relation, $column] = $rc;
                    $query->whereHas($relation, function (Builder $q) use ($column, $val) {
                        $q->where($column, $val);
                    });
                } else {
                    $query->where($field, $val);
                }
            } elseif ($filterT === 'boolean' && $val !== '') {
                if ($rc = $this->parseRelationField($field)) {
                    [$relation, $column] = $rc;
                    $query->whereHas($relation, function (Builder $q) use ($column, $val) {
                        $q->where($column, (bool) intval($val));
                    });
                } else {
                    $query->where($field, (bool) intval($val));
                }
            } elseif ($filterT === 'date_range') {
                $from = Arr::get($val, 'from');
                $to   = Arr::get($val, 'to');
                if ($from) $query->whereDate($field, '>=', $from);
                if ($to)   $query->whereDate($field, '<=', $to);
            }
        }

        /** 4) Sorting (block relation fields) */
        $sortable = array_column(array_filter($cols, fn($c) => $c['sortable'] ?? true), 'field');
        if (in_array($this->sortField, $sortable, true) && !$this->parseRelationField($this->sortField)) {
            $query->orderBy($this->sortField, $this->sortDirection);
        } else {
            $query->latest();
        }

        return $query;
    }

    protected function defaultFilterForType(string $type): string
    {
        return match ($type) {
            'boolean' => 'boolean',
            'date'    => 'none', // no date pickers
            default   => 'text',
        };
    }
}
