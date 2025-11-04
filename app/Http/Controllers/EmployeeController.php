<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    // Show employee dashboard
    public function dashboard()
    {
        $employee = auth()->user();
        $attendances = $employee->attendances()->latest()->get();

        return view('employee.dashboard', compact('employee', 'attendances'));
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
}


