<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Checklist;
use App\Models\Employee;
use App\Models\Zone;
use DB;

class ManagerDashboard extends Component
{
    public $pendingCount;
    public $approvedCount;
    public $employeeCount;
    public $rejectedCount;
    public function mount()
    {
        $this->pendingCount = Checklist::where('status', 'pending')->where('user_id', auth()->id())->count();
        $this->approvedCount = Checklist::where('status', 'approved')->where('user_id', auth()->id())->count();
        $this->employeeCount = Employee::where('is_active', true)->where('user_id', auth()->id())->count();
        $this->rejectedCount = Checklist::where('status', 'rejected')->where('user_id', auth()->id())->count();
    }
    public function render()
    {
        $recentChecklists = Checklist::with(['employee', 'employee.user'])
            ->where('user_id', auth()->id())
            ->latest()
            ->take(10)
            ->get();
      
                $employeeUploads = DB::table('employees')
        ->select(
            'employees.id',
            DB::raw("CONCAT(employees.first_name, ' ', employees.last_name) AS fullname"),
            DB::raw('COUNT(checklists.id) as uploads_count')
        )
        ->leftJoin('checklists', function ($join) {
            $join->on('employees.id', '=', 'checklists.employee_id')
                 ->where('checklists.user_id', auth()->id());
        })
        ->where('employees.user_id', auth()->id())
        ->where('employees.is_active', true)
        ->groupBy('employees.id', 'employees.first_name', 'employees.last_name')
        ->orderByDesc('uploads_count')
        ->limit(10)
        ->get();
        return view('livewire.dashboard.manager-dashboard', [
            'recentChecklists' => $recentChecklists,
            'employeeUploads' => $employeeUploads,

        ]);
    }
}
