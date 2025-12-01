@extends('layouts.app')

@section('content')
<style>
    /* KPI card styles */
    .card-clickable { text-decoration: none; color: inherit; }
    .card-clickable .card {
        border-radius: 12px;
        transition: transform .12s ease, box-shadow .12s ease;
        cursor: pointer;
    }
    .card-clickable .card:hover { transform: translateY(-6px); box-shadow: 0 8px 30px rgba(0,0,0,0.12); }
    .kpi-value { font-size: 1.9rem; font-weight: 700; }
    .kpi-title { font-size: .95rem; font-weight: 600; color: #fff; opacity: .95; }
    .card-blue { background: linear-gradient(135deg,#0b63b7,#0a4b8c); color: #fff; }
    .card-green { background: linear-gradient(135deg,#28a745,#1f7a33); color: #fff; }
    .card-red { background: linear-gradient(135deg,#dc3545,#a8272a); color: #fff; }
    .kpi-sub { font-size: .9rem; opacity: .95; }
    .card-active { box-shadow: 0 10px 40px rgba(0,0,0,0.18); transform: translateY(-4px); }

    /* Small pagination */
    .pagination { --bs-pagination-padding-y: .25rem; --bs-pagination-padding-x: .6rem; font-size: .9rem; }
    .table thead th { vertical-align: middle; }
    .small-muted { font-size: .85rem; color:#6c757d; }
    .filter-row .form-control { min-height: 44px; }
    .nowrap { white-space: nowrap; }
</style>

<div class="container mt-4">
    <h3 class="text-center text-primary mb-4">Admin Dashboard</h3>

    {{-- KPI CARDS --}}
    <div class="row text-center mb-4 g-3">
        <div class="col-md-4">
            <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['card' => 'all'])) }}" class="card-clickable">
                <div class="card card-blue shadow-sm border-0 p-3 {{ $card === 'all' ? 'card-active' : '' }}">
                    <div class="card-body">
                        <div class="kpi-title">Total Employees</div>
                        <div class="kpi-value">{{ $totalEmployees ?? 0 }}</div>
                        <div class="kpi-sub small-muted">All registered employees</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['card' => 'present'])) }}" class="card-clickable">
                <div class="card card-green shadow-sm border-0 p-3 {{ $card === 'present' ? 'card-active' : '' }}">
                    <div class="card-body">
                        <div class="kpi-title">Present Today</div>
                        <div class="kpi-value">{{ $presentToday ?? 0 }}</div>
                        <div class="kpi-sub small-muted">{{ $permissionToday ?? 0 }} with permission</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['card' => 'absent'])) }}" class="card-clickable">
                <div class="card card-red shadow-sm border-0 p-3 {{ $card === 'absent' ? 'card-active' : '' }}">
                    <div class="card-body">
                        <div class="kpi-title">Absent Today</div>
                        <div class="kpi-value">{{ $absentToday ?? 0 }}</div>
                        <div class="kpi-sub small-muted">Employees not checked-in</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Filters & Export --}}
    <div class="row align-items-center mb-3">
        <div class="col-lg-9">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-2 filter-row">
                {{-- preserve card --}}
                <input type="hidden" name="card" value="{{ $card }}">

                <div class="col-md-5">
                    <input type="text" name="employee_name" class="form-control" placeholder="Search employee name / email / dept" value="{{ old('employee_name', $employeeName ?? '') }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $startDate) }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $endDate) }}">
                </div>
                <div class="col-md-1 d-grid">
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </form>
        </div>

        <div class="col-lg-3 text-end">
            <a href="{{ route('admin.export', request()->query()) }}" class="btn btn-success">
                <i class="bi bi-file-earmark-excel"></i> Export to Excel
            </a>
        </div>
    </div>

    {{-- Section Title --}}
    <div class="mb-3">
        <h5 class="mb-0">{{ $sectionTitle ?: 'Overview' }}</h5>
        <small class="small-muted">Showing results for: <strong>{{ ucfirst($card) }}</strong></small>
    </div>

    {{-- TABLES --}}
    <div class="card shadow-sm">
        <div class="card-body">
            {{-- Present view shows attendances --}}
            @if ($viewMode === 'present')
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
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
                                <tr><td colspan="8" class="text-center small-muted">No attendance records found.</td></tr>
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
                    <table class="table table-hover align-middle">
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
                                            // Determine today's status quickly for display (present vs absent)
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
                                <tr><td colspan="5" class="text-center small-muted">No employees found.</td></tr>
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
</div>
@endsection
