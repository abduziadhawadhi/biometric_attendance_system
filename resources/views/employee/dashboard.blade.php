@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3 class="text-center text-primary mb-4">Employee Dashboard</h3>

    <!-- Attendance Actions -->
    <div class="card shadow-sm p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h5>Attendance Actions</h5>
            <div>
                <!-- Check-In Button -->
                <form method="POST" action="{{ route('attendance.checkin') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-success">Check In</button>
                </form>

                <!-- Check-Out Button -->
                <form method="POST" action="{{ route('attendance.checkout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger">Check Out</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Attendance History -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h5>My Attendance Records</h5>
            <table class="table table-striped mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->date }}</td>
                            <td>{{ $attendance->check_in ?? '-' }}</td>
                            <td>{{ $attendance->check_out ?? '-' }}</td>
                            <td>{{ ucfirst($attendance->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No attendance records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection


