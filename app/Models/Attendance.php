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
    ];

    protected $appends = ['attendance_date'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // Convert created_at to Attendance Date
    public function getAttendanceDateAttribute()
    {
        return Carbon::parse($this->created_at)->format('Y-m-d');
    }
}



