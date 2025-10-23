<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    // ✅ Allow these fields to be mass assigned
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department',
        'position',
    ];

    // ✅ Hide sensitive fields when returning JSON
    protected $hidden = [
        'password',
    ];

    // ✅ Each employee can have many attendance records
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}

