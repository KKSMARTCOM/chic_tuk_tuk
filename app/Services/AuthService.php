<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function login($credentials, $request)
    {
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Redirect based on role: admins -> admin dashboard, others -> client dashboard
            $user = Auth::user();

            if ($user && $user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'))->with('success', 'Connexion réussie en tant qu\'administrateur.');
            }

            if ($user && $user->role === 'driver') {
                return redirect()->intended(route('driver.dashboard'))->with('success', 'Connexion réussie en tant que chauffeur.');
            }

            // Default: client dashboard (includes clients and drivers per requirements)
            return redirect()->intended(route('client.dashboard'))->with('success', 'Connexion réussie.');
        }

        return false;
    }

    public function logout()
    {
        // Logic for logging out the user
    }
}
