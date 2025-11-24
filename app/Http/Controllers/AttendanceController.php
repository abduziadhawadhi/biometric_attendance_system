<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    /**
     * Employee manual CHECK-IN
     */
    public function checkIn(Request $request)
    {
        $employee = auth()->user();
        $tz       = 'Africa/Nairobi';

        if (!$employee || $employee->role !== 'employee') {
            return redirect()->route('login')->with('error', 'Unauthorized.');
        }

        $today = Carbon::now($tz)->toDateString();

        // Already checked in?
        $existing = Attendance::where('employee_id', $employee->id)
            ->whereDate('created_at', $today)
            ->first();

        if ($existing && $existing->check_in) {
            return back()->with('error', 'You already checked in today.');
        }

        // Create or update today's record
        if ($existing) {
            $existing->update([
                'check_in' => Carbon::now($tz)->format('H:i:s'),
                'status'   => 'present',
            ]);
        } else {
            Attendance::create([
                'employee_id' => $employee->id,
                'check_in'    => Carbon::now($tz)->format('H:i:s'),
                'status'      => 'present',
            ]);
        }

        return back()->with('success', 'Check-in successful!');
    }

    /**
     * Employee manual CHECK-OUT
     */
    public function checkOut(Request $request)
    {
        $employee = auth()->user();
        $tz       = 'Africa/Nairobi';

        if (!$employee || $employee->role !== 'employee') {
            return redirect()->route('login')->with('error', 'Unauthorized.');
        }

        $today = Carbon::now($tz)->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('created_at', $today)
            ->first();

        if (!$attendance || !$attendance->check_in) {
            return back()->with('error', 'You must check in first.');
        }

        if ($attendance->check_out) {
            return back()->with('error', 'You already checked out today.');
        }

        $attendance->update([
            'check_out' => Carbon::now($tz)->format('H:i:s'),
        ]);

        return back()->with('success', 'Check-out successful!');
    }
}
