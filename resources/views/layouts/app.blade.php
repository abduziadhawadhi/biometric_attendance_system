<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'WCF Attendance System') }}</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    {{-- GLOBAL PAGINATION FIXES --}}
    <style>
        /* ===============================
           LARAVEL PAGINATION FIX (Admin + Employee)
        ================================*/
        .pagination {
            padding: 0 !important;
            margin-top: 10px !important;
        }

        .pagination .page-item .page-link {
            padding: 4px 10px !important;
            font-size: 13px !important;
            border-radius: 6px !important;
        }

        .pagination .page-item.active .page-link {
            background-color: #2563eb !important;
            border-color: #2563eb !important;
            color: #fff !important;
        }


        /* ===============================
           DATATABLES PAGINATION FIX (Employee table)
        ================================*/
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 3px 8px !important;
            margin: 0 2px !important;
            font-size: 12px !important;
            border-radius: 4px !important;
            border: 1px solid #d1d5db !important;
            background: #f3f4f6 !important;
            color: #374151 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #e5e7eb !important;
            color: #000 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #2563eb !important;
            color: white !important;
            border: 1px solid #2563eb !important;
        }

        /* Remove the HUGE SVG Arrow Buttons */
        .dataTables_wrapper .dataTables_paginate .paginate_button .previous,
        .dataTables_wrapper .dataTables_paginate .paginate_button .next {
            font-size: 12px !important;
        }

        /* Fix arrow icons */
        .dataTables_wrapper .dataTables_paginate .paginate_button svg {
            width: 12px !important;
            height: 12px !important;
        }
    </style>

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
