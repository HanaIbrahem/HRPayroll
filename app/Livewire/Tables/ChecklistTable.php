<?php

namespace App\Livewire\Tables;

use App\Livewire\Tables\ChecklistTableBase;
use App\Models\Checklist;
use Illuminate\Database\Eloquent\Builder;
class ChecklistTable extends ChecklistTableBase
{
    

    public string $title = 'Checklists';

    protected function modelClass(): string
    {
        return Checklist::class;
    }

    protected function baseQuery(): Builder
    {
        return Checklist::query()
            ->where('user_id', auth()->id())
            ->with(['employee']); // if you show employee fields
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
            'sort_on'    => 'employee.first_name',   // sort by first_name
            'hide_sm'    => false,
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
