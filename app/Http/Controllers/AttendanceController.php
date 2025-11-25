<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected string $tz = 'Africa/Nairobi';

    /**
     * Employee checks in.
     */
    public function checkIn(Request $request)
{
    $employee = auth()->user();
    $tz       = 'Africa/Nairobi';
    $today    = Carbon\Carbon::now($tz)->toDateString();

    // Check if employee already checked in today
    $existing = Attendance::where('employee_id', $employee->id)
        ->whereDate('created_at', $today)
        ->first();

    if ($existing) {
        return back()->with('error', 'You have already checked in today.');
    }

    // Correct timestamp creation
    $now = Carbon\Carbon::now($tz);

    Attendance::create([
        'employee_id' => $employee->id,
        'check_in'    => $now->format('H:i:s'),
        'status'      => 'present',
        'created_at'  => $now,   // FULL TIMESTAMP
        'updated_at'  => $now    // FULL TIMESTAMP
    ]);

    return back()->with('success', 'Check-in successful.');
}


    /**
     * Employee checks out.
     */
   public function checkOut(Request $request)
{
    $employee = auth()->user();
    $tz       = 'Africa/Nairobi';
    $today    = Carbon\Carbon::now($tz)->toDateString();

    $attendance = Attendance::where('employee_id', $employee->id)
        ->whereDate('created_at', $today)
        ->first();

    if (!$attendance) {
        return back()->with('error', 'You have not checked in today.');
    }

    if ($attendance->check_out) {
        return back()->with('error', 'You have already checked out today.');
    }

    $now = Carbon\Carbon::now($tz);

    $attendance->update([
        'check_out'  => $now->format('H:i:s'),
        'updated_at' => $now   // FULL TIMESTAMP
    ]);

    return back()->with('success', 'Check-out successful.');
}

}
