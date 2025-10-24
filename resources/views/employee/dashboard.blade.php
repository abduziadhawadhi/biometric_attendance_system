@extends('layouts.app')

@section('content')
<div class="container text-center">
    <h3>Hello, {{ auth()->user()->name }}</h3>
    <p class="text-muted">Your Attendance Today</p>

    <form action="{{ route('attendance.checkin') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-success m-2">Check In</button>
    </form>

    <form action="{{ route('attendance.checkout') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-danger m-2">Check Out</button>
    </form>

    <hr>
    <h5>Your Recent Attendance</h5>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Check In</th>
                <th>Check Out</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $rec)
            <tr>
                <td>{{ $rec->created_at->format('d M Y') }}</td>
                <td>{{ $rec->check_in }}</td>
                <td>{{ $rec->check_out ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
