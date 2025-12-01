{{-- resources/views/employee/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet"/>
<style>
    .today-card { border-radius:8px; padding:18px; }
    .btn-checkin { background: #28a745; color:#fff; }
    .btn-checkout { background: #dc3545; color:#fff; }
    .table thead { background: linear-gradient(90deg,#1e73be,#2373dd); color:#fff; }
</style>

<div class="container py-4">
    <h2 class="mb-4">Welcome, {{ auth()->user()->name }}</h2>

    {{-- Today box --}}
    <div class="card today-card mb-4 shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">Today</h5>
                @if(!empty($todayAttendance))
                    <div class="text-muted">
                        Checked in at: {{ optional(\Carbon\Carbon::parse($todayAttendance->created_at))->format('Y-m-d H:i:s') }}
                        |
                        Checked out at: {{ $todayAttendance->check_out ?? '-' }}
                    </div>
                @else
                    <div class="text-muted">No check in yet today.</div>
                @endif
            </div>

            <div>
                {{-- Check In Form --}}
                <form method="POST" action="{{ route('attendance.checkin') }}" style="display:inline-block;">
                    @csrf
                    <button class="btn btn-checkin btn-lg me-2">Check In</button>
                </form>

                {{-- Check Out Form --}}
                <form method="POST" action="{{ route('attendance.checkout') }}" style="display:inline-block;">
                    @csrf
                    <button class="btn btn-checkout btn-lg">Check Out</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Attendance table --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Your attendance</h5>
            <div class="table-responsive">
                <table id="myAttendance" class="table table-striped table-bordered">
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
                    @if(!empty($attendances) && count($attendances))
                        @foreach($attendances as $att)
                            <tr>
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
                        <tr><td colspan="6" class="text-center text-muted">No attendance records yet.</td></tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- scripts --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function(){
    $('#myAttendance').DataTable({
        pageLength: 10,
        order: [[0, 'desc']],
        dom: 'lrtip' // basic controls, no default search on top (keeps layout clean)
    });
});
</script>
@endsection
