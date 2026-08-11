<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectSanctumTokenFromCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->bearerToken()) {
            // Parcourt les trois cookies possibles
            foreach (['admin', 'driver', 'client', 'owner'] as $profil) {
                $token = $request->cookie('ctt_' . $profil . '_token');
                if ($token) {
                    $request->headers->set('Authorization', 'Bearer ' . $token);
                    break;
                }
            }
        }

        return $next($request);
    }
}
