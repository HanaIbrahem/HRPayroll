<?php

use App\Livewire\Dashboard;
use App\Livewire\Departments\DepartmentComponent;
use App\Livewire\Departments\DepartmentEdit;
use App\Livewire\Employee\EmployeeComponent;
use App\Livewire\Employee\EmployeeEdit;
use Illuminate\Support\Facades\Route;


Route::get('/',Dashboard::class)->name('dashboard');
Route::get('/department',DepartmentComponent::class )->name('department');
Route::get('/department/edit/{department}',DepartmentEdit::class )->name('department.edit');
// employee

Route::get('/employee',EmployeeComponent::class )->name('employee');
Route::get('/employee/edit/{employee}',EmployeeEdit::class )->name('employee.edit');


