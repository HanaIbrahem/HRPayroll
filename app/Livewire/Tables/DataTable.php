<?php
declare(strict_types=1);

namespace App\Livewire\Tables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Livewire\Component;
use Livewire\WithPagination;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

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
            $type = $c['filter'] ?? $this->defaultFilterForType($c['type'] ?? 'text');
            if ($type !== 'none') {
                $this->filters[$this->filterBindingKey($c)] = '';
            }
        }
    }

    public function updated($name): void
    {
        if (
            $name === 'q' || $name === 'perPage' ||
            str_starts_with($name, 'filters.') ||
            $name === 'sortField' || $name === 'sortDirection'
        ) {
            $this->resetPage();
        }
    }

    public function updatedPerPage($value): void
    {
        $v = (int) $value;
        if (!in_array($v, $this->perPageOptions, true)) $v = 10;
        $this->perPage = $v;
    }

    public function clearFilters(): void
    {
        $this->q = '';
        foreach ($this->columns() as $c) {
            $type = $c['filter'] ?? $this->defaultFilterForType($c['type'] ?? 'text');
            if ($type !== 'none') {
                $this->filters[$this->filterBindingKey($c)] = '';
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

    // ---------------- helpers ----------------

    /** For "relation.path.column" return [relationPath, column]; null if simple field. */
    protected function parseRelationField(string $field): ?array
    {
        if (strpos($field, '.') === false) return null;
        $parts  = explode('.', $field);
        $column = array_pop($parts);
        $relation = implode('.', $parts);
        return [$relation, $column];
    }

    /** Build a Livewire-safe filter binding key (no dots) or use provided 'filter_key'. */
    protected function filterBindingKey(array $c): string
    {
        return $c['filter_key'] ?? str_replace('.', '__', $c['field']);
    }

    /** Gather relations that should be eager-loaded based on columns + filter_on + search_on. */
    protected function relationsFromColumns(array $cols): array
    {
        $with = [];
        foreach ($cols as $c) {
            $candidates = array_filter([
                $c['field'] ?? null,
                ...((array)($c['filter_on'] ?? [])),
                ...((array)($c['search_on'] ?? [])),
            ]);

            foreach ($candidates as $cand) {
                if ($rc = $this->parseRelationField($cand)) {
                    $with[] = $rc[0];
                }
            }
        }
        return array_values(array_unique($with));
    }

    /** Identify boolean "status" field (first marked 'status', else first boolean). */
    protected function statusField(): ?string
    {
        foreach ($this->columns() as $c) {
            if (($c['type'] ?? 'text') === 'boolean' && ($c['status'] ?? false)) return $c['field'];
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

    /** Try to order by a single-hop belongsTo relation via subquery. */
    protected function tryOrderByBelongsTo(Builder $query, string $relationPath, string $column, string $dir): bool
    {
        $modelClass = $this->modelClass();
        $model = new $modelClass;

        if (str_contains($relationPath, '.')) return false; // one hop only here
        if (!method_exists($model, $relationPath)) return false;

        $rel = $model->{$relationPath}();
        if (!$rel instanceof BelongsTo) return false;

        $related = $rel->getRelated();
        $relatedTable = $related->getTable();
        $target = "{$relatedTable}.{$column}";

        $sub = $related->newQuery()
            ->select($target)
            ->whereColumn($rel->getQualifiedOwnerKeyName(), $rel->getQualifiedForeignKeyName())
            ->limit(1);

        $query->orderBy($sub, $dir);
        return true;
    }

    protected function buildQuery(): Builder
    {
        $model = $this->modelClass();
        /** @var Builder $query */
        $query = $model::query();
        $cols  = $this->columns();

        // 1) Eager-load relations referenced in field/filter_on/search_on
        $with = $this->relationsFromColumns($cols);
        if (!empty($with)) $query->with($with);

        // 2) Global search (supports relational via 'search_on' or falls back to field)
        if ($this->q !== '') {
            $needle = trim($this->q);
            $lower  = mb_strtolower($needle);
            $searchables = array_values(array_filter($cols, fn($c) => $c['searchable'] ?? true));

            $query->where(function (Builder $sub) use ($searchables, $needle, $lower) {
                foreach ($searchables as $c) {
                    $targets = (array)($c['search_on'] ?? [$c['field']]);
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

        // 3) Column filters (supports relation targets + multi-target via filter_on)
        foreach ($cols as $c) {
            $type    = $c['type'] ?? 'text';
            $filterT = $c['filter'] ?? $this->defaultFilterForType($type);

            $bindKey = $this->filterBindingKey($c);
            if (!array_key_exists($bindKey, $this->filters)) continue;

            $val = $this->filters[$bindKey];
            if ($val === '' || $val === null) continue;

            $filterOn = (array)($c['filter_on'] ?? $c['field']);

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
                        $query->whereHas($relation, fn (Builder $q) => $q->where($column, $val));
                    } else {
                        $query->where($target, $val);
                    }
                }
            } elseif ($filterT === 'boolean') {
                $bool = (bool) intval($val);
                foreach ($filterOn as $target) {
                    if ($rc = $this->parseRelationField($target)) {
                        [$relation, $column] = $rc;
                        $query->whereHas($relation, fn (Builder $q) => $q->where($column, $bool));
                    } else {
                        $query->where($target, $bool);
                    }
                }
            } elseif ($filterT === 'date_range') {
                $field = $c['field']; // only local fields supported for date-range
                $from = Arr::get($val, 'from');
                $to   = Arr::get($val, 'to');
                if ($from) $query->whereDate($field, '>=', $from);
                if ($to)   $query->whereDate($field, '<=', $to);
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

    protected function defaultFilterForType(string $type): string
    {
        return match ($type) {
            'boolean' => 'boolean',
            'date'    => 'none',
            default   => 'text',
        };
    }
     public function export(string $format = 'xlsx')
    {
        $format = strtolower($format);
        $filename = strtolower(class_basename($this->modelClass())) . '-' . now()->format('Ymd_His');

        if ($format === 'xlsx') {
            $columns = $this->columns();

            return response()->streamDownload(function () use ($columns) {
                $writer = WriterEntityFactory::createXLSXWriter();
                // IMPORTANT: we’re controlling headers with streamDownload,
                // so write directly to output buffer:
                $writer->openToFile('php://output');

                // Header row
                $headerCells = array_map(
                    fn ($c) => WriterEntityFactory::createCell($c['label'] ?? ucfirst($c['field'])),
                    $columns
                );
                $writer->addRow(WriterEntityFactory::createRow($headerCells));

                // Data rows (chunk to avoid memory issues)
                $this->buildQuery()->chunk(1000, function ($chunk) use ($columns, $writer) {
                    foreach ($chunk as $row) {
                        $cells = [];
                        foreach ($columns as $c) {
                            $cells[] = WriterEntityFactory::createCell(
                                $this->formatExportValue($row, $c)
                            );
                        }
                        $writer->addRow(WriterEntityFactory::createRow($cells));
                    }
                });

                $writer->close();
            }, "{$filename}.xlsx", [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        if ($format === 'pdf') {
            // NOTE: for huge datasets consider chunked export or CSV instead
            $rows    = $this->buildQuery()->get();
            $columns = $this->columns();

            // Build plain data for the PDF view (formatted)
            $dataRows = [];
            foreach ($rows as $r) {
                $dataRows[] = array_map(
                    fn ($c) => $this->formatExportValue($r, $c),
                    $columns
                );
            }

            $html = view('exports.table-pdf', [
                'title'   => $this->title ?: class_basename($this->modelClass()),
                'columns' => $columns,
                'rows'    => $dataRows,
            ])->render();

            $pdf = app('dompdf.wrapper')
                ->setPaper('a4')
                ->loadHTML($html);

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, "{$filename}.pdf", ['Content-Type' => 'application/pdf']);
        }

        // Unknown format → no-op
        return;
    }

    // ⬇️ NEW: consistent formatting used for both Excel & PDF
    protected function formatExportValue($row, array $col): string
    {
        $field = $col['field'] ?? '';
        $type  = $col['type']  ?? 'text';
        $val   = data_get($row, $field);

        if ($type === 'boolean') {
            $on  = $col['options'][1] ?? 'Active';
            $off = $col['options'][0] ?? 'Inactive';
            return (string) ((bool)$val ? $on : $off);
        }

        if ($type === 'date' && !empty($col['format']) && $val) {
            try {
                return \Illuminate\Support\Carbon::parse($val)->format($col['format']);
            } catch (\Throwable $e) {
                return (string) $val;
            }
        }

        // Default stringify
        return is_scalar($val) || $val === null ? (string) $val : json_encode($val, JSON_UNESCAPED_UNICODE);
    }
}
