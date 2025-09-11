<?php

use App\Livewire\Auth\UserComponent;
use App\Livewire\Auth\UserEdit;
use App\Livewire\Checklist\ChecklistComponent;
use App\Livewire\Checklist\ChecklistCreate;
use App\Livewire\Checklist\ChecklistEdit;
use App\Livewire\Dashboard;
use App\Livewire\Dashboard\ManagerDashboard;
use App\Livewire\Dashboard\AdminDashboard;
use App\Livewire\Dashboard\HrDashboard;
use App\Livewire\Departments\DepartmentComponent;
use App\Livewire\Departments\DepartmentEdit;
use App\Livewire\Employee\EmployeeComponent;
use App\Livewire\Employee\EmployeeEdit;
use App\Livewire\Hr\ChecklistShow;
use App\Livewire\Hr\EmployeeChecklists;
use App\Livewire\Location\LocationComponent;
use App\Livewire\Location\LocationEdit;
use App\Livewire\Pending\PendingComponent;
use App\Livewire\Report\ReportComponent;
use App\Livewire\Zone\ZoneComponent;
use App\Livewire\Zone\ZoneEdit;
use App\Livewire\Checklist\CkecklistShow;


use Illuminate\Support\Facades\Route;


// shaired route 
Route::middleware(['auth', 'role:manager,hr,admin'])->group(function () {

    Route::get('/', Dashboard::class)->name('dashboard');

});

// admin roues 
Route::middleware(['auth', 'role:admin'])->group(function () {

    // department routes 
    Route::get('/department', DepartmentComponent::class)->name('department');
    Route::get('/department/edit/{department}', DepartmentEdit::class)->name('department.edit');
    // employee
    Route::get('/employee', EmployeeComponent::class)->name('employee');
    Route::get('/employee/edit/{employee}', EmployeeEdit::class)->name('employee.edit');
    // zones
    Route::get('/zone', ZoneComponent::class)->name('zone');
    Route::get('/zone/edit/{zone}', ZoneEdit::class)->name('zone.edit');
    // authentication 
    Route::get('/users', UserComponent::class)->name('user');
    Route::get('/uses/edit/{user}', UserEdit::class)->name('user.edit');

    // employee location routes 
    Route::get('/locations', LocationComponent::class)->name('location');
    Route::get('/location/edit/{location}', LocationEdit::class)->name('location.edit');


});

// manager routes 
Route::middleware(['auth', 'role:manager'])->group(function () {

    // checklist
    Route::get('/checklist', ChecklistComponent::class)->name('checklist');
    Route::get('/checklist/create', ChecklistCreate::class)->name('checklist.create');
    Route::get('/checklist/edit/{checklist}', ChecklistEdit::class)->name('checklist.edit');
    Route::get('/checklist/{checklist}', CkecklistShow::class)->name('checklist.show');

    //     Route::get('/checklists/{checklist}/file', function (Checklist $checklist) {
    //     // Only owner can view (adjust authorization as needed)
    //     abort_unless(auth()->id() === $checklist->user_id, 403);

    //     // Resolve absolute path from DB 'filename'
    //     $abs = (function (string $f) {
    //         $f = ltrim($f, '/\\');

    //         $candidates = [
    //             // storage paths
    //             storage_path("app/$f"),
    //             storage_path("app/public/$f"),
    //             // public paths (if you put files under /public/... manually)
    //             public_path($f),
    //             public_path("uploads/$f"),
    //         ];

    //         foreach ($candidates as $p) {
    //             if (is_file($p)) return $p;
    //         }
    //         return null;
    //     })((string) $checklist->filename);

    //     abort_unless($abs && is_readable($abs), 404, 'File not found');

    //     // Guess MIME; default to Excel if unknown
    //     $mime = (function ($path) {
    //         if (function_exists('mime_content_type')) {
    //             $mt = @mime_content_type($path);
    //             if ($mt) return $mt;
    //         }
    //         return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    //     })($abs);

    //     return response()->file($abs, [
    //         'Content-Type'        => $mime,
    //         'Content-Disposition' => 'inline; filename="'.basename($abs).'"',
    //     ]);
    // })->name('checklists.file');

});


// hr routes
Route::middleware(['auth', 'role:hr'])->group(function () {

    Route::get('/pending', PendingComponent::class)->name('hr.pending');
    Route::get('/checklist/show/{checklist}', ChecklistShow::class)->name('hr.show');
    Route::get('/checklists', EmployeeChecklists::class)->name('hr.checklist');
    Route::get('/report', ReportComponent::class)->name('hr.report');


});



// login routes 
Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\AuthController::class, 'index'])->name('login');
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'store'])->name('login.store');
});
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');