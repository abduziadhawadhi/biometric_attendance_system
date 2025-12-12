{{-- resources/views/employee/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<style>
    :root {
        --sidebar-bg: #f3f4fb;
        --sidebar-border: #e5e7eb;
        --sidebar-text: #374151;
        --sidebar-active-bg: #e5edff;
        --sidebar-active-text: #1d4ed8;
        --body-bg: #f5f6fb;
    }

    body {
        background: var(--body-bg);
    }

    .dashboard-layout {
        display: flex;
        min-height: calc(100vh - 56px);
    }

    /* Sidebar */
    .dashboard-sidebar {
        width: 260px;
        padding: 16px 12px;
    }

    .sidebar-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid var(--sidebar-border);
        padding: 16px 14px 18px;
        height: 100%;
    }

    .sidebar-logo {
        text-align: center;
        margin-bottom: 14px;
    }

    .sidebar-logo img {
        max-width: 120px;
    }

    .sidebar-section-title {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #9ca3af;
        margin-bottom: 8px;
    }

    .sidebar-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 9px 11px;
        border-radius: 14px;
        text-decoration: none;
        color: var(--sidebar-text);
        font-size: 13px;
        margin-bottom: 6px;
        transition: 0.15s;
    }

    .sidebar-item:hover {
        background: #f3f4ff;
        transform: translateX(2px);
    }

    .sidebar-item.active {
        background: var(--sidebar-active-bg);
        color: var(--sidebar-active-text);
        font-weight: 500;
    }

    /* Main content */
    .dashboard-main {
        flex: 1;
    }

    .dashboard-content {
        padding: 24px 32px 32px;
    }

    .today-card {
        border-radius: 18px;
        box-shadow: 0 6px 18px rgba(148, 163, 184, 0.25);
    }

    .btn-checkin { background: #16a34a; color:#fff; }
    .btn-checkout { background: #dc2626; color:#fff; }

    .table thead {
        background: linear-gradient(90deg,#1e73be,#2373dd);
        color:#fff;
    }
</style>

<div class="dashboard-layout">

    {{-- LEFT SIDEBAR --}}
    <aside class="dashboard-sidebar">
        <div class="sidebar-card">
            <div class="sidebar-logo">
                <img src="{{ asset('images/wcf_logo.png') }}" alt="WCF Logo" onerror="this.outerHTML='WCF';">
            </div>

            <div class="sidebar-section-title">Dashboard</div>

            <a href="{{ route('employee.dashboard') }}" class="sidebar-item active">
                <span>My Attendance</span>
                <span class="text-muted small">{{ $attendances->total() }}</span>
            </a>
        </div>
    </aside>

    {{-- RIGHT SIDE CONTENT --}}
    <div class="dashboard-main">
        <div class="dashboard-content">

            <h4 class="mb-1">ATTENDANCE</h4>
            <small class="text-muted">Time Sheet for <strong>{{ $employee->name }}</strong></small>

            {{-- TODAY CARD --}}
            <div class="card today-card mb-4 mt-3">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">Today</h5>
                            @if(!empty($todayAttendance))
                                <div class="text-muted small">
                                    Checked in at:
                                    {{ optional(\Carbon\Carbon::parse($todayAttendance->created_at))->format('Y-m-d H:i:s') }}
                                    |
                                    Checked out at: {{ $todayAttendance->check_out ?? '-' }}
                                </div>
                            @else
                                <div class="text-muted small">No check in yet today.</div>
                            @endif
                        </div>

                        <div>
                            {{-- Check In --}}
                            <form method="POST" action="{{ route('attendance.checkin') }}" class="d-inline-block">
                                @csrf
                                <button class="btn btn-checkin btn-sm me-2">Check In</button>
                            </form>

                            {{-- Check Out --}}
                            <form method="POST" action="{{ route('attendance.checkout') }}" class="d-inline-block">
                                @csrf
                                <button class="btn btn-checkout btn-sm">Check Out</button>
                            </form>
                        </div>
                    </div>

                    {{-- FILTERS + EXPORT --}}
                    <form method="GET" action="{{ route('employee.dashboard') }}" class="row g-2">
                        <div class="col-md-3">
                            <input type="date" name="start_date" class="form-control"
                                   value="{{ request('start_date') }}">
                        </div>

                        <div class="col-md-3">
                            <input type="date" name="end_date" class="form-control"
                                   value="{{ request('end_date') }}">
                        </div>

                        <div class="col-md-2 d-grid">
                            <button class="btn btn-primary">Search</button>
                        </div>

                        <div class="col-md-4 d-grid">
                            <a href="{{ route('employee.export', ['start_date' => request('start_date'), 'end_date' => request('end_date')]) }}"
                               class="btn btn-success">
                                <i class="bi bi-file-earmark-excel"></i> Export My Attendance
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Your attendance</h5>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Late (min)</th>
                                    <th>Overtime (min)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendances as $att)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($att->created_at)->format('d M Y') }}</td>
                                        <td>{{ $att->check_in ?? '-' }}</td>
                                        <td>{{ $att->check_out ?? '-' }}</td>
                                        <td>{{ $att->late_minutes ?? '-' }}</td>
                                        <td>{{ $att->overtime_minutes ?? '-' }}</td>
                                        <td>
                                            @if($att->status === 'present')
                                                <span class="badge bg-success">Present</span>
                                            @elseif($att->status === 'permission')
                                                <span class="badge bg-warning text-dark">Permission</span>
                                            @else
                                                <span class="badge bg-danger">Absent</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- LARAVEL PAGINATION ONLY --}}
                    <div class="mt-3">
                        {{ $attendances->links() }}
                    </div>

                </div>
            </div>

        </div>
    </div>

</div>
@endsection
