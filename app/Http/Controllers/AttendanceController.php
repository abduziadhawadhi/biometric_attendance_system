<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // Default timezone for the whole controller
    protected string $tz = 'Africa/Nairobi';

    /**
     * Employee checks in.
     */
    public function checkIn(Request $request)
    {
        $employee = Auth::user();
        $now      = Carbon::now($this->tz);           // full timestamp
        $today    = $now->toDateString();             // YYYY-MM-DD

        // Has this employee already checked in today?
        $existing = Attendance::where('employee_id', $employee->id)
            ->whereDate('created_at', $today)
            ->first();

        if ($existing) {
            return back()->with('error', 'You have already checked in today.');
        }

        // Store FULL timestamp in check_in (matches PostgreSQL column type)
        Attendance::create([
            'employee_id' => $employee->id,
            'check_in'    => $now,        // timestamp
            'status'      => 'present',
            // created_at / updated_at are filled automatically by Eloquent
        ]);

        return back()->with('success', 'Check-in successful.');
    }

    /**
     * Employee checks out.
     */
    public function checkOut(Request $request)
    {
        $employee = Auth::user();
        $now      = Carbon::now($this->tz);
        $today    = $now->toDateString();

        // Find today’s attendance record
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('created_at', $today)
            ->first();

        if (! $attendance) {
            return back()->with('error', 'You have not checked in today.');
        }

        if ($attendance->check_out) {
            return back()->with('error', 'You have already checked out today.');
        }

        // Store FULL timestamp in check_out too
        $attendance->update([
            'check_out' => $now,
        ]);

        return back()->with('success', 'Check-out successful.');
    }
}
