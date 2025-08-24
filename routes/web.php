<?php

use App\Livewire\Dashboard;
use App\Livewire\Departments\DepartmentComponent;
use App\Livewire\Departments\DepartmentEdit;
use App\Livewire\Employee\EmployeeComponent;
use App\Livewire\Employee\EmployeeEdit;
use App\Livewire\Zone\ZoneComponent;
use App\Livewire\Zone\ZoneEdit;
use Illuminate\Support\Facades\Route;


Route::get('/',Dashboard::class)->name('dashboard');
Route::get('/department',DepartmentComponent::class )->name('department');
Route::get('/department/edit/{department}',DepartmentEdit::class )->name('department.edit');
// employee
Route::get('/employee',EmployeeComponent::class )->name('employee');
Route::get('/employee/edit/{employee}',EmployeeEdit::class )->name('employee.edit');
// zones
Route::get('/zone',ZoneComponent::class )->name('zone');
Route::get('/zone/edit/{zone}',ZoneEdit::class )->name('zone.edit');




