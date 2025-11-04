<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Show form to create a new employee.
     */
    public function create()
    {
        return view('employees.create');
    }

    /**
     * Store a newly created employee in the database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'position' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        Employee::create([
            'name' => $validated['name'],
            'department' => $validated['department'],
            'email' => $validated['email'],
            'position' => $validated['position'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Employee added successfully!');
    }

    /**
     * Employee dashboard – shows only their own attendance records.
     */
    public function dashboard()
    {
        $employee = Auth::user();

        $attendances = Attendance::where('employee_id', $employee->id)
            ->orderBy('check_in', 'desc')
            ->paginate(10);

        return view('employee.dashboard', compact('employee', 'attendances'));
    }
}

