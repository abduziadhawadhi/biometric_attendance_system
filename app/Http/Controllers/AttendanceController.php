<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function checkIn()
    {
        $employee = auth()->user();
        $tz = "Africa/Nairobi";

        // check if already checked in today
        $today = Carbon::now($tz)->toDateString();
        $exists = Attendance::where('employee_id', $employee->id)
            ->whereDate('created_at', $today)
            ->first();

        if ($exists) {
            return back()->with('error', 'You already checked in today.');
        }

        Attendance::create([
            'employee_id' => $employee->id,
            'check_in'    => Carbon::now($tz)->format('H:i:s'), // only time
            'status'      => 'present'
        ]);

        return back()->with('success', 'Check-in successful!');
    }

    public function checkOut()
    {
        $employee = auth()->user();
        $tz = "Africa/Nairobi";

        $today = Carbon::now($tz)->toDateString();
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('created_at', $today)
            ->first();

        if (!$attendance) {
            return back()->with('error', 'You did not check in today.');
        }

        if ($attendance->check_out) {
            return back()->with('error', 'You already checked out today.');
        }

        $attendance->update([
            'check_out' => Carbon::now($tz)->format('H:i:s') // only time
        ]);

        return back()->with('success', 'Check-out successful!');
    }
}
