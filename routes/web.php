<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider within a group 
| which contains the "web" middleware group.
|
*/

// -----------------------------
// Redirect root to login
// -----------------------------
Route::get('/', function () {
    return redirect()->route('login');
});

// -----------------------------
// Authentication Routes
// -----------------------------
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Password reset routes (fix for password.request error)
Route::get('password/reset', function () {
    return view('auth.passwords.email');
})->name('password.request');

// -----------------------------
// Generic Dashboard Redirect
// -----------------------------
Route::middleware(['auth'])->get('/dashboard', function () {
    $user = auth()->user();

    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'employee') {
        return redirect()->route('employee.dashboard');
    }

    auth()->logout();
    return redirect()->route('login');
})->name('dashboard');

// -----------------------------
// Admin Routes
// -----------------------------
Route::middleware(['auth', 'role:admin'])->group(function () {
    
    // Dashboard
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    
    // Export attendance to Excel
    Route::get('/admin/export', [AdminController::class, 'export'])->name('admin.export');
    
    // Add employees
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
});

// -----------------------------
// Employee Routes
// -----------------------------
Route::middleware(['auth', 'role:employee'])->group(function () {

    // Employee dashboard
    Route::get('/employee/dashboard', [EmployeeController::class, 'dashboard'])->name('employee.dashboard');

    // Attendance check-in & check-out
    Route::post('/attendance/checkin', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/attendance/checkout', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');
});

Route::middleware(['auth', 'role:employee'])->group(function () {
    Route::get('/employee/change-password', [PasswordController::class, 'showChangeForm'])->name('employee.password.form');
    Route::post('/employee/change-password', [PasswordController::class, 'update'])->name('employee.password.update');
});

// -----------------------------
// Fallback Route for Undefined Pages
// -----------------------------
Route::fallback(function () {
    return redirect()->route('login');
});
