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
        $tz    = $this->tz;
        $today = Carbon::now($tz)->toDateString();

        // Which card is active? all | present | absent
        $card = $request->get('card', 'all');

        // Search text box
        $search = trim($request->get('employee_name', ''));

        // Date filters (blank by default – only used when user chooses)
        $startDate = $request->get('start_date') ?: null;
        $endDate   = $request->get('end_date') ?: null;

        // -----------------------------
        // TOP CARDS (always for today)
        // -----------------------------
        $totalEmployees = Employee::count();

        $presentToday = Attendance::whereDate('created_at', $today)
            ->whereNotNull('check_in')
            ->whereIn('status', ['present', 'permission'])
            ->distinct('employee_id')
            ->count('employee_id');

        $permissionToday = Attendance::whereDate('created_at', $today)
            ->where('status', 'permission')
            ->distinct('employee_id')
            ->count('employee_id');

        $absentToday = max($totalEmployees - $presentToday - $permissionToday, 0);

        // -----------------------------
        // TABLE DATA
        // -----------------------------
        $viewMode     = null;   // 'all' | 'present' | 'absent'
        $sectionTitle = '';
        $employees    = null;   // for all + absent
        $attendances  = null;   // for present

        // 1) TOTAL EMPLOYEES CARD
        if ($card === 'all') {

            $viewMode     = 'all';
            $sectionTitle = 'All Employees';

            $employeesQuery = Employee::query();

            if ($search) {
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
        }

        // 2) PRESENT TODAY CARD
        elseif ($card === 'present') {

            $viewMode     = 'present';
            $sectionTitle = 'Employees Present';

            $attendanceQuery = Attendance::with('employee')
                ->whereNotNull('check_in')
                ->whereIn('status', ['present', 'permission']);

            // Date filters
            if ($startDate && $endDate) {
                $attendanceQuery->whereBetween('created_at', [
                    $startDate . ' 00:00:00',
                    $endDate   . ' 23:59:59',
                ]);
            } elseif ($startDate) {
                $attendanceQuery->where('created_at', '>=', $startDate . ' 00:00:00');
            } elseif ($endDate) {
                $attendanceQuery->where('created_at', '<=', $endDate . ' 23:59:59');
            } else {
                // No dates chosen -> show today
                $attendanceQuery->whereDate('created_at', $today);
            }

            if ($search) {
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

            // ===== Compute Late & Overtime (minutes) for each record =====
            foreach ($attendances as $att) {
                // created_at is always a valid full timestamp
                $created = Carbon::parse($att->created_at)->setTimezone($tz);
                $date    = $created->toDateString(); // e.g. "2025-11-27"

                $officeStart = Carbon::parse($date . ' 08:00:00', $tz);
                $officeEnd   = Carbon::parse($date . ' 17:00:00', $tz);

                $lateMinutes     = 0;
                $overtimeMinutes = 0;

                // ---- CHECK IN ----
                if ($att->check_in) {
                    // If check_in already has a date part, use as-is,
                    // otherwise prefix with $date.
                    $checkInRaw = $att->check_in;
                    $checkInStr = (strpos($checkInRaw, ' ') !== false)
                        ? $checkInRaw
                        : ($date . ' ' . $checkInRaw);

                    $checkIn = Carbon::parse($checkInStr, $tz);

                    if ($checkIn->gt($officeStart)) {
                        $lateMinutes = $checkIn->diffInMinutes($officeStart);
                    }
                }

                // ---- CHECK OUT ----
                if ($att->check_out) {
                    $checkOutRaw = $att->check_out;
                    $checkOutStr = (strpos($checkOutRaw, ' ') !== false)
                        ? $checkOutRaw
                        : ($date . ' ' . $checkOutRaw);

                    $checkOut = Carbon::parse($checkOutStr, $tz);

                    if ($checkOut->gt($officeEnd)) {
                        $overtimeMinutes = $checkOut->diffInMinutes($officeEnd);
                    }
                }

                // attach to model for blade
                $att->late_minutes     = $lateMinutes;
                $att->overtime_minutes = $overtimeMinutes;
            }
        }

        // 3) ABSENT TODAY CARD
        elseif ($card === 'absent') {

            $viewMode = 'absent';

            $filterDate   = $startDate ?: $today;
            $sectionTitle = 'Employees Absent on ' .
                Carbon::parse($filterDate)->format('d M Y');

            $presentIds = Attendance::whereDate('created_at', $filterDate)
                ->whereNotNull('check_in')
                ->whereIn('status', ['present', 'permission'])
                ->pluck('employee_id')
                ->unique()
                ->toArray();

            $employeesQuery = Employee::query()
                ->whereNotIn('id', $presentIds);

            if ($search) {
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
        }

        return view('admin.dashboard', [
            'card'            => $card,
            'viewMode'        => $viewMode,
            'sectionTitle'    => $sectionTitle,
            'employees'       => $employees,
            'attendances'     => $attendances,
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
     * Export attendance data to Excel (uses current filters).
     */
    public function export(Request $request)
    {
        $search    = trim($request->get('employee_name', ''));
        $startDate = $request->get('start_date') ?: null;
        $endDate   = $request->get('end_date') ?: null;

        $fileName = 'attendance_' .
            ($startDate ?: 'all') . '_to_' . ($endDate ?: 'all') . '.xlsx';

        return Excel::download(
            new AttendanceExport($search, $startDate, $endDate),
            $fileName
        );
    }
}
