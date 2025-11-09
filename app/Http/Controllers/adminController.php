<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;


class AdminController extends Controller
{
    public function index(Request $request)
    {
        // --- Filtering Inputs ---
        $employeeName = $request->employee_name;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // --- Attendance Query ---
        $query = Attendance::with('employee');

        // Filter by Employee Name
        if (!empty($employeeName)) {
            $query->whereHas('employee', function ($q) use ($employeeName) {
                $q->where('name', 'LIKE', "%$employeeName%");
            });
        }

        // Filter by Date Range using created_at
        if (!empty($startDate) && !empty($endDate)) {
            $query->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
        }

        $attendances = $query->orderBy('created_at', 'DESC')->paginate(10);

        // --- Dashboard Counts ---
        $today = Carbon::today()->format('Y-m-d');

        $totalEmployees = Employee::count();

        $presentToday = Attendance::whereDate('created_at', $today)
            ->whereNotNull('check_in')
            ->distinct('employee_id')
            ->count('employee_id');

        $absentToday = $totalEmployees - $presentToday;

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

    public function export(Request $request)
{
    return Excel::download(
        new AttendanceExport(
            $request->employee_name,
            $request->start_date,
            $request->end_date
        ),
        'attendance_records.xlsx'
    );
}

}








