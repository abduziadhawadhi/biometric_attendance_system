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
                <h5 class="card-title mb-1">Today</h5>
                <small class="text-muted">
                    @if ($todayAttendance)
                        Checked in at: {{ $todayAttendance->check_in ?? '-' }} |
                        Checked out at: {{ $todayAttendance->check_out ?? '-' }}
                    @else
                        No attendance recorded yet.
                    @endif
                </small>
            </div>

            <div class="mt-3 mt-md-0">
                {{-- Check In --}}
                <form action="{{ route('attendance.checkin') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit"
                            class="btn btn-success"
                            @if($todayAttendance && $todayAttendance->check_in) disabled @endif>
                        Check In
                    </button>
                </form>

                {{-- Check Out --}}
                <form action="{{ route('attendance.checkout') }}" method="POST" class="d-inline ms-2">
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

    {{-- Attendance history --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Your attendance</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
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
                            @php
                                $date = \Carbon\Carbon::parse($attendance->created_at)->timezone('Africa/Nairobi')->format('d M Y');
                            @endphp
                            <tr>
                                <td>{{ $date }}</td>
                                <td>{{ $attendance->check_in ?? '-' }}</td>
                                <td>{{ $attendance->check_out ?? '-' }}</td>
                                <td>{{ $attendance->late_minutes ?? '-' }}</td>
                                <td>{{ $attendance->overtime_minutes ?? '-' }}</td>
                                <td>
                                    @if($attendance->status === 'permission')
                                        <span class="badge bg-warning text-dark">Permission</span>
                                    @elseif($attendance->status === 'present')
                                        <span class="badge bg-success">Present</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($attendance->status ?? 'N/A') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">
                                    No attendance records.
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
