<?php

namespace App\Livewire\Tables;

class DepartmentTable extends DataTable
{
    public string $title = 'Departments';

    protected function modelClass(): string
    {
        return \App\Models\Department::class;
    }

    protected function columns(): array
    {
        return [
          
            [
                'field' => 'name',
                'label' => 'Name',
                'type'  => 'text',
                'searchable' => true,
                'sortable'   => true,
                'filter'     => 'text',
            ],
            [
                'field' => 'is_active',
                'label' => 'Status',
                'type'  => 'boolean',
                'searchable' => true,
                'sortable'   => true,
                'filter'     => 'boolean',
                'options'    => [1 => 'Active', 0 => 'Inactive'],
                'status'     => true,     // <- mark as the status field (replaces 'toggleable')
            ],
            [
                'field'  => 'created_at',
                'label'  => 'Created',
                'type'   => 'date',
                'searchable' => false,
                'sortable'   => true,
                'filter'     => 'none',   // <- no date picker
                'format'     => 'Y-m-d',
            ],
        ];
    }
    public function editUrl(int $id): ?string
    {
        // Adjust to your route name / params
        return route('department.edit', $id);
    }
}
