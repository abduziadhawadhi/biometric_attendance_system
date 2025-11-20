<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;
use Carbon\Carbon;
use Auth;

class PermissionController extends Controller
{
    public function create()
    {
        return view('employee.permission_request');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'=>'required|date',
            'reason'=>'required|string|max:1000'
        ]);

        $employee = Auth::user();

        Permission::create([
            'employee_id' => $employee->id,
            'date' => $request->date,
            'reason' => $request->reason,
            'status' => 'pending'
        ]);

        return redirect()->route('employee.dashboard')->with('success','Permission request submitted');
    }
}
