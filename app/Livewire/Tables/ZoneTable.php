<?php

namespace App\Livewire\Tables;

use Livewire\Component;

class ZoneTable extends DataTable
{

    public string $title = 'Zones';

    protected function modelClass(): string
    {
        return \App\Models\Zone::class;
    }

    protected function columns(): array
    {
        return [
            ['field' => 'from_zone', 'label' => 'From', 'type' => 'text', 'searchable' => true, 'sortable' => true, 'filter' => 'text'],
            ['field' => 'to_zone', 'label' => 'To', 'type' => 'text', 'searchable' => true, 'sortable' => true, 'filter' => 'text'],
            ['field' => 'code', 'label' => 'Code', 'type' => 'text', 'searchable' => true, 'sortable' => true, 'filter' => 'text'],
            ['field' => 'km', 'label' => 'KM', 'type' => 'number', 'searchable' => true, 'sortable' => true, 'filter' => 'text'],
            ['field' => 'fixed_rate', 'label' => 'Fixed (IQD)', 'type' => 'number', 'searchable' => true, 'sortable' => true, 'filter' => 'text'],
            ['field' => 'between_zone', 'label' => 'Between (IQD)', 'type' => 'number', 'searchable' => true, 'sortable' => true, 'filter' => 'text'],
            ['field' => 'description', 'label' => 'Description', 'type' => 'text', 'searchable' => true, 'sortable' => false, 'filter' => 'text','width'=> 'min-w-[20rem]'],
            ['field' => 'is_active', 'label' => 'Status', 'type' => 'boolean', 'searchable' => true, 'sortable' => false, 'filter' => 'boolean','options'    => [1 => 'Active', 0 => 'Inactive'],'stutus'=>true],          
            ['field' => 'created_at', 'label' => 'Created', 'type' => 'date', 'searchable' => false, 'sortable' => true, 'filter' => 'none', 'format' => 'Y-m-d'],
        ];
    }

    public function editUrl(int $id): ?string
    {
        return route('zone.edit', $id);
    }
}
