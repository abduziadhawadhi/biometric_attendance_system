@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg rounded-4">
                <div class="card-header text-center bg-primary text-white">
                    <h4>WCF Biometric Attendance System</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label>Email Address</label>
                            <input id="email" type="email" class="form-control" name="email" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input id="password" type="password" class="form-control" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Sign In</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
