<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'WCF Attendance System') }}</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <!-- Optional Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">WCF Attendance</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    @auth
                        {{-- Redirect Dashboard Based on User Role --}}
                        @if(auth()->user()->role === 'admin')
                            <li class="nav-item">
                                <a href="{{ route('admin.dashboard') }}" class="nav-link">Dashboard</a>
                            </li>
                        @elseif(auth()->user()->role === 'employee')
                            <li class="nav-item">
                                <a href="{{ route('employee.dashboard') }}" class="nav-link">Dashboard</a>
                            </li>
                        @endif

                        {{-- Logout --}}
                        <li class="nav-item">
                            <a href="{{ route('logout') }}"
                               class="nav-link"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Logout
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>


