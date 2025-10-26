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
        <a href="{{ route('admin.export', request()->all()) }}" class="btn btn-success">
            <i class="bi bi-file-earmark-excel"></i> Export to Excel
        </a>
    </div>

    <!-- Attendance Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h5>Attendance Records</h5>
            <table class="table table-hover mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>Employee Name</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->employee->name ?? 'N/A' }}</td>
                            <td>{{ $attendance->employee->department ?? 'N/A' }}</td>
                            <td>{{ $attendance->date }}</td>
                            <td>{{ $attendance->check_in }}</td>
                            <td>{{ $attendance->check_out }}</td>
                            <td>{{ ucfirst($attendance->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No attendance records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {{ $attendances->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

