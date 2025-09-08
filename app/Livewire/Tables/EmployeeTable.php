<?php
declare(strict_types=1);

namespace App\Livewire\Tables;

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
                'field'      => 'first_name',
                'label'      => 'First Name',
                'type'       => 'text',
                'searchable' => true,
                'sortable'   => true,
                'filter'     => 'text',
            ],
            [
                'field'      => 'last_name',
                'label'      => 'Last Name',
                'type'       => 'text',
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
            ],
            [
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
                'searchable' => true,                   
                'search_on'  => ['department.name'],    
                'sortable'   => true,                   
                'filter'     => 'text',
                'filter_key' => 'department',           
                'filter_on'  => ['department.name'],    
            ],

               [
                'field'      => 'location.name',
                'label'      => 'Location',
                'type'       => 'text',
                'searchable' => true,                  
                'search_on'  => ['location.name'],    
                'sortable'   => true,                   
                'filter'     => 'text',
                'filter_key' => 'location',          
                'filter_on'  => ['location.name'],   
            ],
            [
                'field'      => 'user.first_name',
                'label'      => 'Manager',
                'type'       => 'text',
                'searchable' => true,
                'search_on'  => ['user.first_name','user.last_name'],
                'sortable'   => true,                   
                'filter'     => 'text',
                'filter_key' => 'manager',              
                'filter_on'  => ['user.first_name','user.last_name'],
            ],

            [
                'field'      => 'is_active',
                'label'      => 'Status',
                'type'       => 'boolean',
                'searchable' => true,
                'sortable'   => true,
                'filter'     => 'boolean',
                'options'    => [1 => 'Active', 0 => 'Inactive'],
                'hide_sm'    => true,
                'status'     => true,
            ],
            [
                'field'      => 'created_at',
                'label'      => 'Created',
                'type'       => 'date',
                'searchable' => false,
                'sortable'   => true,
                'filter'     => 'none',
                'format'     => 'Y-m-d',
            ],
        ];
    }

    public function editUrl(int $id): ?string
    {
        return route('employee.edit', $id);
    }
}
