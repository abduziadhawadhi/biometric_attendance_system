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
    protected string $tz = 'Africa/Nairobi';

    /**
     * Show Admin Dashboard.
     */
    public function index(Request $request)
    {
        $card   = $request->get('card', 'all');   // all | present | absent
        $search = trim($request->get('employee_name', ''));

        $today  = Carbon::now($this->tz)->toDateString();

        // ====== Top cards ======
        $totalEmployees = Employee::count();

        $presentToday = Attendance::whereDate('created_at', $today)
            ->whereIn('status', ['present', 'permission'])
            ->whereNotNull('check_in')
            ->distinct('employee_id')
            ->count('employee_id');

        $permissionToday = Attendance::whereDate('created_at', $today)
            ->where('status', 'permission')
            ->distinct('employee_id')
            ->count('employee_id');

        $absentToday = max($totalEmployees - $presentToday - $permissionToday, 0);

        $employees   = collect();
        $attendances = collect();
        $mode        = 'all';
        $sectionTitle = '';

        // ====== card = all → all employees list ======
        if ($card === 'all') {
            $employeesQuery = Employee::query();

            if ($search !== '') {
                $employeesQuery->where(function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                      ->orWhere('email', 'ILIKE', "%{$search}%")
                      ->orWhere('department', 'ILIKE', "%{$search}%")
                      ->orWhere('position', 'ILIKE', "%{$search}%");
                });
            }

            $employees = $employeesQuery
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString();

            $mode         = 'employees_all';
            $sectionTitle = 'All Employees';
        }

        // ====== card = present → present employees today ======
        elseif ($card === 'present') {
            $attendanceQuery = Attendance::with('employee')
                ->whereDate('created_at', $today)
                ->whereIn('status', ['present', 'permission'])
                ->whereNotNull('check_in');

            if ($search !== '') {
                $attendanceQuery->whereHas('employee', function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                      ->orWhere('email', 'ILIKE', "%{$search}%")
                      ->orWhere('department', 'ILIKE', "%{$search}%")
                      ->orWhere('position', 'ILIKE', "%{$search}%");
                });
            }

            $attendances = $attendanceQuery
                ->orderBy('created_at', 'desc')
                ->paginate(10)
                ->withQueryString();

            // Late & overtime for each record
            foreach ($attendances as $att) {
                $created = Carbon::parse($att->created_at)->setTimezone($this->tz);
                $date    = $created->toDateString();

                $officeStart = Carbon::parse($date . ' 08:00:00', $this->tz);
                $officeEnd   = Carbon::parse($date . ' 17:00:00', $this->tz);

                $lateMinutes     = 0;
                $overtimeMinutes = 0;

                if ($att->check_in) {
                    $checkIn = Carbon::parse($date . ' ' . $att->check_in, $this->tz);
                    if ($checkIn->gt($officeStart)) {
                        $lateMinutes = $checkIn->diffInMinutes($officeStart);
                    }
                }

                if ($att->check_out) {
                    $checkOut = Carbon::parse($date . ' ' . $att->check_out, $this->tz);
                    if ($checkOut->gt($officeEnd)) {
                        $overtimeMinutes = $checkOut->diffInMinutes($officeEnd);
                    }
                }

                $att->late_minutes     = $lateMinutes;
                $att->overtime_minutes = $overtimeMinutes;
            }

            $mode         = 'present_today';
            $sectionTitle = 'Employees Present Today';
        }

        // ====== card = absent → employees who did NOT check in today ======
        elseif ($card === 'absent') {
            $presentIds = Attendance::whereDate('created_at', $today)
                ->whereIn('status', ['present', 'permission'])
                ->whereNotNull('check_in')
                ->pluck('employee_id')
                ->unique()
                ->toArray();

            $employeesQuery = Employee::query()
                ->whereNotIn('id', $presentIds);

            if ($search !== '') {
                $employeesQuery->where(function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                      ->orWhere('email', 'ILIKE', "%{$search}%")
                      ->orWhere('department', 'ILIKE', "%{$search}%")
                      ->orWhere('position', 'ILIKE', "%{$search}%");
                });
            }

            $employees = $employeesQuery
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString();

            $mode         = 'employees_absent';
            $sectionTitle = 'Employees Absent Today';
        }

        return view('admin.dashboard', [
            'card'            => $card,
            'mode'            => $mode,
            'sectionTitle'    => $sectionTitle,
            'employees'       => $employees,
            'attendances'     => $attendances,
            'employeeName'    => $search,
            'totalEmployees'  => $totalEmployees,
            'presentToday'    => $presentToday,
            'absentToday'     => $absentToday,
            'permissionToday' => $permissionToday,
        ]);
    }

    /**
     * Export to Excel using your AttendanceExport class.
     * For now, export ALL attendance; you can later add filters.
     */
    public function export(Request $request)
    {
        $search = trim($request->get('employee_name', ''));
        $tz     = $this->tz;
        $today  = Carbon::now($tz)->toDateString();

        // Example: export from start of month to today
        $startDate = Carbon::now($tz)->startOfMonth()->toDateString();
        $endDate   = $today;

        $fileName = 'attendance_' . $startDate . '_to_' . $endDate . '.xlsx';

        return Excel::download(
            new AttendanceExport($search, $startDate, $endDate),
            $fileName
        );
    }
}
