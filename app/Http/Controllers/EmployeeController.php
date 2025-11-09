<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    // Employee Dashboard
    public function dashboard()
    {
        $employee = auth()->user();

    $attendances = $employee->attendances()->latest()->paginate(10);

    return view('employee.dashboard', compact('employee', 'attendances'));
    }

    // Show Add Employee Form
    public function create()
    {
        return view('admin.add_employee');
    }

    // Save New Employee
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:4',
        ]);

        // Create Login Account
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'employee', // Ensure 'role' column exists in users table
        ]);

        // Create Employee Profile
        Employee::create([
            'user_id'    => $user->id,
            'name'       => $validated['name'],
            'department' => $validated['department'],
        ]);

        return redirect()->route('admin.dashboard')->with('success', '✅ Employee added successfully!');
    }
}




