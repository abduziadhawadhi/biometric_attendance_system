@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Welcome Admin, {{ auth()->user()->name }}</h3>
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card p-4 text-center shadow-sm">
                <h6>Total Employees</h6>
                <h3>{{ $totalEmployees }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4 text-center shadow-sm">
                <h6>Today's Attendance</h6>
                <h3>{{ $todayRecords }}</h3>
            </div>
        </div>
    </div>

    <h5 class="mt-5">Recent Attendance Records</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentAttendance as $att)
            <tr>
                <td>{{ $att->employee->name }}</td>
                <td>{{ $att->check_in }}</td>
                <td>{{ $att->check_out ?? '-' }}</td>
                <td>{{ $att->created_at->format('d M Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
