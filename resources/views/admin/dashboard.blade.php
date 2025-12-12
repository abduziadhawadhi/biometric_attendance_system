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
        min-height: calc(100vh - 56px); /* adjust if top navbar height differs */
    }

    /* ===== LEFT SIDEBAR (LIKE SAMPLE) ===== */
    .dashboard-sidebar {
        width: 260px;
        padding: 16px 12px;
        background: transparent;
        display: flex;
        flex-direction: column;
    }

    .sidebar-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid var(--sidebar-border);
        padding: 16px 14px 18px;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .sidebar-logo {
        text-align: center;
        margin-bottom: 14px;
    }

    .sidebar-logo img {
        max-width: 120px;
        height: auto;
    }

    .sidebar-section-title {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #9ca3af;
        margin: 6px 0 8px;
    }

    .sidebar-menu {
        display: flex;
        flex-direction: column;
        gap: 6px;
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
        transition: background 0.15s ease, color 0.15s ease, transform 0.1s ease;
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

    .sidebar-item-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .sidebar-item-icon {
        width: 22px;
        height: 22px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .sidebar-item-label {
        white-space: nowrap;
    }

    .sidebar-badge {
        font-size: 12px;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: 999px;
        background: #eff1f9;
        color: #4b5563;
    }

    .sidebar-item-all  .sidebar-item-icon { background: #e0ebff; color: #1d4ed8; }
    .sidebar-item-pres .sidebar-item-icon { background: #dcfce7; color: #15803d; }
    .sidebar-item-abs  .sidebar-item-icon { background: #fee2e2; color: #b91c1c; }
    .sidebar-item-pres .sidebar-badge { background: #dcfce7; color: #166534; }
    .sidebar-item-abs  .sidebar-badge { background: #fee2e2; color: #b91c1c; }

    /* ===== RIGHT MAIN CONTENT ===== */
    .dashboard-main {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .dashboard-content {
        padding: 24px 32px 32px;
    }

    .page-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .page-subtitle {
        font-size: 13px;
        color: #6b7280;
    }

    .small-muted { font-size: .85rem; color:#6c757d; }
    .filter-row .form-control { min-height: 44px; }
    .nowrap { white-space: nowrap; }

    .content-card {
        margin-top: 20px;
        background: #ffffff;
        border-radius: 18px;
        padding: 20px 22px 24px;
        box-shadow: 0 6px 18px rgba(148, 163, 184, 0.25);
    }

    .content-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
        gap: 10px;
        flex-wrap: wrap;
    }

    @media (max-width: 768px) {
        .dashboard-layout { flex-direction: column; }
        .dashboard-sidebar { width: 100%; }
        .dashboard-main { width: 100%; }
    }
</style>

<div class="dashboard-layout">

    {{-- LEFT SIDEBAR --}}
    <aside class="dashboard-sidebar">
        <div class="sidebar-card">
            <div class="sidebar-logo">
                {{-- Replace with your real logo path if you have it, e.g. images/wcf_logo.png --}}
                {{-- <img src="{{ asset('images/wcf_logo.png') }}" alt="WCF Logo"> --}}
                <img src="{{ asset('images/wcf_logo.png') }}" alt="WCF Logo" onerror="this.outerHTML='WCF';">
            </div>

            <div class="sidebar-section-title">Dashboard</div>
            <div class="sidebar-menu">

                {{-- "All Employees" behaves like Agent Dashboard in sample --}}
                <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['card' => 'all'])) }}"
                   class="sidebar-item sidebar-item-all {{ $card === 'all' ? 'active' : '' }}">
                    <div class="sidebar-item-left">
                        <span class="sidebar-item-icon">
                            {{-- grid icon --}}
                            ▢
                        </span>
                        <span class="sidebar-item-label">All Employees</span>
                    </div>
                    <span class="sidebar-badge">{{ $totalEmployees ?? 0 }}</span>
                </a>

                {{-- Present --}}
                <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['card' => 'present'])) }}"
                   class="sidebar-item sidebar-item-pres {{ $card === 'present' ? 'active' : '' }}">
                    <div class="sidebar-item-left">
                        <span class="sidebar-item-icon">
                            {{-- chat-like icon --}}
                            ✔
                        </span>
                        <span class="sidebar-item-label">Present Today</span>
                    </div>
                    <span class="sidebar-badge">{{ $presentToday ?? 0 }}</span>
                </a>

                {{-- Absent --}}
                <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['card' => 'absent'])) }}"
                   class="sidebar-item sidebar-item-abs {{ $card === 'absent' ? 'active' : '' }}">
                    <div class="sidebar-item-left">
                        <span class="sidebar-item-icon">
                            {{-- note icon --}}
                            ✖
                        </span>
                        <span class="sidebar-item-label">Absent Today</span>
                    </div>
                    <span class="sidebar-badge">{{ $absentToday ?? 0 }}</span>
                </a>

            </div>
        </div>
    </aside>

    {{-- RIGHT MAIN CONTENT --}}
    <div class="dashboard-main">
        <div class="dashboard-content">

            {{-- Header like sample --}}
            <div class="mb-3">
                <div class="page-title">ATTENDANCE</div>
                <div class="page-subtitle">
                    Time Sheet for
                    <strong>{{ auth()->user()->name ?? 'Admin' }}</strong>
                </div>
            </div>

            {{-- FILTERS + EXPORT + TABLE --}}
            <div class="content-card">

                {{-- Filters & Export --}}
                <div class="content-card-header">
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-2 filter-row flex-grow-1">
                        {{-- preserve card --}}
                        <input type="hidden" name="card" value="{{ $card }}">

                        <div class="col-md-5">
                            <input type="text" name="employee_name" class="form-control"
                                   placeholder="Search employee name / email / dept"
                                   value="{{ old('employee_name', $employeeName ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="start_date" class="form-control"
                                   value="{{ old('start_date', $startDate) }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="end_date" class="form-control"
                                   value="{{ old('end_date', $EndDate ?? $endDate ?? '') }}">
                        </div>
                        <div class="col-md-1 d-grid">
                            <button class="btn btn-primary" type="submit">Search</button>
                        </div>
                    </form>

                    <div class="text-end">
                        <a href="{{ route('admin.export', request()->query()) }}" class="btn btn-success btn-sm">
                            <i class="bi bi-file-earmark-excel"></i> Export to Excel
                        </a>
                    </div>
                </div>

                {{-- Section Title --}}
                <div class="mb-3">
                    <h6 class="mb-0">{{ $sectionTitle ?: 'Overview' }}</h6>
                    <small class="small-muted">Showing results for: <strong>{{ ucfirst($card) }}</strong></small>
                </div>

                {{-- TABLES --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        {{-- Present view shows attendances --}}
                        @if ($viewMode === 'present')
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-dark">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Department</th>
                                        <th class="nowrap">Date</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Late (min)</th>
                                        <th>Overtime (min)</th>
                                        <th>Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($attendances ?? collect() as $att)
                                        <tr>
                                            <td>{{ $att->employee->name ?? 'N/A' }}</td>
                                            <td>{{ $att->employee->department ?? 'N/A' }}</td>
                                            <td class="nowrap">{{ \Carbon\Carbon::parse($att->created_at)->format('d M Y') }}</td>
                                            <td class="nowrap">{{ $att->check_in ?? '-' }}</td>
                                            <td class="nowrap">{{ $att->check_out ?? '-' }}</td>
                                            <td>{{ $att->late_minutes ?? 0 }}</td>
                                            <td>{{ $att->overtime_minutes ?? 0 }}</td>
                                            <td>{{ ucfirst($att->status ?? 'unknown') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center small-muted p-3">
                                                No attendance records found.
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- pagination --}}
                            <div class="d-flex justify-content-center mt-3">
                                @if($attendances) {{ $attendances->links() }} @endif
                            </div>

                        {{-- All / Absent view shows employees --}}
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-dark">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Department</th>
                                        <th>Email</th>
                                        <th>Position</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($employees ?? collect() as $emp)
                                        <tr>
                                            <td>{{ $emp->name }}</td>
                                            <td>{{ $emp->department ?? '-' }}</td>
                                            <td>{{ $emp->email }}</td>
                                            <td>{{ $emp->position ?? '-' }}</td>
                                            <td class="text-center">
                                                @php
                                                    $present = \App\Models\Attendance::where('employee_id', $emp->id)
                                                        ->whereDate('created_at', \Carbon\Carbon::now('Africa/Nairobi')->toDateString())
                                                        ->whereNotNull('check_in')
                                                        ->exists();
                                                @endphp
                                                @if ($present)
                                                    <span class="badge bg-success">Present</span>
                                                @else
                                                    <span class="badge bg-danger">Absent</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center small-muted p-3">
                                                No employees found.
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-center mt-3">
                                @if($employees) {{ $employees->links() }} @endif
                            </div>
                        @endif
                    </div>
                </div>

            </div> {{-- /content-card --}}
        </div> {{-- /dashboard-content --}}
    </div> {{-- /dashboard-main --}}
</div> {{-- /dashboard-layout --}}
@endsection
