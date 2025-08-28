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

            // Department (belongsTo)
            [
                'field'      => 'department.name',
                'label'      => 'Department',
                'type'       => 'text',
                'searchable' => true,                   // global search
                'search_on'  => ['department.name'],    // explicit target
                'sortable'   => true,                   // sort via subquery
                'filter'     => 'text',
                'filter_key' => 'department',           // Livewire-safe key
                'filter_on'  => ['department.name'],    // column filter target(s)
            ],

            // Manager (belongsTo user) — display first name; search/filter across first+last
            [
                'field'      => 'user.first_name',
                'label'      => 'Manager',
                'type'       => 'text',
                'searchable' => true,
                'search_on'  => ['user.first_name','user.last_name'],
                'sortable'   => true,                   // sort by first_name
                'filter'     => 'text',
                'filter_key' => 'manager',              // Livewire-safe key
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
