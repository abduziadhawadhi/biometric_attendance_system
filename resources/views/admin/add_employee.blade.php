@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">Add New Employee</h3>

    <form action="{{ route('employees.store') }}" method="POST">
        @csrf
        
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Department</label>
            <input type="text" name="department" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Position</label>
            <input type="text" name="position" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Email Login</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Password Login</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button class="btn btn-primary">Save Employee</button>
    </form>
</div>
@endsection

