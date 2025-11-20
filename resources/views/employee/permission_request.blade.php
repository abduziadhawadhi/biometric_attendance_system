@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header">Request Permission</div>
        <div class="card-body">
            <form action="{{ route('permission.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Reason</label>
                    <textarea name="reason" class="form-control" rows="4" required></textarea>
                </div>
                <button class="btn btn-primary">Submit Request</button>
            </form>
        </div>
    </div>
</div>
@endsection
