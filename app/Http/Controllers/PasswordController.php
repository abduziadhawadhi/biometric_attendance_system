<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function showChangeForm()
    {
        return view('employee.change_password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $employee = auth()->user();

        if (!Hash::check($request->current_password, $employee->password)) {
            return back()->withErrors(['current_password' => 'Your current password is incorrect']);
        }

        $employee->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->route('employee.dashboard')->with('success', 'Password changed successfully!');
    }
}
