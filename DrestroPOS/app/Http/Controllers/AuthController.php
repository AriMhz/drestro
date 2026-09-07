<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        // If already logged in, redirect to admin
        if (Auth::check()) {
            return redirect('/admin');
        }
        
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $loginValue = $request->input('login');

        // Determine if the user is logging in via email or username
        $field = filter_var($loginValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Find user without global scopes since tenant is not yet known at centralized login portal
        $user = \App\Models\User::withoutGlobalScopes()->where($field, $loginValue)->first();

        // Always 'Remember Me' so staff stay logged in across sessions
        if ($user && \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            \Illuminate\Support\Facades\Auth::login($user, true);
            $request->session()->regenerate();
            
            // Set tenant slug for TenantMiddleware
            if ($user->restaurant) {
                $request->session()->put('tenant_slug', $user->restaurant->slug);
                $request->session()->save(); // Explicitly save to prevent race conditions during redirect
            }

            if (in_array($user->role, ['admin', 'manager', 'super_admin'])) {
                return redirect()->intended('/admin');
            }
            
            $roleMap = [
                'waiter' => '/staff/waiter',
                'cashier' => '/staff/cashier',
                'receptionist' => '/staff/hotel-reception',
                'hotel' => '/staff/room-service',
                'kitchen' => '/staff/kitchen',
                'bar' => '/staff/bar'
            ];
            
            return redirect()->intended($roleMap[$user->role] ?? '/login');
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $host = $request->getHost();
        if (str_contains($host, 'localhost') || $host === '127.0.0.1') {
            return redirect('http://localhost:3000/signout');
        }
        return redirect('https://drestro.com/signout');
    }
}
