@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <!-- <h2 class="text-center mb-4 fw-bold">Admin Dashboard</h2> -->

    {{-- TOP CARDS --}}
    <div class="row g-3 mb-4">

        {{-- Total Employees --}}
        <div class="col-md-4">
            <a href="{{ route('admin.dashboard', ['card' => 'all']) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 text-center py-3
                    {{ $card === 'all' ? 'bg-primary text-white' : 'bg-light text-dark' }}">
                    <h6 class="mb-1 text-uppercase small">Total Employees</h6>
                    <h2 class="fw-bold mb-0">{{ $totalEmployees }}</h2>
                </div>
            </a>
        </div>

        {{-- Present Today --}}
        <div class="col-md-4">
            <a href="{{ route('admin.dashboard', ['card' => 'present']) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 text-center py-3
                    {{ $card === 'present' ? 'bg-success text-white' : 'bg-light text-dark' }}">
                    <h6 class="mb-1 text-uppercase small">Present Today</h6>
                    <h2 class="fw-bold mb-0">{{ $presentToday }}</h2>
                </div>
            </a>
        </div>

        {{-- Absent Today --}}
        <div class="col-md-4">
            <a href="{{ route('admin.dashboard', ['card' => 'absent']) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 text-center py-3
                    {{ $card === 'absent' ? 'bg-danger text-white' : 'bg-light text-dark' }}">
                    <h6 class="mb-1 text-uppercase small">Absent Today</h6>
                    <h2 class="fw-bold mb-0">{{ $absentToday }}</h2>
                </div>
            </a>
        </div>
    </div>

    {{-- FILTERS --}}
    <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-2 align-items-end mb-3">
        {{-- keep current card when searching --}}
        <input type="hidden" name="card" value="{{ $card }}">

        <div class="col-md-4">
            <label class="form-label small mb-1">Search Employee</label>
            <input type="text" name="employee_name" class="form-control"
                   placeholder="Name, email, department, position"
                   value="{{ $employeeName }}">
        </div>

        <div class="col-md-3">
            <label class="form-label small mb-1">From Date</label>
            <input type="date" name="start_date" class="form-control"
                   value="{{ $startDate }}">
        </div>

        <div class="col-md-3">
            <label class="form-label small mb-1">To Date</label>
            <input type="date" name="end_date" class="form-control"
                   value="{{ $endDate }}">
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill">
                <i class="bi bi-search"></i> Search
            </button>
            <a href="{{ route('admin.dashboard', ['card' => $card]) }}"
               class="btn btn-outline-secondary flex-fill">
                <i class="bi bi-arrow-clockwise"></i> Reset
            </a>
        </div>
    </form>

    {{-- Export button --}}
    <div class="text-end mb-3">
        <a href="{{ route('admin.export', request()->query()) }}"
           class="btn btn-success">
            <i class="bi bi-file-earmark-excel"></i> Export to Excel
        </a>
    </div>

    {{-- CONTENT CARD --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">{{ $sectionTitle ?: 'Employees' }}</h5>

            {{-- 1) TOTAL EMPLOYEES VIEW --}}
            @if ($viewMode === 'all')
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th style="width:60px;">#</th>
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

                @if($employees)
                    <div class="d-flex justify-content-center">
                        {{ $employees->links() }}
                    </div>
                @endif
            @endif

            {{-- 2) PRESENT VIEW (with late & overtime) --}}
            @if ($viewMode === 'present')
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th style="width:60px;">#</th>
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
                        @forelse ($attendances as $index => $att)
                            <tr>
                                <td>{{ $attendances->firstItem() + $index }}</td>
                                <td>{{ $att->employee->name ?? 'N/A' }}</td>
                                <td>{{ $att->employee->department ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($att->created_at)->toDateString() }}</td>
                                <td>{{ $att->check_in ?? '-' }}</td>
                                <td>{{ $att->check_out ?? '-' }}</td>
                                <td>{{ $att->late_minutes ?? 0 }}</td>
                                <td>{{ $att->overtime_minutes ?? 0 }}</td>
                                <td>
                                    <span class="badge bg-success">
                                        {{ ucfirst($att->status ?? 'present') }}
                                    </span>
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

                @if($attendances)
                    <div class="d-flex justify-content-center">
                        {{ $attendances->links() }}
                    </div>
                @endif
            @endif

            {{-- 3) ABSENT VIEW --}}
            @if ($viewMode === 'absent')
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th style="width:60px;">#</th>
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
                                    No absent employees found.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if($employees)
                    <div class="d-flex justify-content-center">
                        {{ $employees->links() }}
                    </div>
                @endif
            @endif

        </div>
    </div>

</div>
@endsection
