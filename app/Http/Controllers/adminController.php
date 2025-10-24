<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;

class AdminController extends Controller
{
    public function index()
    {
        $totalEmployees = Employee::count();
        $todayRecords = Attendance::whereDate('created_at', today())->count();
        $recentAttendance = Attendance::with('employee')
                            ->latest()
                            ->take(10)
                            ->get();

        return view('admin.dashboard', compact('totalEmployees', 'todayRecords', 'recentAttendance'));
    }
}
