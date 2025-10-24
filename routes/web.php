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
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root URL to login page
Route::get('/', function () {
    return redirect('/login');
});

// Laravel default authentication routes
Auth::routes();

// Routes accessible only after login
Route::middleware(['auth'])->group(function () {

    // Redirect user to dashboard depending on role
    Route::get('/dashboard', function () {
        return auth()->user()->role === 'admin'
            ? redirect('/admin/dashboard')
            : redirect('/employee/dashboard');
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | Employee Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:employee'])->group(function () {
        Route::get('/employee/dashboard', [EmployeeController::class, 'index'])->name('employee.dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | Attendance Routes (Check In / Check Out)
    |--------------------------------------------------------------------------
    */
    Route::post('/attendance/checkin', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/attendance/checkout', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');
});


