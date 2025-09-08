<?php

namespace App\Livewire\Tables;

use Livewire\Component;
use Illuminate\Database\Eloquent\Builder;

class UserTable extends DataTable
{

    public string $title = 'Users';

    protected function modelClass(): string
    {
        return \App\Models\User::class;
    }

    protected function columns(): array
    {
        return [
            [
                'field' => 'first_name',
                'label' => 'First Name',
                'type' => 'text',
                'searchable' => true,
                'sortable' => true,
                'filter' => 'text',
            ],
            [
                'field' => 'last_name',
                'label' => 'Last Name',
                'type' => 'text',
                'searchable' => true,
                'sortable' => true,
                'filter' => 'text',
            ],
            [
                'field' => 'username',
                'label' => 'Username',
                'type' => 'text',
                'searchable' => true,
                'sortable' => true,
                'filter' => 'text',
            ],


            // Department (belongsTo)
            [
                'field' => 'department.name',
                'label' => 'Department',
                'type' => 'text',
                'searchable' => true,
                'search_on' => ['department.name'],
                'sortable' => true,
                'filter' => 'text',
                'filter_key' => 'department',
                'filter_on' => ['department.name'],
            ],
            [
                'field' => 'role',
                'label' => 'Role',
                'type' => 'enum',
                'searchable' => true,
                'sortable' => true,
                'filter' => 'text',
            ],
            [
                'field' => 'is_active',
                'label' => 'Status',
                'type' => 'boolean',
                'searchable' => true,
                'sortable' => true,
                'filter' => 'boolean',
                'options' => [1 => 'Active', 0 => 'Inactive'],
                'status' => true,
            ],


            [
                'field' => 'updated_at',
                'label' => 'Updated',
                'type' => 'date',
                'searchable' => false,
                'sortable' => true,
                'filter' => 'none',
                'format' => 'Y-m-d',
            ],
            [
                'field' => 'created_at',
                'label' => 'Created',
                'type' => 'date',
                'searchable' => false,
                'sortable' => true,
                'filter' => 'none',
                'format' => 'Y-m-d',
            ],
        ];
    }
    protected function buildQuery(): Builder
    {
        return parent::buildQuery()
            ->whereNot('role', 'admin');
    }


    public function editUrl(int $id): ?string
    {
        return route('user.edit', $id);
    }
}
