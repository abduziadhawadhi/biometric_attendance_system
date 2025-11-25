<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    protected $tz = 'Africa/Nairobi';

    // Show employee dashboard
    public function dashboard()
    {
        $employee = auth()->user();
        $today = Carbon::now($this->tz)->toDateString();

        // Today attendance (if exists)
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('created_at', $today)
            ->first();

        // Fetch user's attendance history
        $attendances = Attendance::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Compute late & overtime CORRECTLY
        foreach ($attendances as $att) {
            // Use ORIGINAL timestamp (NO double date construction)
            $created  = Carbon::parse($att->created_at)->setTimezone($this->tz);
            $dateOnly = $created->toDateString();

            $officeStart = Carbon::parse($dateOnly . ' 08:00:00', $this->tz);
            $officeEnd   = Carbon::parse($dateOnly . ' 17:00:00', $this->tz);

            $late = 0;
            $overtime = 0;

            // Late
            if ($att->check_in) {
                $checkIn = Carbon::parse($att->check_in, $this->tz);
                if ($checkIn->gt($officeStart)) {
                    $late = $checkIn->diffInMinutes($officeStart);
                }
            }

            // Overtime
            if ($att->check_out) {
                $checkOut = Carbon::parse($att->check_out, $this->tz);
                if ($checkOut->gt($officeEnd)) {
                    $overtime = $checkOut->diffInMinutes($officeEnd);
                }
            }

            $att->late_minutes = $late;
            $att->overtime_minutes = $overtime;
        }

        return view('employee.dashboard', compact('employee', 'attendances', 'todayAttendance'));
    }

    // Show add employee form
    public function create()
    {
        return view('admin.add_employee');
    }

    // Store new employee
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'department'=> 'required|string|max:255',
            'email'     => 'required|email|unique:employees,email',
            'position'  => 'required|string|max:255',
            'password'  => 'required|string|min:6',
        ]);

        Employee::create([
            'name'      => $validated['name'],
            'department'=> $validated['department'],
            'email'     => $validated['email'],
            'position'  => $validated['position'],
            'password'  => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Employee added successfully!');
    }
}
