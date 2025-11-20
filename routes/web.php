<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Default: send to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Laravel auth scaffolding (login, register, reset password…)
Auth::routes();

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])
        ->name('admin.dashboard');

    Route::get('/admin/export', [AdminController::class, 'export'])
        ->name('admin.export');

    // Employee management by admin
    Route::get('/employees/create', [EmployeeController::class, 'create'])
        ->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])
        ->name('employees.store');
});

/*
|--------------------------------------------------------------------------
| Employee Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:employee'])->group(function () {

    // Employee dashboard
    Route::get('/employee/dashboard', [EmployeeController::class, 'dashboard'])
        ->name('employee.dashboard');

    // Manual check-in / check-out
    Route::post('/attendance/checkin', [AttendanceController::class, 'checkIn'])
        ->name('attendance.checkin');

    Route::post('/attendance/checkout', [AttendanceController::class, 'checkOut'])
        ->name('attendance.checkout');
});
