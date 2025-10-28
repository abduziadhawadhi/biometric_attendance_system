<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable
{
    use Notifiable;

    protected $table = 'employees'; // make sure this matches your DB table name

    protected $fillable = [
        'name',
        'email',
        'password',
        'department',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}




