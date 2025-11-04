@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3 class="text-center text-primary mb-4">Admin Dashboard</h3>

    <!-- Dashboard Stats -->
    <div class="row text-center mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-3 bg-light">
                <h5>Total Employees</h5>
                <h2>{{ $totalEmployees }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-3 bg-success text-white">
                <h5>Present Today</h5>
                <h2>{{ $presentToday }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-3 bg-danger text-white">
                <h5>Absent Today</h5>
                <h2>{{ $absentToday }}</h2>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="employee_name" class="form-control" placeholder="Search Employee" value="{{ $employeeName }}">
        </div>
        <div class="col-md-3">
            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
        </div>
        <div class="col-md-3">
            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
        </div>
        <div class="col-md-2 text-end">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <!-- Export Button -->
    <div class="text-end mb-3">
        <a href="" class="btn btn-success">
            <i class="bi bi-file-earmark-excel"></i> Export to Excel
        </a>
    </div>

    <!-- Attendance Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <a href="" class="btn btn-success">
            <i class="bi bi-file-earmark-excel"></i> Export to Excel
        </a>
            <h5>All Employees</h5>
            <table class="table table-hover mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>Employee Name</th>
                        <th>Department</th>
                        <th>Email</th>
                        <th>Position</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($allEmployees as $employee)
                        <tr>
                            <td>{{ $employee->name ?? 'N/A' }}</td>
                            <td>{{ $employee->department ?? 'N/A' }}</td>
                            <td>{{ $employee->email }}</td>
                            <td>{{ $employee->position }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No attendance records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection


