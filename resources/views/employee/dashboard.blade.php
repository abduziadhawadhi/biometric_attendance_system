@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3 class="mb-3">Welcome, {{ $employee->name }}</h3>

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
                <h5 class="card-title mb-1">Today&apos;s Attendance</h5>
                <p class="mb-0 text-muted">
                    @if ($todayAttendance && $todayAttendance->check_in)
                        Checked in at <strong>{{ $todayAttendance->check_in }}</strong>
                        @if ($todayAttendance->check_out)
                            , checked out at <strong>{{ $todayAttendance->check_out }}</strong>
                        @else
                            , not yet checked out
                        @endif
                    @else
                        You have not checked in yet today.
                    @endif
                </p>
            </div>

            <div class="mt-3 mt-md-0">
                @if ($canCheckIn)
                    <form method="POST" action="{{ route('attendance.checkin') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-box-arrow-in-right"></i> Check In
                        </button>
                    </form>
                @endif

                @if ($canCheckOut)
                    <form method="POST" action="{{ route('attendance.checkout') }}" class="d-inline ms-2">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-box-arrow-right"></i> Check Out
                        </button>
                    </form>
                @endif

                @unless($canCheckIn || $canCheckOut)
                    <span class="badge bg-secondary ms-2">Today completed</span>
                @endunless
            </div>
        </div>
    </div>

    {{-- Attendance history --}}
    <div class="card">
        <div class="card-header">
            <strong>Your attendance</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
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
                            <tr>
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
                                <td colspan="6" class="text-center text-muted py-3">
                                    No attendance records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
