<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        // Get filters
        $employeeName = $request->get('employee_name');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Stats
        $totalEmployees = Employee::count();

        $today = Carbon::today()->toDateString();

        $presentToday = Attendance::whereDate('check_in', $today)->distinct('employee_id')->count('employee_id');
        $absentToday = $totalEmployees - $presentToday;

        // Attendance query (for filtering)
        $query = Attendance::with('employee');

        if ($employeeName) {
            $query->whereHas('employee', function ($q) use ($employeeName) {
                $q->where('name', 'LIKE', "%{$employeeName}%");
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('check_in', [$startDate, $endDate]);
        }

        // Fetch attendance data
        $attendances = $query->orderBy('check_in', 'desc')->paginate(10);

        // Employees list for search suggestions
        $employees = Employee::select('id', 'name')->orderBy('name')->get();

        return view('admin.dashboard', compact(
            'totalEmployees',
            'presentToday',
            'absentToday',
            'attendances',
            'employees',
            'employeeName',
            'startDate',
            'endDate'
        ));
    }
}


