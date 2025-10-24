<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function checkIn()
    {
        $today = now()->format('Y-m-d');
        $attendance = Attendance::where('employee_id', auth()->id())
            ->whereDate('created_at', $today)
            ->first();

        if ($attendance) {
            return back()->with('error', 'Already checked in today.');
        }

        Attendance::create([
            'employee_id' => auth()->id(),
            'check_in' => now(),
        ]);

        return back()->with('success', 'Checked in successfully.');
    }

    public function checkOut()
    {
        $today = now()->format('Y-m-d');
        $attendance = Attendance::where('employee_id', auth()->id())
            ->whereDate('created_at', $today)
            ->first();

        if (!$attendance) {
            return back()->with('error', 'You have not checked in yet.');
        }

        if ($attendance->check_out) {
            return back()->with('error', 'Already checked out today.');
        }

        $attendance->update(['check_out' => now()]);

        return back()->with('success', 'Checked out successfully.');
    }
}

