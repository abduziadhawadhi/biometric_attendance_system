<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WCF Biometric Attendance | Login</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        body {
            background: url('{{ asset('images/wcf-bg.jpg') }}') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
        }

        .overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 0;
        }

        .login-container {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 15px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.92);
            border-radius: 16px;
            padding: 40px 35px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
            width: 400px;
            max-width: 95%;
            animation: fadeIn 0.7s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo img {
            height: 80px;
        }

        h4 {
            text-align: center;
            color: #003366;
            font-weight: 700;
            margin-bottom: 20px;
        }

        label {
            font-weight: 600;
            color: #003366;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .btn-primary {
            width: 100%;
            background-color: #004884;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            padding: 10px;
            margin-top: 10px;
            transition: background 0.3s ease-in-out;
        }

        .btn-primary:hover {
            background-color: #00345f;
        }

        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }

        .forgot-password a {
            color: #004884;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        footer {
            text-align: center;
            color: #eee;
            font-size: 0.85rem;
            position: absolute;
            bottom: 10px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="overlay"></div>

    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <img src="{{ asset('images/wcf-logo.png') }}" alt="WCF Logo">
            </div>
            <h4>Sign in to Attendance System</h4>

            @if (session('error'))
                <div class="alert alert-danger text-center">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>

            <div class="forgot-password">
                <a href="{{ route('password.request') }}">Forgot Your Password?</a>
            </div>
        </div>
    </div>

    <footer>
        &copy; {{ date('Y') }} Workers Compensation Fund (WCF). All rights reserved.
    </footer>
</body>
</html>


