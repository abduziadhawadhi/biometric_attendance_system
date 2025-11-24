<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    /**
     * Admin Dashboard – cards + employee lists.
     *
     * card:
     *  - all     : all employees
     *  - present : employees present today
     *  - absent  : employees absent today
     */
    public function index(Request $request)
    {
        $card   = $request->get('card', 'all');      // all | present | absent
        $search = trim($request->get('employee_name', ''));

        $tz    = 'Africa/Nairobi';
        $today = Carbon::now($tz)->toDateString();

        // --------- Top cards ---------
        $totalEmployees = Employee::count();

        $presentIdsToday = Attendance::whereDate('created_at', $today)
            ->whereIn('status', ['present', 'permission'])
            ->whereNotNull('check_in')
            ->pluck('employee_id')
            ->unique()
            ->toArray();

        $presentToday = count($presentIdsToday);

        $permissionToday = Attendance::whereDate('created_at', $today)
            ->where('status', 'permission')
            ->distinct('employee_id')
            ->count('employee_id');

        $absentToday = max($totalEmployees - $presentToday, 0);

        // --------- Employee list under cards ---------
        $employeesQuery = Employee::query();

        if ($search) {
            $employeesQuery->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%")
                  ->orWhere('department', 'ILIKE', "%{$search}%")
                  ->orWhere('position', 'ILIKE', "%{$search}%");
            });
        }

        if ($card === 'present') {
            $employeesQuery->whereIn('id', $presentIdsToday);
            $sectionTitle = 'Employees Present Today';
        } elseif ($card === 'absent') {
            $employeesQuery->whereNotIn('id', $presentIdsToday);
            $sectionTitle = 'Employees Absent Today';
        } else {
            $sectionTitle = 'All Employees';
        }

        $employees = $employeesQuery
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.dashboard', [
            'card'            => $card,
            'sectionTitle'    => $sectionTitle,
            'employees'       => $employees,
            'employeeName'    => $search,
            'totalEmployees'  => $totalEmployees,
            'presentToday'    => $presentToday,
            'absentToday'     => $absentToday,
            'permissionToday' => $permissionToday,
        ]);
    }

    /**
     * Export attendance data to Excel (still works, used by button in the dashboard).
     */
    public function export(Request $request)
    {
        $search    = trim($request->get('employee_name', ''));
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        $tz    = 'Africa/Nairobi';
        $today = Carbon::now($tz)->toDateString();

        if (!$startDate) {
            $startDate = Carbon::now($tz)->subDays(6)->toDateString();
        }
        if (!$endDate) {
            $endDate = $today;
        }

        $fileName = 'attendance_' . $startDate . '_to_' . $endDate . '.xlsx';

        return Excel::download(
            new AttendanceExport($search, $startDate, $endDate),
            $fileName
        );
    }
}
