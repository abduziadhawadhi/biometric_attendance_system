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
     * Show Admin Dashboard.
     */
    public function index(Request $request)
    {
        $card       = $request->get('card', 'all');        // all | present | absent
        $search     = trim($request->get('employee_name', ''));
        $startDate  = $request->get('start_date');
        $endDate    = $request->get('end_date');

        $tz    = 'Africa/Nairobi';
        $today = Carbon::now($tz)->toDateString();

        // ------------------------
        // TOP CARDS (always "today")
        // ------------------------
        $totalEmployees = Employee::count();

        // Present count
        $presentToday = Attendance::whereDate('created_at', $today)
            ->whereIn('status', ['present', 'permission'])
            ->whereNotNull('check_in')
            ->distinct('employee_id')
            ->count('employee_id');

        // Permission count
        $permissionToday = Attendance::whereDate('created_at', $today)
            ->where('status', 'permission')
            ->distinct('employee_id')
            ->count('employee_id');

        // Absent count
        $absentToday = max($totalEmployees - $presentToday - $permissionToday, 0);

        // ------------------------
        // DATE RANGE FILTER
        // ------------------------
        if (!$startDate && !$endDate) {
            $startDate = Carbon::now($tz)->subDays(6)->toDateString();
            $endDate   = $today;
        } else {
            if (!$startDate) $startDate = $today;
            if (!$endDate)   $endDate   = $today;
        }

        $filterDate = $startDate ?: $today;
        $viewMode = 'attendance';
        $sectionTitle = 'Attendance Records';
        $attendances = null;
        $employees = null;

        // ------------------------
        // ABSENT LIST
        // ------------------------
        if ($card === 'absent') {

            $presentIds = Attendance::whereDate('created_at', $filterDate)
                ->whereIn('status', ['present', 'permission'])
                ->whereNotNull('check_in')
                ->pluck('employee_id')
                ->unique()
                ->toArray();

            $employeesQuery = Employee::query();

            if ($search) {
                $employeesQuery->where(function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                        ->orWhere('email', 'ILIKE', "%{$search}%")
                        ->orWhere('department', 'ILIKE', "%{$search}%")
                        ->orWhere('position', 'ILIKE', "%{$search}%");
                });
            }

            $employeesQuery->whereNotIn('id', $presentIds);

            $employees = $employeesQuery->orderBy('name')->paginate(10)->withQueryString();

            $viewMode     = 'absent';
            $sectionTitle = 'Employees Absent on ' . Carbon::parse($filterDate)->format('d M Y');
        } 
        // ------------------------
        // ATTENDANCE LIST
        // ------------------------
        else {

            $attendanceQuery = Attendance::with('employee')
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

            if ($search) {
                $attendanceQuery->whereHas('employee', function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                      ->orWhere('email', 'ILIKE', "%{$search}%")
                      ->orWhere('department', 'ILIKE', "%{$search}%")
                      ->orWhere('position', 'ILIKE', "%{$search}%");
                });
            }

            if ($card === 'present') {
                $attendanceQuery->whereIn('status', ['present', 'permission'])
                    ->whereNotNull('check_in');
                $sectionTitle = 'Employees Present (Attendance Records)';
            }

            $attendances = $attendanceQuery->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

            // LATE & OVERTIME CALCULATION
            foreach ($attendances as $att) {
                $created = Carbon::parse($att->created_at)->setTimezone($tz);
                $date    = $created->toDateString();

                $officeStart = Carbon::parse($date . ' 08:00:00', $tz);
                $officeEnd   = Carbon::parse($date . ' 17:00:00', $tz);

                $att->late_minutes     = ($att->check_in  && Carbon::parse($date . ' ' . $att->check_in)->gt($officeStart))
                                         ? Carbon::parse($date . ' ' . $att->check_in)->diffInMinutes($officeStart)
                                         : 0;

                $att->overtime_minutes = ($att->check_out && Carbon::parse($date . ' ' . $att->check_out)->gt($officeEnd))
                                         ? Carbon::parse($date . ' ' . $att->check_out)->diffInMinutes($officeEnd)
                                         : 0;
            }
        } // <<--- THIS CLOSING BRACE WAS MISSING ❗

        return view('admin.dashboard', [
            'card'            => $card,
            'viewMode'        => $viewMode,
            'sectionTitle'    => $sectionTitle,
            'attendances'     => $attendances,
            'employees'       => $employees,
            'employeeName'    => $search,
            'startDate'       => $startDate,
            'endDate'         => $endDate,
            'totalEmployees'  => $totalEmployees,
            'presentToday'    => $presentToday,
            'absentToday'     => $absentToday,
            'permissionToday' => $permissionToday,
        ]);
    }

    /**
     * Export attendance data to Excel.
     */
    public function export(Request $request)
    {
        $search    = trim($request->get('employee_name', ''));
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        $tz    = 'Africa/Nairobi';
        $today = Carbon::now($tz)->toDateString();

        if (!$startDate) $startDate = Carbon::now($tz)->subDays(6)->toDateString();
        if (!$endDate)   $endDate   = $today;

        return Excel::download(
            new AttendanceExport($search, $startDate, $endDate),
            'attendance_' . $startDate . '_to_' . $endDate . '.xlsx'
        );
    }
}
