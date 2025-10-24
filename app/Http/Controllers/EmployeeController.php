<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;

class EmployeeController extends Controller
{
    public function index()
    {
        $records = Attendance::where('employee_id', auth()->id())
                             ->orderBy('created_at', 'desc')
                             ->take(10)
                             ->get();

        return view('employee.dashboard', compact('records'));
    }
}
