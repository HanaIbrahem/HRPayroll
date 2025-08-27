<?php

namespace App\Livewire\Tables;

use Livewire\Component;

class EmployeeTable extends DataTable
{
   
    public string $title = 'Employees';

    protected function modelClass(): string
    {
        return \App\Models\Employee::class;
    }

    protected function columns(): array
    {
        return [
          
            [
                'field' => 'first_name',
                'label' => 'First Name',
                'type'  => 'text',
                'searchable' => true,
                'sortable'   => true,
                'filter'     => 'text',
            ],
             [
                'field' => 'last_name',
                'label' => 'Last Name',
                'type'  => 'text',
                'searchable' => true,
                'sortable'   => true,
                'filter'     => 'text',
            ],
            [
            'field'      => 'position',
            'label'      => 'Position',
            'type'       => 'text',
            'searchable' => true,
            'sortable'   => true,
            'filter'     => 'text',
        ],  [
            'field'      => 'code',
            'label'      => 'Code',
            'type'       => 'text',
            'searchable' => true,
            'sortable'   => true,
            'filter'     => 'text',
        ],
        [
            'field'      => 'department.name',
            'label'      => 'Department',
            'type'       => 'text',      
            'searchable' => false,          
            'sortable'   => false,           
            'filter'     => 'text',       
        ],
         [
            'field'      => 'user.first_name',
            'label'      => 'Manager',
            'type'       => 'text',      
            'searchable' => false,          
            'sortable'   => false,           
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
        return route('employee.edit', $id);
    }
}
