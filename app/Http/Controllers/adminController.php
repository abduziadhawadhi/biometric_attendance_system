<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Display the Admin Dashboard.
     */
    public function index(Request $request)
    {
        // Filters
        $employeeName = $request->get('employee_name');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Calculate today's date
        $today = Carbon::today()->toDateString();

        // Dashboard summary
        $totalEmployees = Employee::count();
        $presentToday = Attendance::whereDate('attendance_date', $today)
            ->whereNotNull('check_in')
            ->distinct('employee_id')
            ->count('employee_id');
        $absentToday = $totalEmployees - $presentToday;

        // Query Attendance Records
        $query = Attendance::with('employee');

        if ($employeeName) {
            $query->whereHas('employee', function ($q) use ($employeeName) {
                $q->where('name', 'like', "%{$employeeName}%");
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('attendance_date', [$startDate, $endDate]);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->paginate(10);

        // Fix status dynamically
        foreach ($attendances as $attendance) {
            $attendance->status = $attendance->check_in ? 'Present' : 'Absent';
        }

        // Return data to view
        return view('admin.dashboard', compact(
            'totalEmployees',
            'presentToday',
            'absentToday',
            'attendances',
            'employeeName',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export attendance to Excel.
     */
    public function export(Request $request)
    {
        $employeeName = $request->get('employee_name');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        return Excel::download(new AttendanceExport($employeeName, $startDate, $endDate), 'attendance_report.xlsx');
    }
}



