<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'department',
        'position',
        'employee_number',
        'role', // 'admin' or 'employee'
    ];

    /**
     * An employee can have many attendance records.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }
}



