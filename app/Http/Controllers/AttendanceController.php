<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Employee Check-In
     */
    public function checkIn(Request $request)
    {
        $employeeId = Auth::id();
        $today = Carbon::today();

        // Check if already checked in today
        $existing = Attendance::where('employee_id', $employeeId)
            ->whereDate('check_in', $today)
            ->first();

        if ($existing) {
            return back()->with('error', 'You have already checked in today.');
        }

        Attendance::create([
            'employee_id' => $employeeId,
            'check_in' => Carbon::now(),
            'status' => 'present',
        ]);

        return back()->with('success', 'Check-in successful!');
    }

    /**
     * Employee Check-Out
     */
    public function checkOut(Request $request)
    {
        $employeeId = Auth::id();
        $today = Carbon::today();

        $attendance = Attendance::where('employee_id', $employeeId)
            ->whereDate('check_in', $today)
            ->first();

        if (!$attendance) {
            return back()->with('error', 'You have not checked in today.');
        }

        if ($attendance->check_out) {
            return back()->with('error', 'You have already checked out today.');
        }

        $attendance->update([
            'check_out' => Carbon::now(),
        ]);

        return back()->with('success', 'Check-out successful!');
    }
}



