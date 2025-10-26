<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        // Filters
        $employeeName = $request->input('employee_name');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Base query
        $query = Attendance::with('employee');

        if ($employeeName) {
            $query->whereHas('employee', function ($q) use ($employeeName) {
                $q->where('name', 'LIKE', "%{$employeeName}%");
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(10);

        // Dashboard stats
        $totalEmployees = Employee::count();
        $presentToday = Attendance::whereDate('date', today())->count();
        $absentToday = $totalEmployees - $presentToday;

        return view('admin.dashboard', compact(
            'attendances',
            'employeeName',
            'startDate',
            'endDate',
            'totalEmployees',
            'presentToday',
            'absentToday'
        ));
    }

    public function export(Request $request)
    {
        $employeeName = $request->input('employee_name');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        return Excel::download(new AttendanceExport($employeeName, $startDate, $endDate), 'attendance_report.xlsx');
    }
}
