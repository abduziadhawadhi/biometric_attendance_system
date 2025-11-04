<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard | WCF Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #f8f9fa;">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-white" href="#">WCF Attendance</a>
        <div class="d-flex">
            <a href="{{ route('employee.dashboard') }}" class="nav-link text-white me-3">Dashboard</a>
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="nav-link text-white">Logout</a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="text-center text-primary fw-bold mb-4">Welcome, {{ $employee->name }}</h3>

    <!-- Messages -->
    @if (session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger text-center">{{ session('error') }}</div>
    @endif

    <!-- Attendance Buttons -->
    <div class="text-center mb-4">
        <form action="{{ route('attendance.checkin') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success me-2">Check In</button>
        </form>

        <form action="{{ route('attendance.checkout') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-danger">Check Out</button>
        </form>
    </div>

    <!-- Attendance Records -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h5 class="text-primary fw-semibold mb-3">Your Attendance Records</h5>
            <table class="table table-bordered table-hover text-center align-middle">
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
                            <td>{{ \Carbon\Carbon::parse($attendance->check_in)->format('Y-m-d') }}</td>
                            <td>{{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') }}</td>
                            <td>
                                @if ($attendance->check_out)
                                    {{ \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') }}
                                @else
                                    <span class="text-muted">Not Checked Out</span>
                                @endif
                            </td>
                            <td>
                                @if ($attendance->status === 'present')
                                    <span class="badge bg-success">Present</span>
                                @elseif($attendance->status === 'late')
                                    <span class="badge bg-warning text-dark">Late</span>
                                @else
                                    <span class="badge bg-danger">Absent</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-muted text-center">No attendance records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {{ $attendances->links() }}
            </div>
        </div>
    </div>
</div>

<footer class="text-center text-muted mt-4 mb-3">
    &copy; {{ date('Y') }} Workers Compensation Fund (WCF). All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



