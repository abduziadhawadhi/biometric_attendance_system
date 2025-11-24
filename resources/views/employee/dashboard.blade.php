@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4">Welcome, {{ $employee->name }}</h3>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Check In / Check Out buttons --}}
    <div class="card mb-4">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between">
            <div>
                <h5 class="card-title mb-1">
                    Today: {{ \Carbon\Carbon::now('Africa/Nairobi')->format('d M Y') }}
                </h5>
                <p class="mb-0 text-muted">
                    Use the buttons to record your attendance.
                </p>
            </div>

            <div class="d-flex gap-2 mt-3 mt-md-0">
                {{-- Check In --}}
                <form method="POST" action="{{ route('attendance.checkin') }}">
                    @csrf
                    <button type="submit"
                            class="btn btn-success"
                            @if($todayAttendance && $todayAttendance->check_in) disabled @endif>
                        Check In
                    </button>
                </form>

                {{-- Check Out --}}
                <form method="POST" action="{{ route('attendance.checkout') }}">
                    @csrf
                    <button type="submit"
                            class="btn btn-danger"
                            @if(!$todayAttendance || !$todayAttendance->check_in || $todayAttendance->check_out) disabled @endif>
                        Check Out
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Attendance table --}}
    <div class="card">
        <div class="card-header">
            <strong>Your attendance</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-dark">
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
                        @forelse ($attendances as $attendance)
                            @php
                                $date = \Carbon\Carbon::parse($attendance->created_at)
                                    ->timezone('Africa/Nairobi')
                                    ->format('d M Y');
                            @endphp
                            <tr>
                                <td>{{ $date }}</td>
                                <td>{{ $attendance->check_in ?? '-' }}</td>
                                <td>{{ $attendance->check_out ?? '-' }}</td>
                                <td>{{ $attendance->late_minutes ?? '-' }}</td>
                                <td>{{ $attendance->overtime_minutes ?? '-' }}</td>
                                <td>
                                    @if ($attendance->status === 'permission')
                                        <span class="badge bg-warning text-dark">Permission</span>
                                    @elseif ($attendance->status === 'present')
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
            </div>

            <div class="d-flex justify-content-center mt-3 mb-3">
                {{ $attendances->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
