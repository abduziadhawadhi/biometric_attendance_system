@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3 class="text-center mb-4">Admin Dashboard</h3>

    {{-- Cards row --}}
    <div class="row mb-4 text-center">
        {{-- Total Employees --}}
        <div class="col-md-4 mb-3">
            <a href="{{ route('admin.dashboard', ['card' => 'all']) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="text-muted">Total Employees</h5>
                        <h2 class="mt-2">{{ $totalEmployees }}</h2>
                    </div>
                </div>
            </a>
        </div>

        {{-- Present Today --}}
        <div class="col-md-4 mb-3">
            <a href="{{ route('admin.dashboard', ['card' => 'present']) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 bg-success text-white">
                    <div class="card-body">
                        <h5>Present Today</h5>
                        <h2 class="mt-2">{{ $presentToday }}</h2>
                    </div>
                </div>
            </a>
        </div>

        {{-- Absent Today --}}
        <div class="col-md-4 mb-3">
            <a href="{{ route('admin.dashboard', ['card' => 'absent']) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 bg-danger text-white">
                    <div class="card-body">
                        <h5>Absent Today</h5>
                        <h2 class="mt-2">{{ $absentToday }}</h2>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Filters row --}}
    <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3 mb-3">
        {{-- Keep current card when searching --}}
        <input type="hidden" name="card" value="{{ $card }}">

        <div class="col-md-6">
            <input type="text"
                   name="employee_name"
                   class="form-control"
                   placeholder="Search Employee (name, email, department, position)"
                   value="{{ $employeeName }}">
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search"></i> Search
            </button>
        </div>

        <div class="col-md-2">
            <a href="{{ route('admin.dashboard', ['card' => $card]) }}" class="btn btn-outline-secondary w-100">
                <i class="bi bi-arrow-clockwise"></i> Reset
            </a>
        </div>

        <div class="col-md-2 text-md-end">
            <a href="{{ route('employees.create') }}" class="btn btn-success w-100">
                <i class="bi bi-person-plus"></i> Add New Employee
            </a>
        </div>
    </form>

    {{-- Employees table (depends on selected card) --}}
    <div class="card">
        <div class="card-header">
            <strong>{{ $sectionTitle }}</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Employee Name</th>
                            <th>Department</th>
                            <th>Email</th>
                            <th>Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $index => $employee)
                            <tr>
                                <td>{{ ($employees->currentPage() - 1) * $employees->perPage() + $index + 1 }}</td>
                                <td>{{ $employee->name }}</td>
                                <td>{{ $employee->department }}</td>
                                <td>{{ $employee->email }}</td>
                                <td>{{ $employee->position }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    No employees found for this filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3 mb-3">
                {{ $employees->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
