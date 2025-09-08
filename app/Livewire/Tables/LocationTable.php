<?php

namespace App\Livewire\Tables;

use Livewire\Component;

class LocationTable extends DataTable
{
    public string $title = 'Locations';

    protected function modelClass(): string
    {
        return \App\Models\Location::class;
    }

    protected function columns(): array
    {
        return [
            [
                'field'      => 'name',
                'label'      => 'Name',
                'type'       => 'text',
                'searchable' => true,
                'sortable'   => true,
                'filter'     => 'text',
            ],
            [
                'field'      => 'iqd_per_km',
                'label'      => 'IQD per KM',
                'type'       => 'text',
                'searchable' => true,
                'sortable'   => true,
                'filter'     => 'text',
            ],
            [
                'field'      => 'maximum_price',
                'label'      => 'Maximum Price',
                'type'       => 'text',
                'searchable' => true,
                'sortable'   => true,
                'filter'     => 'text',
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
                'field'      => 'updated_at',
                'label'      => 'Updated',
                'type'       => 'date',
                'searchable' => false,
                'sortable'   => true,
                'filter'     => 'none',
                'format'     => 'Y-m-d',
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
        return route('location.edit', $id);
    }
}
