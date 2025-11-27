<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;

class AdminController extends Controller
{
    /**
     * Use Nairobi timezone everywhere on admin side.
     */
    protected string $tz = 'Africa/Nairobi';

    /**
     * Show Admin Dashboard.
     */
    public function index(Request $request)
    {
        $card         = $request->get('card', 'all'); // all | present | absent
        $employeeName = trim($request->get('employee_name', ''));
        $startDate    = $request->get('start_date');
        $endDate      = $request->get('end_date');

        $today = Carbon::now($this->tz)->toDateString();

        // ------------------------------------------------------------------
        // TOP CARDS (always based on TODAY, not on filters)
        // ------------------------------------------------------------------
        $totalEmployees = Employee::count();

        $presentToday = Attendance::whereDate('created_at', $today)
            ->whereNotNull('check_in')
            ->distinct('employee_id')
            ->count('employee_id');

        $absentToday = max($totalEmployees - $presentToday, 0);

        // ------------------------------------------------------------------
        // Default date filter = last 7 days (including today)
        // ------------------------------------------------------------------
        if (!$startDate && !$endDate) {
            $startDate = Carbon::now($this->tz)->subDays(6)->toDateString();
            $endDate   = $today;
        } else {
            if (!$startDate) {
                $startDate = $today;
            }
            if (!$endDate) {
                $endDate = $today;
            }
        }

        $employees    = null;
        $attendances  = null;
        $viewMode     = 'employeesAll';   // employeesAll | presentAttendance | absentEmployees
        $sectionTitle = '';

        // ------------------------------------------------------------------
        // CARD = ALL  → list ALL EMPLOYEES
        // ------------------------------------------------------------------
        if ($card === 'all') {
            $employeesQuery = Employee::query();

            if ($employeeName) {
                $employeesQuery->where(function ($q) use ($employeeName) {
                    $q->where('name', 'ILIKE', "%{$employeeName}%")
                        ->orWhere('email', 'ILIKE', "%{$employeeName}%")
                        ->orWhere('department', 'ILIKE', "%{$employeeName}%")
                        ->orWhere('position', 'ILIKE', "%{$employeeName}%");
                });
            }

            $employees = $employeesQuery
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString();

            $viewMode     = 'employeesAll';
            $sectionTitle = 'All Employees';
        }

        // ------------------------------------------------------------------
        // CARD = PRESENT  → Attendance records (with late & overtime)
        // ------------------------------------------------------------------
        elseif ($card === 'present') {
            $attendanceQuery = Attendance::with('employee')
                ->whereBetween('created_at', [
                    $startDate . ' 00:00:00',
                    $endDate   . ' 23:59:59',
                ])
                ->whereNotNull('check_in'); // only people who actually checked in

            if ($employeeName) {
                $attendanceQuery->whereHas('employee', function ($q) use ($employeeName) {
                    $q->where('name', 'ILIKE', "%{$employeeName}%")
                        ->orWhere('email', 'ILIKE', "%{$employeeName}%")
                        ->orWhere('department', 'ILIKE', "%{$employeeName}%")
                        ->orWhere('position', 'ILIKE', "%{$employeeName}%");
                });
            }

            $attendances = $attendanceQuery
                ->orderBy('created_at', 'desc')
                ->paginate(10)
                ->withQueryString();

            // Compute Late & Overtime for each record
            foreach ($attendances as $att) {
                $created = Carbon::parse($att->created_at)->setTimezone($this->tz);
                $date    = $created->toDateString();

                $officeStart = Carbon::parse($date . ' 08:00:00', $this->tz);
                $officeEnd   = Carbon::parse($date . ' 17:00:00', $this->tz);

                $lateMinutes     = 0;
                $overtimeMinutes = 0;

                // Late
                if ($att->check_in) {
                    $checkIn = Carbon::parse($date . ' ' . $att->check_in, $this->tz);
                    if ($checkIn->gt($officeStart)) {
                        $lateMinutes = $checkIn->diffInMinutes($officeStart);
                    }
                }

                // Overtime
                if ($att->check_out) {
                    $checkOut = Carbon::parse($date . ' ' . $att->check_out, $this->tz);
                    if ($checkOut->gt($officeEnd)) {
                        $overtimeMinutes = $checkOut->diffInMinutes($officeEnd);
                    }
                }

                $att->late_minutes     = $lateMinutes;
                $att->overtime_minutes = $overtimeMinutes;
            }

            $viewMode     = 'presentAttendance';
            $sectionTitle = 'Attendance Records (Present)';
        }

        // ------------------------------------------------------------------
        // CARD = ABSENT  → Employees who did NOT check in on selected day
        // ------------------------------------------------------------------
        elseif ($card === 'absent') {
            // we use only startDate as the "day" to check absence
            $filterDate = $startDate ?: $today;

            // IDs of employees who checked in that day
            $presentIds = Attendance::whereDate('created_at', $filterDate)
                ->whereNotNull('check_in')
                ->pluck('employee_id')
                ->unique()
                ->toArray();

            $employeesQuery = Employee::query();

            if ($employeeName) {
                $employeesQuery->where(function ($q) use ($employeeName) {
                    $q->where('name', 'ILIKE', "%{$employeeName}%")
                        ->orWhere('email', 'ILIKE', "%{$employeeName}%")
                        ->orWhere('department', 'ILIKE', "%{$employeeName}%")
                        ->orWhere('position', 'ILIKE', "%{$employeeName}%");
                });
            }

            if (!empty($presentIds)) {
                $employeesQuery->whereNotIn('id', $presentIds);
            }

            $employees = $employeesQuery
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString();

            $viewMode     = 'absentEmployees';
            $sectionTitle = 'Employees Absent on ' . Carbon::parse($filterDate)->format('d M Y');
        }

        return view('admin.dashboard', [
            'card'            => $card,
            'viewMode'        => $viewMode,
            'sectionTitle'    => $sectionTitle,
            'employees'       => $employees,
            'attendances'     => $attendances,
            'employeeName'    => $employeeName,
            'startDate'       => $startDate,
            'endDate'         => $endDate,
            'totalEmployees'  => $totalEmployees,
            'presentToday'    => $presentToday,
            'absentToday'     => $absentToday,
        ]);
    }

    /**
     * Export attendance data to Excel (uses current filters).
     */
    public function export(Request $request)
    {
        $employeeName = trim($request->get('employee_name', ''));
        $startDate    = $request->get('start_date');
        $endDate      = $request->get('end_date');

        $today = Carbon::now($this->tz)->toDateString();

        if (!$startDate) {
            $startDate = Carbon::now($this->tz)->subDays(6)->toDateString();
        }
        if (!$endDate) {
            $endDate = $today;
        }

        $fileName = 'attendance_' . $startDate . '_to_' . $endDate . '.xlsx';

        return Excel::download(
            new AttendanceExport($employeeName, $startDate, $endDate),
            $fileName
        );
    }
}
