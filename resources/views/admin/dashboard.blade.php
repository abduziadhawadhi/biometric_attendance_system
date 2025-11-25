@extends('layouts.app')

@section('content')
<div class="container py-4">

    <h2 class="text-center mb-4">Admin Dashboard</h2>

    {{-- Top summary cards --}}
    <div class="row g-3 mb-4">

        {{-- Total Employees --}}
        <div class="col-md-4">
            <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['card' => 'all'])) }}"
               class="text-decoration-none">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted text-uppercase mb-2">Total Employees</h6>
                        <div class="display-6 fw-bold">{{ $totalEmployees }}</div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Present Today --}}
        <div class="col-md-4">
            <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['card' => 'present'])) }}"
               class="text-decoration-none">
                <div class="card shadow-sm border-0 h-100 bg-success text-white">
                    <div class="card-body text-center">
                        <h6 class="text-uppercase mb-2">Present Today</h6>
                        <div class="display-6 fw-bold">{{ $presentToday }}</div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Absent Today --}}
        <div class="col-md-4">
            <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['card' => 'absent'])) }}"
               class="text-decoration-none">
                <div class="card shadow-sm border-0 h-100 bg-danger text-white">
                    <div class="card-body text-center">
                        <h6 class="text-uppercase mb-2">Absent Today</h6>
                        <div class="display-6 fw-bold">{{ $absentToday }}</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Filters row --}}
    <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-2 align-items-center mb-3">
        {{-- keep current card when searching --}}
        <input type="hidden" name="card" value="{{ $card }}"/>

        <div class="col-md-6">
            <input
                type="text"
                name="employee_name"
                class="form-control"
                placeholder="Search Employee (name, email, department, position)"
                value="{{ $employeeName }}"
            >
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search"></i> Search
            </button>
        </div>

        <div class="col-md-2">
            <a href="{{ route('admin.dashboard', ['card' => 'all']) }}" class="btn btn-outline-secondary w-100">
                <i class="bi bi-arrow-repeat"></i> Reset
            </a>
        </div>

        <div class="col-md-2 text-end">
            <a href="{{ route('admin.export', request()->query()) }}" class="btn btn-success w-100">
                <i class="bi bi-file-earmark-excel"></i> Export to Excel
            </a>
        </div>
    </form>

    {{-- Content section --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">{{ $sectionTitle }}</h5>

            {{-- EMPLOYEES LIST (for card = all / absent) --}}
            @if($viewMode === 'employees')
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Employee Name</th>
                                <th>Department</th>
                                <th>Email</th>
                                <th>Position</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $index => $emp)
                                <tr>
                                    <td>{{ $employees->firstItem() + $index }}</td>
                                    <td>{{ $emp->name }}</td>
                                    <td>{{ $emp->department }}</td>
                                    <td>{{ $emp->email }}</td>
                                    <td>{{ $emp->position }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        No employees found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center">
                    {{ $employees->links() }}
                </div>

            {{-- ATTENDANCE LIST (for card = present / default attendance view) --}}
            @else
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
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
                        </thead>
                        <tbody>
                            @forelse($attendances as $index => $att)
                                <tr>
                                    <td>{{ $attendances->firstItem() + $index }}</td>
                                    <td>{{ $att->employee->name ?? 'N/A' }}</td>
                                    <td>{{ $att->employee->department ?? 'N/A' }}</td>
                                    <td>{{ $att->created_at->format('d M Y') }}</td>
                                    <td>{{ $att->check_in ?? '-' }}</td>
                                    <td>{{ $att->check_out ?? '-' }}</td>
                                    <td>{{ $att->late_minutes ?? 0 }}</td>
                                    <td>{{ $att->overtime_minutes ?? 0 }}</td>
                                    <td>
                                        @php
                                            $status = strtolower($att->status ?? '');
                                        @endphp

                                        @if($status === 'present')
                                            <span class="badge bg-success">Present</span>
                                        @elseif($status === 'permission')
                                            <span class="badge bg-warning text-dark">Permission</span>
                                        @elseif($status === 'absent')
                                            <span class="badge bg-danger">Absent</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($status ?: 'N/A') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        No attendance records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
