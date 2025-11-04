<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Default redirect to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Auth::routes();

// Custom logout route
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ====================== ADMIN ROUTES ======================
Route::middleware(['auth', 'role:admin'])->group(function () {

    // Admin Dashboard
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    // Excel Export Route (Filtered Export)
    Route::get('/admin/export', [AdminController::class, 'export'])->name('admin.export');

    // Employee Management Routes
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
});

// ====================== EMPLOYEE ROUTES ======================
Route::middleware(['auth', 'role:employee'])->group(function () {
    Route::get('/employee/dashboard', [EmployeeController::class, 'dashboard'])->name('employee.dashboard');
});

