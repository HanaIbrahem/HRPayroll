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
        'q' => ['except' => ''],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    public int $perPage = 10;

    /** Allowed per-page sizes for the dropdown */
    public array $perPageOptions = [10, 50, 100];

    public string $title = '';
    public string $q = '';
    public string $sortField = 'id';
    public string $sortDirection = 'asc';
    public array $filters = [];

    abstract protected function modelClass(): string;
    abstract protected function columns(): array;

    protected $paginationTheme = 'tailwind';

    public function paginationView() { return 'vendor.livewire.tailwind'; }
    public function paginationSimpleView() { return 'vendor.livewire.simple-tailwind'; }

    public function mount(): void
    {
        foreach ($this->columns() as $c) {
            $key = $c['field'];
            $type = $c['filter'] ?? $this->defaultFilterForType($c['type'] ?? 'text');

            // No date_range anymore; only init filters that are not 'none'
            if ($type !== 'none') {
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
        // no need to resetPage here; updated() will handle it
    }

    public function clearFilters(): void
    {
        $this->q = '';
        foreach ($this->columns() as $c) {
            $key = $c['field'];
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

    /** Base toggle used by Actions column */
    public function toggle(string $field, int $id): void
    {
        $model = $this->modelClass();
        $row = $model::query()->findOrFail($id);
        $row->{$field} = !(bool) $row->{$field};
        $row->save();
        session()->flash('ok', 'Updated.');
    }

    /** ===== Actions helpers ===== */

    /** Identify the boolean "status" field */
    protected function statusField(): ?string
    {
        foreach ($this->columns() as $c) {
            if (($c['type'] ?? 'text') === 'boolean' && ($c['status'] ?? false)) {
                return $c['field'];
            }
        }
        // Fallback: first boolean column
        foreach ($this->columns() as $c) {
            if (($c['type'] ?? 'text') === 'boolean') {
                return $c['field'];
            }
        }
        return null;
    }

    /** Toggle Activate/Deactivate */
    public function toggleStatus(int $id): void
    {
        if ($field = $this->statusField()) {
            $this->toggle($field, $id);
        }
    }

    /** Default edit behavior (emit event). Child can listen or override editUrl(). */
    public function edit(int $id): void
    {
        $this->dispatch('edit', id: $id);
    }

    /** Optional route for Edit link */
    public function editUrl(int $id): ?string
    {
        return null; 
    }

    public function render()
    {
        $rows = $this->buildQuery()->paginate($this->perPage);

        return view('livewire.tables.data-table', [
            'columns' => $this->columns(),
            'rows' => $rows,
            'title' => $this->title ?: class_basename($this->modelClass()),
        ]);
    }

    protected function buildQuery(): Builder
    {
        $model = $this->modelClass();
        $query = $model::query();
        $cols = $this->columns();

        if ($this->q !== '') {
            $needle = trim($this->q);
            $lower = mb_strtolower($needle);
            $searchables = array_values(array_filter($cols, fn($c) => $c['searchable'] ?? true));

            $query->where(function (Builder $sub) use ($searchables, $needle, $lower) {
                foreach ($searchables as $c) {
                    $f = $c['field'];
                    $t = $c['type'] ?? 'text';

                    if ($t === 'text') {
                        $sub->orWhere($f, 'like', '%' . $needle . '%');
                    } elseif ($t === 'number' && is_numeric($needle)) {
                        $sub->orWhere($f, (int) $needle);
                    } elseif ($t === 'boolean') {
                        if (in_array($lower, ['1', 'true', 'yes', 'active', 'enabled', 'on', 'فعال'], true))
                            $sub->orWhere($f, 1);
                        if (in_array($lower, ['0', 'false', 'no', 'inactive', 'disabled', 'off', 'غير فعال'], true))
                            $sub->orWhere($f, 0);
                    } elseif ($t === 'date') {
                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $needle))
                            $sub->orWhereDate($f, $needle);
                    }
                }
            });
        }

        foreach ($cols as $c) {
            $f = $c['field'];
            $t = $c['filter'] ?? $this->defaultFilterForType($c['type'] ?? 'text');
            if (!array_key_exists($f, $this->filters)) continue;

            $val = $this->filters[$f];

            if ($t === 'text' && $val !== '') {
                $query->where($f, 'like', '%' . $val . '%');
            } elseif ($t === 'select' && $val !== '') {
                $query->where($f, $val);
            } elseif ($t === 'boolean' && $val !== '') {
                $query->where($f, (bool) intval($val));
            }
            // date_range removed from UI; keeping branch for future compatibility
            elseif ($t === 'date_range') {
                $from = Arr::get($val, 'from');
                $to = Arr::get($val, 'to');
                if ($from) $query->whereDate($f, '>=', $from);
                if ($to)   $query->whereDate($f, '<=', $to);
            }
        }

        $sortable = array_column(array_filter($cols, fn($c) => $c['sortable'] ?? true), 'field');
        if (in_array($this->sortField, $sortable, true)) {
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
            'date'    => 'none',   // <- disable date pickers
            default   => 'text',
        };
    }
}
