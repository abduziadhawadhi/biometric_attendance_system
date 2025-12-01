{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet"/>
<link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" rel="stylesheet"/>
<style>
    /* Corporate blue theme + card polish */
    .kpi-card { border-radius: 8px; padding: 28px; color: #fff; box-shadow: 0 6px 14px rgba(0,0,0,0.04); }
    .kpi-total { background: linear-gradient(90deg,#1e73be,#2373dd); }
    .kpi-present { background: linear-gradient(90deg,#1f7e4a,#1aa05a); }
    .kpi-absent { background: linear-gradient(90deg,#c93a3a,#d34444); }
    .kpi-number { font-size: 34px; font-weight:700; margin-top:8px; }
    .dataTables_wrapper .dt-buttons { margin-bottom: 10px; }
    .table thead { background: linear-gradient(90deg,#1e73be,#2373dd); color: #fff; }
    .filter-row .form-control { min-height: 44px; }
</style>

<div class="container py-4">
    <h2 class="text-center mb-4" style="font-weight:700">Admin Dashboard</h2>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="kpi-card kpi-total text-center">
                <div>TOTAL EMPLOYEES</div>
                <div class="kpi-number">{{ $totalEmployees ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-card kpi-present text-center">
                <div>PRESENT TODAY</div>
                <div class="kpi-number">{{ $presentToday ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-card kpi-absent text-center">
                <div>ABSENT TODAY</div>
                <div class="kpi-number">{{ $absentToday ?? 0 }}</div>
            </div>
        </div>
    </div>

    {{-- Filters: search + dates + actions --}}
    <form id="filterForm" class="row align-items-center mb-3 g-2" onsubmit="return false;">
        <div class="col-md-5">
            <input id="searchInput" class="form-control" placeholder="Search Employee (name, email, dept, position)"
                   value="{{ old('employee_name', $employeeName ?? '') }}">
        </div>

        <div class="col-md-2">
            <input id="startDate" type="date" class="form-control" value="{{ $startDate ?? '' }}">
        </div>

        <div class="col-md-2">
            <input id="endDate" type="date" class="form-control" value="{{ $endDate ?? '' }}">
        </div>

        <div class="col-md-3 text-end">
            <button id="btnSearch" class="btn btn-primary me-2">🔍 Search</button>
            <button id="btnReset" class="btn btn-outline-secondary me-2">⟲ Reset</button>

            {{-- Client-side Excel export using DataTables Buttons --}}
            <button id="btnExportClient" class="btn btn-success">📥 Export to Excel</button>
            {{-- If you want to use server-side export (route admin.export), uncomment the a tag below & remove client export button above
            <a href="{{ route('admin.export', request()->query()) }}" class="btn btn-success">Export to Excel</a>
            --}}
        </div>
    </form>

    {{-- Content area: show employees list (default) or attendance records based on viewMode --}}
    <div class="card shadow-sm">
        <div class="card-body">
            @php
                $viewMode = $viewMode ?? 'employees'; // fallback
            @endphp

            @if ($viewMode === 'attendance')
                <h5 class="mb-3">Attendance Records</h5>
                <div class="table-responsive">
                    <table id="attendanceTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Employee</th>
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
                        @if(!empty($attendances) && count($attendances))
                            @foreach($attendances as $i => $att)
                                <tr>
                                    <td>{{ $i+1 }}</td>
                                    <td>{{ $att->employee->name ?? '-' }}</td>
                                    <td>{{ $att->employee->department ?? '-' }}</td>
                                    <td>{{ optional(\Carbon\Carbon::parse($att->created_at))->format('d M Y') }}</td>
                                    <td>{{ $att->check_in ?? '-' }}</td>
                                    <td>{{ $att->check_out ?? '-' }}</td>
                                    <td>{{ $att->late_minutes ?? '-' }}</td>
                                    <td>{{ $att->overtime_minutes ?? '-' }}</td>
                                    <td>
                                        @if($att->status === 'present') <span class="badge bg-success">Present</span>
                                        @elseif($att->status === 'permission') <span class="badge bg-warning text-dark">Permission</span>
                                        @else <span class="badge bg-danger">Absent</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="9" class="text-center text-muted">No attendance records found.</td></tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            @else
                <h5 class="mb-3">All Employees</h5>
                <div class="table-responsive">
                    <table id="employeesTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width:60px">#</th>
                                <th>Employee Name</th>
                                <th>Department</th>
                                <th>Email</th>
                                <th>Position</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if(!empty($employees) && count($employees))
                            @foreach($employees as $idx => $emp)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ $emp->name }}</td>
                                    <td>{{ $emp->department }}</td>
                                    <td>{{ $emp->email }}</td>
                                    <td>{{ $emp->position }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="5" class="text-center text-muted">No employees found.</td></tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- scripts --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Init DataTable for whichever table exists
    let table = null;
    if (document.querySelector('#employeesTable')) {
        table = $('#employeesTable').DataTable({
            pageLength: 10,
            dom: 'Bfrtip',
            buttons: [],
            order: []
        });
    }
    if (document.querySelector('#attendanceTable')) {
        table = $('#attendanceTable').DataTable({
            pageLength: 10,
            dom: 'Bfrtip',
            buttons: [],
            order: [[3, 'desc']]
        });
    }

    // Hook up local "Export to Excel" button - use DataTables Buttons extension
    document.getElementById('btnExportClient').addEventListener('click', function(e){
        e.preventDefault();
        if (!table) { alert('Nothing to export.'); return; }

        // Add a temp excel button and trigger it
        const exportBtn = new $.fn.dataTable.Buttons(table, {
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: 'Export',
                    title: 'export',
                    exportOptions: { columns: ':visible' }
                }
            ]
        }).container().appendTo(document.createElement('div'));

        // trigger excel export
        exportBtn.find('button').click();
        // cleanup
        exportBtn.remove();
    });

    // Search button: apply local search (client-side) or submit to server if you prefer
    document.getElementById('btnSearch').addEventListener('click', function(e){
        e.preventDefault();
        const q = document.getElementById('searchInput').value.trim().toLowerCase();
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;

        // If you want server filtering instead, redirect to admin route with query params:
        // window.location = "{{ route('admin.dashboard') }}?employee_name=" + encodeURIComponent(q) + "&start_date=" + start + "&end_date=" + end + "&card=all";

        // Client side: use DataTables search + custom date column filter when attendance table
        if (table) {
            table.search(q).draw();

            // If we have attendance table, apply date filter on column 3 (date)
            if ($('#attendanceTable').length && (start || end)) {
                // custom filtering
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex){
                    const dateStr = data[3]; // date column (format: d M Y)
                    if (!dateStr) return true;
                    const parsed = new Date(dateStr);
                    if (isNaN(parsed)) return true;
                    if (start && new Date(start) > parsed) return false;
                    if (end && new Date(end) < parsed) return false;
                    return true;
                });
                table.draw();
                // remove custom filter to avoid stacking on repeated filter
                $.fn.dataTable.ext.search.pop();
            }
        }
    });

    // Reset
    document.getElementById('btnReset').addEventListener('click', function(e){
        e.preventDefault();
        document.getElementById('searchInput').value = '';
        document.getElementById('startDate').value = '';
        document.getElementById('endDate').value = '';
        if (table) { table.search('').columns().search('').draw(); }
    });
});
</script>
@endsection
