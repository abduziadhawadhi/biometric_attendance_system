@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h3 class="text-center text-primary mb-4 fw-bold">
        Admin Dashboard
    </h3>

    <!-- Dashboard Cards -->
    <div class="row text-center mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm p-3 border-0 bg-light">
                <h6 class="text-muted">Total Employees</h6>
                <h2 class="fw-bold text-primary">{{ $totalEmployees }}</h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm p-3 border-0 bg-success text-white">
                <h6>Present Today</h6>
                <h2 class="fw-bold">{{ $presentToday }}</h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm p-3 border-0 bg-danger text-white">
                <h6>Absent Today</h6>
                <h2 class="fw-bold">{{ $absentToday }}</h2>
            </div>
        </div>
    </div>

    <!-- Search / Filter Form -->
    <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="employee_name" class="form-control"
                   placeholder="Search Employee"
                   value="{{ request('employee_name') }}">
        </div>

        <div class="col-md-3">
            <input type="date" name="start_date" class="form-control"
                   value="{{ request('start_date') }}">
        </div>

        <div class="col-md-3">
            <input type="date" name="end_date" class="form-control"
                   value="{{ request('end_date') }}">
        </div>

        <div class="col-md-2 d-flex">
            <button type="submit" class="btn btn-primary w-100 me-2">
                <i class="bi bi-search"></i> Search
            </button>
        </div>
    </form>

    <!-- Reset Filter Button -->
    <div class="text-end mb-3">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
            <i class="bi bi-x-circle"></i> Reset Filters
        </a>
    </div>

    <!-- Add Employee Button -->
    <div class="text-start mb-3">
        <a href="{{ route('employees.create') }}" class="btn btn-success">
            <i class="bi bi-person-plus"></i> Add New Employee
        </a>
    </div>

    <!-- Attendance Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold">Attendance Records</h5>

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
                            <td>{{ \Carbon\Carbon::parse($attendance->created_at)->format('d M Y') }}</td>
                            <td>{{ $attendance->check_in ?? '-' }}</td>
                            <td>{{ $attendance->check_out ?? '-' }}</td>
                            <td>
                                <span class="badge 
                                    @if($attendance->status == 'present') bg-success
                                    @elseif($attendance->status == 'absent') bg-danger
                                    @else bg-warning @endif">
                                    {{ ucfirst($attendance->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                No attendance records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {!! $attendances->links() !!}
            </div>

        </div>
    </div>
</div>
@endsection


