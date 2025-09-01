<div>

    @switch($role)
        @case('admin')
            <livewire:dashboard.admin-dashboard>
            @break
    
        @case('hr')
            <livewire:dashboard.hr-dashboard> 
            @break

        @default
            <livewire:dashboard.manager-dashboard>
            
    @endswitch

</div>
