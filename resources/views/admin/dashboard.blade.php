@extends('layouts.app')

@section('content')
<div class="container mt-4">
    {{-- Flash messages --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <h3 class="mb-4 text-center">Admin Dashboard</h3>

    {{-- TOP CARDS --}}
    <div class="row mb-4 text-center">
        {{-- Total Employees --}}
        <div class="col-md-4 mb-3">
            <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['card' => 'all'])) }}"
               class="text-decoration-none">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">Total Employees</h6>
                        <h2 class="mb-0">{{ $totalEmployees }}</h2>
                    </div>
                </div>
            </a>
        </div>

        {{-- Present Today --}}
        <div class="col-md-4 mb-3">
            <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['card' => 'present'])) }}"
               class="text-decoration-none">
                <div class="card shadow-sm border-0 bg-success text-white">
                    <div class="card-body">
                        <h6 class="mb-1">Present Today (incl. Permission)</h6>
                        <h2 class="mb-0">{{ $presentToday }}</h2>
                    </div>
                </div>
            </a>
        </div>

        {{-- Absent Today --}}
        <div class="col-md-4 mb-3">
            <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['card' => 'absent'])) }}"
               class="text-decoration-none">
                <div class="card shadow-sm border-0 bg-danger text-white">
                    <div class="card-body">
                        <h6 class="mb-1">Absent Today</h6>
                        <h2 class="mb-0">{{ $absentToday }}</h2>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- FILTERS --}}
    <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-2 align-items-end mb-3">
        <input type="hidden" name="card" value="{{ $card }}">

        <div class="col-md-4">
            <label class="form-label">Search Employee</label>
            <input type="text" name="employee_name" class="form-control"
                   placeholder="Name, email, department, position"
                   value="{{ $employeeName }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search"></i> Search
            </button>
            <a href="{{ route('admin.dashboard', ['card' => $card]) }}" class="btn btn-outline-secondary"
               title="Reset filters">
                <i class="bi bi-arrow-clockwise"></i>
            </a>
        </div>
    </form>

    {{-- ACTION BUTTONS --}}
    <div class="d-flex justify-content-between mb-3">
        <a href="{{ route('employees.create') }}" class="btn btn-success">
            <i class="bi bi-person-plus"></i> Add New Employee
        </a>

        {{-- Export only when viewing attendance --}}
        @if ($viewMode === 'attendance')
            <a href="{{ route('admin.export', request()->query()) }}" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-excel"></i> Export to Excel
            </a>
        @endif
    </div>

    {{-- MAIN TABLE --}}
    <div class="card shadow-sm">
        <div class="card-header">
            <strong>{{ $sectionTitle }}</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        @if ($viewMode === 'attendance')
                            <tr>
                                <th>#</th>
                                <th>Employee Name</th>
                                <th>Department</th>
                                <th>Date</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Late (min)</th>
                                <th>Overtime (min)</th>
                                <th>Status</th>
                            </tr>
                        @else
                            <tr>
                                <th>#</th>
                                <th>Employee Name</th>
                                <th>Department</th>
                                <th>Email</th>
                                <th>Position</th>
                            </tr>
                        @endif
                    </thead>
                    <tbody>
                        @if ($viewMode === 'attendance')
                            @forelse ($attendances as $index => $attendance)
                                <tr>
                                    <td>{{ $attendances->firstItem() + $index }}</td>
                                    <td>{{ $attendance->employee->name ?? 'N/A' }}</td>
                                    <td>{{ $attendance->employee->department ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($attendance->created_at)->format('d M Y') }}</td>
                                    <td>{{ $attendance->check_in ?? '-' }}</td>
                                    <td>{{ $attendance->check_out ?? '-' }}</td>
                                    <td>{{ $attendance->late_minutes ?? '-' }}</td>
                                    <td>{{ $attendance->overtime_minutes ?? '-' }}</td>
                                    <td>
                                        @php
                                            $status = strtolower($attendance->status ?? 'present');
                                        @endphp
                                        @if ($status === 'present')
                                            <span class="badge bg-success">Present</span>
                                        @elseif ($status === 'permission')
                                            <span class="badge bg-warning text-dark">Permission</span>
                                        @else
                                            <span class="badge bg-danger">Absent</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-3">
                                        No attendance records found.
                                    </td>
                                </tr>
                            @endforelse
                        @else
                            @forelse ($employees as $index => $emp)
                                <tr>
                                    <td>{{ $employees->firstItem() + $index }}</td>
                                    <td>{{ $emp->name }}</td>
                                    <td>{{ $emp->department }}</td>
                                    <td>{{ $emp->email }}</td>
                                    <td>{{ $emp->position }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        No employees found for this criteria.
                                    </td>
                                </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if ($viewMode === 'attendance' && $attendances)
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Showing {{ $attendances->firstItem() }} to {{ $attendances->lastItem() }}
                        of {{ $attendances->total() }} results
                    </small>
                    <div>
                        {{ $attendances->onEachSide(1)->links() }}
                    </div>
                </div>
            </div>
        @elseif ($viewMode === 'absent' && $employees)
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }}
                        of {{ $employees->total() }} results
                    </small>
                    <div>
                        {{ $employees->onEachSide(1)->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
