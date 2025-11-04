<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | WCF Attendance</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body style="background-color: #f8f9fa;">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-white" href="#">WCF Attendance</a>
            <div class="d-flex">
                <a href="{{ route('admin.dashboard') }}" class="nav-link text-white me-3">Dashboard</a>
                <a href="{{ route('logout') }}" 
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                   class="nav-link text-white">Logout</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Title -->
        <h3 class="text-center text-primary mb-4 fw-bold">Admin Dashboard</h3>

        <!-- Dashboard Summary -->
        <div class="row text-center mb-4">
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 p-3 bg-light">
                    <h5>Total Employees</h5>
                    <h2>{{ $totalEmployees }}</h2>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 p-3 text-white" style="background-color: #198754;">
                    <h5>Present Today</h5>
                    <h2>{{ $presentToday }}</h2>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 p-3 text-white" style="background-color: #dc3545;">
                    <h5>Absent Today</h5>
                    <h2>{{ $absentToday }}</h2>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3 mb-4">
            <div class="col-md-4">
                <input type="text" name="employee_name" class="form-control" placeholder="Search Employee..." value="{{ $employeeName }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-2 text-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 text-primary fw-semibold">Attendance Records</h5>
            <div>
                <a href="{{ route('employees.create') }}" class="btn btn-primary me-2">
                    <i class="bi bi-person-plus"></i> Add Employee
                </a>
                <a href="{{ route('admin.export', request()->all()) }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel"></i> Export to Excel
                </a>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Employee Name</th>
                            <th>Department</th>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attendances as $attendance)
                            <tr class="text-center">
                                <td>{{ $attendance->employee->name ?? 'N/A' }}</td>
                                <td>{{ $attendance->employee->department ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($attendance->check_in)->format('Y-m-d') }}</td>
                                <td>{{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') }}</td>
                                <td>
                                    @if($attendance->check_out)
                                        {{ \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') }}
                                    @else
                                        <span class="text-muted">Not Checked Out</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attendance->status === 'present')
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
                                <td colspan="6" class="text-center text-muted">No attendance records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $attendances->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center mt-4 mb-3 text-muted">
        &copy; {{ date('Y') }} Workers Compensation Fund (WCF). All rights reserved.
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
