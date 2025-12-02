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
    $tz    = 'Africa/Nairobi';
    $today = Carbon::now($tz)->toDateString();

    // Which card is active? all | present | absent
    $card = $request->get('card', 'all');

    // Search text box
    $search = trim($request->get('employee_name', ''));

    // Date filters (blank by default)
    $startDateRaw = $request->get('start_date');
    $endDateRaw   = $request->get('end_date');

    // parse dates safely or null
    $startDate = null;
    $endDate   = null;
    try {
        if ($startDateRaw) {
            $startDate = Carbon::createFromFormat('Y-m-d', $startDateRaw, $tz)->startOfDay();
        }
        if ($endDateRaw) {
            $endDate = Carbon::createFromFormat('Y-m-d', $endDateRaw, $tz)->endOfDay();
        }
    } catch (\Exception $e) {
        // invalid date format — ignore and treat as null
        $startDate = null;
        $endDate = null;
    }

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

    // 2) PRESENT TODAY OR DATE RANGE
    elseif ($card === 'present') {
        $viewMode     = 'present';
        $sectionTitle = 'Employees Present';

        $attendanceQuery = Attendance::with('employee')
            ->whereNotNull('check_in')
            ->whereIn('status', ['present', 'permission']);

        // apply date filters if provided; otherwise default to today
        if ($startDate && $endDate) {
            $attendanceQuery->whereBetween('created_at', [
                $startDate->toDateTimeString(),
                $endDate->toDateTimeString(),
            ]);
        } elseif ($startDate && ! $endDate) {
            $attendanceQuery->where('created_at', '>=', $startDate->toDateTimeString());
        } elseif (! $startDate && $endDate) {
            $attendanceQuery->where('created_at', '<=', $endDate->toDateTimeString());
        } else {
            // no date filters provided -> show records for today only
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

        // compute late & overtime
        foreach ($attendances as $att) {
            $created = Carbon::parse($att->created_at)->setTimezone($tz);
            $date    = $created->toDateString();

            $officeStart = Carbon::parse($date . ' 08:00:00', $tz);
            $officeEnd   = Carbon::parse($date . ' 17:00:00', $tz);

            $lateMinutes     = 0;
            $overtimeMinutes = 0;

            if ($att->check_in) {
                // check_in might be stored as "H:i:s" or as full datetime; handle both
                $checkInRaw = $att->check_in;
                $checkInStr = (strpos($checkInRaw, ' ') !== false) ? $checkInRaw : ($date . ' ' . $checkInRaw);
                try {
                    $checkIn = Carbon::parse($checkInStr, $tz);
                    if ($checkIn->gt($officeStart)) {
                        $lateMinutes = $checkIn->diffInMinutes($officeStart);
                    }
                } catch (\Exception $e) {
                    $lateMinutes = 0;
                }
            }

            if ($att->check_out) {
                $checkOutRaw = $att->check_out;
                $checkOutStr = (strpos($checkOutRaw, ' ') !== false) ? $checkOutRaw : ($date . ' ' . $checkOutRaw);
                try {
                    $checkOut = Carbon::parse($checkOutStr, $tz);
                    if ($checkOut->gt($officeEnd)) {
                        $overtimeMinutes = $checkOut->diffInMinutes($officeEnd);
                    }
                } catch (\Exception $e) {
                    $overtimeMinutes = 0;
                }
            }

            $att->late_minutes     = $lateMinutes;
            $att->overtime_minutes = $overtimeMinutes;
        }
    }

    // 3) ABSENT (for chosen date or today)
    elseif ($card === 'absent') {
        $viewMode = 'absent';

        // use startDate if any, otherwise today
        $filterDate = $startDate ? $startDate->toDateString() : $today;

        $sectionTitle = 'Employees Absent on ' . Carbon::parse($filterDate)->format('d M Y');

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
        'startDate'       => $startDateRaw,
        'endDate'         => $endDateRaw,
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
