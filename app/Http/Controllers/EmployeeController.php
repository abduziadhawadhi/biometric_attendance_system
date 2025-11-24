<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    /**
     * Employee Dashboard – list own attendance, compute late & overtime.
     */
    public function dashboard()
    {
        $employee = auth()->user();

        if (!$employee instanceof Employee) {
            abort(403, 'Only employees can access this page.');
        }

        $tz    = 'Africa/Nairobi';
        $today = Carbon::now($tz)->toDateString();

        // Used to enable/disable Check In / Check Out buttons
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('created_at', $today)
            ->first();

        // All attendance records for this employee (latest first)
        $attendances = Attendance::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Compute Late & Overtime (minutes) for each record
        foreach ($attendances as $att) {
            $created = Carbon::parse($att->created_at)->setTimezone($tz);
            $date    = $created->toDateString();              // e.g. 2025-11-15

            $officeStart = Carbon::parse($date . ' 08:00:00', $tz);
            $officeEnd   = Carbon::parse($date . ' 17:00:00', $tz);

            $lateMinutes     = 0;
            $overtimeMinutes = 0;

            if (!empty($att->check_in)) {
                // combine date (Y-m-d) + time (H:i:s) – NO double date
                $checkIn = Carbon::parse($date . ' ' . $att->check_in, $tz);
                if ($checkIn->gt($officeStart)) {
                    $lateMinutes = $checkIn->diffInMinutes($officeStart);
                }
            }

            if (!empty($att->check_out)) {
                $checkOut = Carbon::parse($date . ' ' . $att->check_out, $tz);
                if ($checkOut->gt($officeEnd)) {
                    $overtimeMinutes = $checkOut->diffInMinutes($officeEnd);
                }
            }

            // Extra properties only for display in the blade
            $att->late_minutes     = $lateMinutes;
            $att->overtime_minutes = $overtimeMinutes;
        }

        return view('employee.dashboard', [
            'employee'         => $employee,
            'todayAttendance'  => $todayAttendance,
            'attendances'      => $attendances,
        ]);
    }

    /**
     * Show Add Employee form (for admin).
     */
    public function create()
    {
        return view('admin.add_employee');
    }

    /**
     * Store new employee (created by admin).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'email'      => 'required|email|unique:employees,email',
            'position'   => 'required|string|max:255',
            'password'   => 'required|string|min:6',
        ]);

        Employee::create([
            'name'       => $validated['name'],
            'department' => $validated['department'],
            'email'      => $validated['email'],
            'position'   => $validated['position'],
            'password'   => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Employee added successfully!');
    }
}
