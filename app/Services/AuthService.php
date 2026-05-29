<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function login($credentials, $request)
    {
        // Trouver l'utilisateur
        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if (!$user) {
            return false;
        }

        $guard = match ($user->role) {
            'admin' => 'admin',
            'driver' => 'driver',
            default => 'client',
        };

        if (Auth::guard($guard)->attempt($credentials)) {

            $request->session()->regenerate();

            if ($guard === 'admin') {
                return redirect()->route('admin.dashboard')->with('success', 'Connexion réussie en tant qu\'administrateur.');
            }

            if ($guard === 'driver') {
                return redirect()->route('driver.dashboard')->with('success', 'Connexion réussie en tant que chauffeur.');
            }

            return redirect()->route('client.dashboard')->with('success', 'Connexion réussie.');
        }

        /* if (Auth::attempt($credentials)) {

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
        } */

        return false;
    }

    public function logout()
    {
        // Logic for logging out the user
    }
}
