<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté.');
        }

        // Check if user has any of the required roles
        if (!$user->hasAnyRole($roles)) {
            abort(403, 'Accès non autorisé. Rôle insuffisant.');
        }

        return $next($request);
    }
}
