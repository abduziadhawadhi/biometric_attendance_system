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

Route::get('/', function () {
    return redirect('/login');
});

// Laravel built-in authentication
Auth::routes();

// Protected routes (require login)
Route::middleware(['auth'])->group(function () {

    // Redirect based on user role
    Route::get('/dashboard', function () {
        if (auth()->user()->role === 'admin') {
            return redirect('/admin/dashboard');
        } else {
            return redirect('/employee/dashboard');
        }
    })->name('dashboard');

    // Admin dashboard
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    });

    // Employee dashboard
    Route::middleware(['role:employee'])->group(function () {
        Route::get('/employee/dashboard', [EmployeeController::class, 'index'])->name('employee.dashboard');
    });

    // Attendance routes
    Route::post('/attendance/checkin', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/attendance/checkout', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');
});




Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
