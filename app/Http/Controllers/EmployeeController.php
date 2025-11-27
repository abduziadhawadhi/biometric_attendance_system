<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    protected string $tz = 'Africa/Nairobi';

    /**
     * Employee dashboard:
     * - list own attendance records
     * - compute late & overtime
     * - provide today's record to disable buttons
     */
    public function dashboard()
    {
        /** @var \App\Models\Employee $employee */
        $employee = Auth::user();

        // Get this employee's attendance history
        $attendances = Attendance::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Compute late & overtime for each record
        foreach ($attendances as $att) {
            // created_at is a full timestamp
            $created = Carbon::parse($att->created_at)->setTimezone($this->tz);
            $date    = $created->toDateString(); // yyyy-mm-dd

            $officeStart = Carbon::parse($date . ' 08:00:00', $this->tz);
            $officeEnd   = Carbon::parse($date . ' 17:00:00', $this->tz);

            $lateMinutes     = 0;
            $overtimeMinutes = 0;

            // check_in & check_out are FULL timestamps in DB
            if ($att->check_in) {
                $checkIn = Carbon::parse($att->check_in)->setTimezone($this->tz);
                if ($checkIn->gt($officeStart)) {
                    $lateMinutes = $checkIn->diffInMinutes($officeStart);
                }
            }

            if ($att->check_out) {
                $checkOut = Carbon::parse($att->check_out)->setTimezone($this->tz);
                if ($checkOut->gt($officeEnd)) {
                    $overtimeMinutes = $checkOut->diffInMinutes($officeEnd);
                }
            }

            // Attach for Blade view
            $att->late_minutes     = $lateMinutes;
            $att->overtime_minutes = $overtimeMinutes;
        }

        // Today's attendance (used to enable/disable check-in/out buttons)
        $today = Carbon::now($this->tz)->toDateString();

        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('created_at', $today)
            ->first();

        return view('employee.dashboard', [
            'employee'         => $employee,
            'attendances'      => $attendances,
            'todayAttendance'  => $todayAttendance,
        ]);
    }

    /**
     * Show add-employee form (used from admin area).
     */
    public function create()
    {
        return view('admin.add_employee');
    }

    /**
     * Store new employee (called from admin).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'email'      => 'required|email|unique:employees,email',
            'position'   => 'required|string|max:255',
            'password'   => 'required|string|min:6',
            'role'       => 'required|in:admin,employee', // if you use roles on Employee
        ]);

        Employee::create([
            'name'       => $validated['name'],
            'department' => $validated['department'],
            'email'      => $validated['email'],
            'position'   => $validated['position'],
            'role'       => $validated['role'],
            'password'   => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Employee added successfully!');
    }
}
