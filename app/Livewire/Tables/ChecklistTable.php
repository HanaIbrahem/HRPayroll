<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Models\Checklist;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class ChecklistTable extends ChecklistTableBase
{
    use WithPagination;

    public string $title = 'Checklists';

    /** UI: which timestamp to filter on */
    public array $dateFields = [
        'created_at' => 'Created at',
        'updated_at' => 'Updated at',
    ];
    public string $dateField = 'created_at';

    /** UI: from/to (YYYY-MM-DD) */
    public ?string $dateFrom = null;
    public ?string $dateTo   = null;

    /** Keep filters in the URL */
    protected $queryString = [
        'q'          => ['except' => ''],
        'sortField'  => ['except' => 'id'],
        'sortDirection' => ['except' => 'asc'],
        'perPage'    => ['except' => 10],
        'dateField'  => ['except' => 'created_at'],
        'dateFrom'   => ['except' => null],
        'dateTo'     => ['except' => null],
    ];

    public function mount(): void
    {
        // Defaults: current month
        $this->dateFrom ??= Carbon::now()->startOfMonth()->toDateString();
        $this->dateTo   ??= Carbon::now()->toDateString();
    }

    /** When any of these update, reset to the first page */
    public function updatedDateField(): void   { $this->resetPage(); }
    public function updatedDateFrom(): void    { $this->resetPage(); }
    public function updatedDateTo(): void      { $this->resetPage(); }

  
    public function clearFilters(): void
    {
        $this->dateField = 'created_at';
        $this->dateFrom  = Carbon::now()->startOfMonth()->toDateString();
        $this->dateTo    = Carbon::now()->toDateString();
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
        $q = Checklist::query()
            ->where('user_id', auth()->id())
            ->with(['employee']);

        // Apply from/to on the selected timestamp (inclusive)
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
                'label'      => 'Name',
                'search_on'  => ['employee.first_name', 'employee.last_name'],
                'filter_on'  => ['employee.first_name', 'employee.last_name'],
                'sortable'   => false,
                'sort_on'    => 'employee.first_name',
                'hide_sm'    => false,
                'filter'      => 'text',

            ],
            [
                'field'       => 'status',
                'label'       => 'Status',
                'type'        => 'text',
                'filter'      => 'select',
                'options'     => [
                    'open'     => 'Open',
                    'pending'  => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ],
                'searchable'  => true,
                'sortable'    => true,
                'hide_sm'     => false,
            ],
            [
                'field'   => 'created_at',
                'label'   => 'Created',
                'type'    => 'date',
                'format'  => 'Y-m-d',

                'sortable'=> true,
                'hide_sm' => true,
            ],
        ];
    }

    /** Rules: edit when pending; delete when open or rejected; close when open */
    protected function canEditRow($row): bool   { return $row->status === 'open'; }
    protected function canDeleteRow($row): bool { return in_array($row->status, ['open','rejected'], true); }
    protected function canCloseRow($row): bool  { return $row->status === 'open'; }
}
