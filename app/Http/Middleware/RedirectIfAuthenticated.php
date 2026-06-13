<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            // Vérifier que le cookie du profil existe encore
            $profil     = auth()->user()->profil;
            $cookieName = 'ctt_' . $profil . '_token';
            $rawToken   = $request->cookie($cookieName);

            // Si pas de cookie valide → pas vraiment connecté
            if (!$rawToken || !PersonalAccessToken::findToken($rawToken)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return $next($request);
            }

            return redirect()->route($profil . '.dashboard');
        }

        return $next($request);
    }
}
