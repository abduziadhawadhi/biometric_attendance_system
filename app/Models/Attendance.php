<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'status', // e.g. "present", "absent", "late"
    ];

    /**
     * Each attendance record belongs to one employee.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}



