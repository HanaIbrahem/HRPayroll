<?php

namespace App\PowerGridThemes;

use PowerComponents\LivewirePowerGrid\Themes\Tailwind;

class DaisyUi extends Tailwind
{
    public string $name = 'tailwind';

    public function table(): array
    {
        return [
            'layout' => [
                // Outer wrappers -> compact card look
                'container' => 'overflow-x-auto',
                'base'      => '',
                'div'       => 'card bg-base-100 border border-base-300/60 shadow-sm',
                'table'     => 'table table-sm table-zebra w-full',
                'actions'   => 'flex items-center gap-2',
            ],

            'header' => [
                // Sticky head + spacing like your datatable
                'thead'    => 'bg-base-100 sticky top-0 z-10',
                'tr'       => '',
                'th'       => 'px-3 py-2 text-left text-xs font-semibold text-base-content/70 whitespace-nowrap',
                'thAction' => '!font-semibold',
            ],

            'body' => [
                'tbody'              => 'text-base-content',
                'tbodyEmpty'         => '',
                'tr'                 => 'hover:bg-base-200/40',
                'td'                 => 'px-3 py-2 whitespace-nowrap',
                'tdEmpty'            => 'p-6 text-center text-base-content/60',
                'tdSummarize'        => 'p-3 text-sm text-base-content/60 text-right space-y-2',
                'trSummarize'        => '',
                'tdFilters'          => 'bg-base-100 py-2',
                'trFilters'          => '',
                'tdActionsContainer' => 'flex gap-2 justify-end',
            ],
        ];
    }

    public function footer(): array
    {
        return [
            'view'   => $this->root().'.footer',
            'select' => 'select select-bordered select-sm w-auto',
        ];
    }

    /** Global search box */
    public function searchBox(): array
    {
        return [
            'input'      => 'input input-bordered input-sm w-full md:w-72 pl-9',
            'iconSearch' => 'w-4 h-4 opacity-70',
            'iconClose'  => 'opacity-70',
        ];
    }

    /** Toggle-columns menu */
    public function cols(): array
    {
        return [
            'div' => 'select-none flex items-center gap-2',
        ];
    }

    /** Filters styling (specific search) */
    public function filterInputText(): array
    {
        return [
            'view'   => $this->root().'.filters.input-text',
            'base'   => 'min-w-[10rem]',
            'input'  => 'input input-bordered input-sm w-full',
            'select' => 'select select-bordered select-sm w-full',
        ];
    }

    public function filterBoolean(): array
    {
        return [
            'view'   => $this->root().'.filters.boolean',
            'base'   => 'min-w-[8rem]',
            'select' => 'select select-bordered select-sm w-full',
        ];
    }

    /** Toggleable cell uses package default view */
    public function toggleable(): array
    {
        return ['view' => $this->root().'.toggleable'];
    }

    /** Title (left) + controls (right) bar */
    public function header(): array
    {
        // Use PG’s built-in header layout but ensure spacing matches your style
        return [
            'div'             => 'px-3 pt-3 flex items-center justify-between gap-3',
            'divActions'      => 'flex items-center gap-2',
            'divClearFilters' => 'px-3 pb-2',
        ];
    }
}
