<?php

use App\Livewire\Dashboard;
use App\Livewire\Department;
use App\Livewire\DepartmentComponent;
use Illuminate\Support\Facades\Route;

Route::get('/',Dashboard::class)->name('dashboard');
Route::get('/department',DepartmentComponent::class)->name('department');


