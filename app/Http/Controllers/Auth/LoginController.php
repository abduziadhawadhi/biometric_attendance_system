<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to the correct dashboard based on their role.
    |
    */

    use AuthenticatesUsers;

    /**
     * Determine where to redirect users after login based on their role.
     *
     * @return string
     */
    protected function redirectTo()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            return '/admin/dashboard';
        }

        if ($user->role === 'employee') {
            return '/employee/dashboard';
        }

        // Fallback in case a user has no role assigned
        return '/login';
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle user logout and redirect to login page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'You have been logged out successfully.');
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Guests can only access login, authenticated users can log out
        $this->middleware('guest')->except('logout');
    }
}


