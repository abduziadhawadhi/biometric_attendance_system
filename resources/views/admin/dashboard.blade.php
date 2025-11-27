@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3 class="text-center mb-4">Admin Dashboard</h3>

    {{-- TOP CARDS --}}
    <div class="row text-center mb-4">
        {{-- Total employees --}}
        <div class="col-md-4 mb-3">
            <a href="{{ route('admin.dashboard', ['card' => 'all']) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 p-3 {{ $card === 'all' ? 'border-primary' : '' }}">
                    <h6 class="text-muted mb-1">TOTAL EMPLOYEES</h6>
                    <h2 class="fw-bold">{{ $totalEmployees }}</h2>
                </div>
            </a>
        </div>

        {{-- Present today --}}
        <div class="col-md-4 mb-3">
            <a href="{{ route('admin.dashboard', ['card' => 'present']) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 p-3 bg-success text-white {{ $card === 'present' ? 'border border-3 border-light' : '' }}">
                    <h6 class="mb-1 text-uppercase">PRESENT TODAY</h6>
                    <h2 class="fw-bold">{{ $presentToday }}</h2>
                </div>
            </a>
        </div>

        {{-- Absent today --}}
        <div class="col-md-4 mb-3">
            <a href="{{ route('admin.dashboard', ['card' => 'absent']) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 p-3 bg-danger text-white {{ $card === 'absent' ? 'border border-3 border-light' : '' }}">
                    <h6 class="mb-1 text-uppercase">ABSENT TODAY</h6>
                    <h2 class="fw-bold">{{ $absentToday }}</h2>
                </div>
            </a>
        </div>
    </div>

    {{-- FILTER FORM --}}
    <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-2 mb-3">
        <input type="hidden" name="card" value="{{ $card }}">

        <div class="col-md-4">
            <input
                type="text"
                name="employee_name"
                class="form-control"
                placeholder="Search Employee (name, email, department, position)"
                value="{{ old('employee_name', $employeeName ?? '') }}">
        </div>

        <div class="col-md-3">
            <input
                type="date"
                name="start_date"
                class="form-control"
                value="{{ $startDate }}">
        </div>

        <div class="col-md-3">
            <input
                type="date"
                name="end_date"
                class="form-control"
                value="{{ $endDate }}">
        </div>

        <div class="col-md-1 d-grid">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i> Search
            </button>
        </div>

        <div class="col-md-1 d-grid">
            <a href="{{ route('admin.dashboard', ['card' => $card]) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-counterclockwise"></i> Reset
            </a>
        </div>
    </form>

    {{-- EXPORT BUTTON --}}
    <div class="mb-3 text-end">
        <a href="{{ route('admin.export', request()->query()) }}" class="btn btn-success">
            <i class="bi bi-file-earmark-excel"></i> Export to Excel
        </a>
    </div>

    {{-- MAIN TABLE CARD --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">
                @if ($viewMode === 'employeesAll')
                    All Employees
                @elseif ($viewMode === 'presentAttendance')
                    Attendance Records (Present)
                @elseif ($viewMode === 'absentEmployees')
                    Employees Absent
                @else
                    List
                @endif
            </h5>

            {{-- MODE: ALL EMPLOYEES or ABSENT EMPLOYEES --}}
            @if ($viewMode === 'employeesAll' || $viewMode === 'absentEmployees')
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
                                    <td colspan="5" class="text-center text-muted">
                                        No employees found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($employees->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $employees->links() }}
                    </div>
                @endif
            @endif

            {{-- MODE: PRESENT ATTENDANCE (WITH TIME, LATE, OVERTIME) --}}
            @if ($viewMode === 'presentAttendance')
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Late (min)</th>
                                <th>Overtime (min)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($attendances as $index => $att)
                                <tr>
                                    <td>{{ $attendances->firstItem() + $index }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($att->created_at)->setTimezone('Africa/Nairobi')->format('d M Y') }}
                                    </td>
                                    <td>{{ $att->employee->name ?? 'N/A' }}</td>
                                    <td>{{ $att->employee->department ?? 'N/A' }}</td>
                                    <td>{{ $att->check_in ?? '-' }}</td>
                                    <td>{{ $att->check_out ?? '-' }}</td>
                                    <td>{{ $att->late_minutes }}</td>
                                    <td>{{ $att->overtime_minutes }}</td>
                                    <td>
                                        <span class="badge bg-success text-uppercase">
                                            {{ $att->status ?? 'present' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        No attendance records found for the selected period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($attendances->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $attendances->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
