<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Login
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Password Reset (Optional Pages)
Route::get('/password/reset', function () { return view('auth.passwords.email'); })->name('password.request');
Route::post('/password/email', [LoginController::class, 'sendResetLinkEmail'])->name('password.email');


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {

    // Admin Dashboard
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    // Export Attendance to Excel
    Route::get('/admin/export', [AdminController::class, 'export'])->name('admin.export');

    // Add Employee
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
});


/*
|--------------------------------------------------------------------------
| Employee Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:employee'])->group(function () {

    // Employee Dashboard
    Route::get('/employee/dashboard', [EmployeeController::class, 'dashboard'])->name('employee.dashboard');

    // Attendance Check-in & Check-out
    Route::post('/attendance/checkin', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/attendance/checkout', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');
});


/*
|--------------------------------------------------------------------------
| Fallback
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return redirect()->route('login');
});






