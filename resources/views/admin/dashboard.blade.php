@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <!-- <h3 class="text-center text-primary mb-4 fw-bold">Admin Dashboard</h3> -->

    <!-- Dashboard Stats -->
    <div class="row text-center mb-4">
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 p-3 bg-light">
                <h5>Total Employees</h5>
                <h2 class="text-primary">{{ $totalEmployees }}</h2>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 p-3 bg-success text-white">
                <h5>Present Today</h5>
                <h2>{{ $presentToday }}</h2>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 p-3 bg-danger text-white">
                <h5>Absent Today</h5>
                <h2>{{ $absentToday }}</h2>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3 align-items-center mb-3">
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
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search"></i> Search
            </button>
        </div>
    </form>

    <!-- Reset Filters
    <div class="text-end mb-3">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-counterclockwise"></i> Reset Filters
        </a>
    </div> -->

    <!-- Add Employee + Export Buttons -->
    <div class="d-flex justify-content-start mb-3 gap-2">
        <!-- <a href="{{ route('employees.create') }}" class="btn btn-success"> -->
            <!-- <i class="bi bi-person-plus"></i> Add New Employee -->
        <!-- </a> -->
        <a href="{{ route('admin.export', request()->all()) }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel"></i> Export to Excel
        </a>
    </div>

    <!-- Attendance Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="text-primary fw-bold">Attendance Records</h5>
            <table class="table table-hover mt-3 align-middle text-center">
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
                                @if ($attendance->status === 'present')
                                    <span class="badge bg-success">Present</span>
                                @else
                                    <span class="badge bg-danger">Absent</span>
                                @endif
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
            <div class="d-flex justify-content-center mt-3">
                <style>
                    .pagination {
                        font-size: 0.85rem;
                        margin: 0;
                    }

                    .pagination .page-link {
                        padding: 0.25rem 0.6rem;
                        border-radius: 6px;
                    }

                    .pagination .page-item.active .page-link {
                        background-color: #007bff;
                        border-color: #007bff;
                    }

                    .pagination .page-link:hover {
                        background-color: #f1f1f1;
                        color: #007bff;
                    }
                </style>
                {{ $attendances->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <footer class="text-center text-muted mt-4">
        <small>© 2025 Workers Compensation Fund (WCF). All rights reserved.</small>
    </footer>
</div>
@endsection
