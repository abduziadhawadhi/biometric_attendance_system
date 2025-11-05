<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        // Filters
        $employeeName = $request->get('employee_name');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Today's Date
        $today = Carbon::today()->toDateString();

        // ======================
        // Dashboard Summary
        // ======================

        // Total Employees
        $totalEmployees = Employee::count();

        // Present Today = Employees who have a check-in today
        $presentToday = Attendance::whereDate('attendance_date', $today)
            ->whereNotNull('check_in')
            ->distinct('employee_id')
            ->count('employee_id');

        // Absent Today = Total Employees - Present Today
        $absentToday = $totalEmployees - $presentToday;

        // ======================
        // Attendance / Employee Query with Filters
        // ======================
        $query = Attendance::with('employee')->orderByDesc('attendance_date');

        if ($employeeName) {
            $query->whereHas('employee', function ($q) use ($employeeName) {
                $q->where('name', 'LIKE', "%{$employeeName}%");
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('attendance_date', [$startDate, $endDate]);
        }

        $attendances = $query->paginate(10);

        // ======================
        // Send Data to Dashboard View
        // ======================
        return view('admin.dashboard', [
            'totalEmployees' => $totalEmployees,
            'presentToday' => $presentToday,
            'absentToday' => $absentToday,
            'attendances' => $attendances,
            'employeeName' => $employeeName,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
}




