<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'check_in',
        'check_out',
        'status',
        'late_minutes',
        'overtime_minutes',
    ];

    // Relationship
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // Get attendance date from created_at (we are using created_at as the date)
    public function getAttendanceDateAttribute()
    {
        return $this->created_at ? $this->created_at->toDateString() : null;
    }

    // compute late minutes (Nairobi timezone)
    public function computeLateAndOvertime()
    {
        $tz = 'Africa/Nairobi';
        $lateMinutes = null;
        $overtimeMinutes = null;

        if ($this->check_in) {
            $checkIn = Carbon::parse($this->check_in)->setTimezone($tz);
            $expected = Carbon::parse($this->created_at->toDateString() . ' 08:00:00', $tz);
            if ($checkIn->greaterThan($expected)) {
                $lateMinutes = $checkIn->diffInMinutes($expected);
            } else {
                $lateMinutes = 0;
            }
        }

        if ($this->check_out) {
            $checkOut = Carbon::parse($this->check_out)->setTimezone($tz);
            $expectedOut = Carbon::parse($this->created_at->toDateString() . ' 17:00:00', $tz);
            if ($checkOut->greaterThan($expectedOut)) {
                $overtimeMinutes = $checkOut->diffInMinutes($expectedOut);
            } else {
                $overtimeMinutes = 0;
            }
        }

        $this->late_minutes = $lateMinutes;
        $this->overtime_minutes = $overtimeMinutes;
        $this->save();
    }

    // Helper attributes
    public function getIsPresentAttribute()
    {
        return !is_null($this->check_in);
    }
}
