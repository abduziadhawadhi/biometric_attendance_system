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
     * Employee Dashboard
     * - shows his/her own attendance
     * - buttons to Check In / Check Out manually
     */
    public function dashboard()
    {
        $tz       = 'Africa/Nairobi';
        $employee = auth()->user();

        if (!$employee instanceof Employee) {
            abort(403, 'Only employees can access this page.');
        }

        $today = Carbon::now($tz)->toDateString();

        // Today's attendance record (if any)
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'asc')
            ->first();

        // For the table: last 30 attendance records
        $attendances = Attendance::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get();

        // Compute late & overtime per record
        foreach ($attendances as $att) {
            $created = Carbon::parse($att->created_at, $tz);
            $date    = $created->toDateString();

            $officeStart = Carbon::parse($date . ' 08:00:00', $tz);
            $officeEnd   = Carbon::parse($date . ' 17:00:00', $tz);

            $lateMinutes     = 0;
            $overtimeMinutes = 0;

            if ($att->check_in) {
                $checkIn = Carbon::parse($date . ' ' . $att->check_in, $tz);
                if ($checkIn->gt($officeStart)) {
                    $lateMinutes = $checkIn->diffInMinutes($officeStart);
                }
            }

            if ($att->check_out) {
                $checkOut = Carbon::parse($date . ' ' . $att->check_out, $tz);
                if ($checkOut->gt($officeEnd)) {
                    $overtimeMinutes = $checkOut->diffInMinutes($officeEnd);
                }
            }

            $att->late_minutes     = $lateMinutes;
            $att->overtime_minutes = $overtimeMinutes;
        }

        // Button visibility
        $canCheckIn  = !$todayAttendance || !$todayAttendance->check_in;
        $canCheckOut = $todayAttendance && $todayAttendance->check_in && !$todayAttendance->check_out;

        return view('employee.dashboard', [
            'employee'        => $employee,
            'attendances'     => $attendances,
            'todayAttendance' => $todayAttendance,
            'canCheckIn'      => $canCheckIn,
            'canCheckOut'     => $canCheckOut,
        ]);
    }

    /**
     * Show Add Employee form (Admin)
     */
    public function create()
    {
        return view('admin.add_employee');
    }

    /**
     * Store new Employee (Admin)
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
            'role'       => 'employee', // make sure this column exists
        ]);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Employee added successfully!');
    }
}
