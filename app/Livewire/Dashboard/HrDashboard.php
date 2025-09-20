<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Checklist;
use App\Models\Employee;
use App\Models\Zone;
use DB;
class HrDashboard extends Component
{
    public $pendingCount;
    public $approvedCount;
    public $employeeCount;
    public $zoneCount;
    public function mount()
    {
        $this->pendingCount = Checklist::where('status', 'pending')->count();
        $this->approvedCount = Checklist::where('status', 'approved')->count();
        $this->employeeCount = Employee::where('is_active',true)->count();
        $this->zoneCount = Zone::where('is_active',true)->count();
    }
    public function render()
    {

        $recentChecklists = Checklist::with([
            'employee',
            'employee.user',
        ])
        ->whereIn('status', ['approved', 'pending'])
            ->latest()
            ->take(10)
            ->get();

        $topManagers = Checklist::select('user_id', DB::raw('count(*) as uploads_count'))
         ->where('status', 'approved')    
        ->groupBy('user_id')
            ->orderByDesc('uploads_count')
            ->take(10)
            ->with('user')
            ->get();
        return view('livewire.dashboard.hr-dashboard', [
            'recentChecklists' => $recentChecklists,
            'topManagers' => $topManagers
        ]);
    }
}
