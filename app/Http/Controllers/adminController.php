<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard with filters, stats, and export.
     */
    public function index(Request $request)
    {
        // Get filter inputs
        $employeeName = $request->input('employee_name');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Get today’s date
        $today = Carbon::today()->toDateString();

        // Base query for attendance
        $query = Attendance::with('employee');

        // ✅ Filter by employee name if provided
        if ($employeeName) {
            $query->whereHas('employee', function ($q) use ($employeeName) {
                $q->where('name', 'LIKE', "%{$employeeName}%");
            });
        }

        // ✅ Filter by date range if provided
        if ($startDate && $endDate) {
            // Change 'check_in' to your correct date column name if different
            $query->whereBetween('check_in', [$startDate, $endDate]);
        }

        // ✅ Fetch attendance records (paginated)
        $attendances = $query->orderBy('check_in', 'desc')->paginate(10);

        // ✅ Dashboard stats
        $totalEmployees = Employee::count();

        // Change 'check_in' to your actual date column
        $presentToday = Attendance::whereDate('check_in', $today)->count();

        // If you store status (e.g., present/absent), use this
        $absentToday = $totalEmployees - $presentToday;

        // ✅ Pass data to the view
        return view('admin.dashboard', compact(
            'attendances',
            'totalEmployees',
            'presentToday',
            'absentToday',
            'employeeName',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export attendance data to Excel file.
     */
    public function export(Request $request)
    {
        return Excel::download(new AttendanceExport($request), 'attendance_report.xlsx');
    }
}

