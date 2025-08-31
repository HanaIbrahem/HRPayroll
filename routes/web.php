<?php

use App\Livewire\Auth\UserComponent;
use App\Livewire\Auth\UserEdit;
use App\Livewire\Checklist\ChecklistComponent;
use App\Livewire\Checklist\ChecklistCreate;
use App\Livewire\Checklist\ChecklistEdit;
use App\Livewire\Dashboard;
use App\Livewire\Departments\DepartmentComponent;
use App\Livewire\Departments\DepartmentEdit;
use App\Livewire\Employee\EmployeeComponent;
use App\Livewire\Employee\EmployeeEdit;
use App\Livewire\Zone\ZoneComponent;
use App\Livewire\Zone\ZoneEdit;

use Illuminate\Support\Facades\Route;



// admin roues 
Route::middleware(['auth','role:admin'])->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');

    Route::get('/department', DepartmentComponent::class)->name('department');
    Route::get('/department/edit/{department}', DepartmentEdit::class)->name('department.edit');
    // employee
    Route::get('/employee', EmployeeComponent::class)->name('employee');
    Route::get('/employee/edit/{employee}', EmployeeEdit::class)->name('employee.edit');
    // zones
    Route::get('/zone', ZoneComponent::class)->name('zone');
    Route::get('/zone/edit/{zone}', ZoneEdit::class)->name('zone.edit');
    // authentication 
    Route::get('/users',UserComponent::class)->name('user');
    Route::get('/uses/edit/{user}',UserEdit::class)->name('user.edit');

});

// manager routes 
Route::middleware(['auth','role:manager'])->group(function () {

   // checklist
    Route::get('/checklist', ChecklistComponent::class)->name('checklist');
    Route::get('/checklist/create', ChecklistCreate::class)->name('checklist.create');
    Route::get('/checklist/edit/{checklist}', ChecklistEdit::class)->name('checklist.edit');

});


// hr routes
Route::middleware(['auth','role:hr'])->group(function () {
});



// login routes 
Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\AuthController::class, 'index'])->name('login');
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'store'])->name('login.store');
});
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');