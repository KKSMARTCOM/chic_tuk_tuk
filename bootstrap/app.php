<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Laravel\Sanctum\PersonalAccessToken;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(\App\Http\Middleware\InjectSanctumTokenFromCookie::class);

        $middleware->alias([
            'guest'         => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'role'          => \App\Http\Middleware\CheckRole::class,
            'permission'    => \App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withSchedule(function ($schedule) {
        $schedule->command('app:expire-bookings')/* ->dailyAt('01:00') */->appendOutputTo(storage_path('logs/commands.log'));
        $schedule->command('app:process-recurring-bookings')/* ->dailyAt('01:00') */->appendOutputTo(storage_path('logs/commands.log'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            $code = $e->getStatusCode();

            if (!in_array($code, [404, 500], true)) {

                // Supprimer tous les cookies de session Sanctum
                foreach (['admin', 'driver', 'client'] as $profil) {
                    $cookieName = 'ctt_' . $profil . '_token';

                    if ($request->cookie($cookieName)) {
                        // Révoquer le token en base si possible
                        $rawToken = $request->cookie($cookieName);
                        $pat = PersonalAccessToken::findToken($rawToken);
                        $pat?->delete();

                        Cookie::queue(Cookie::forget($cookieName));
                    }
                }

                // Déconnecter via le guard "web" (session PHP)
                // C'est le seul guard qui supporte logout()
                if (Auth::guard('web')->check()) {
                    Auth::guard('web')->logout();
                }

                // Invalider la session
                try {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                } catch (\Exception $sessionException) {
                    // Session peut déjà être invalide selon le contexte
                }
            }

            if (view()->exists("errors.$code")) {
                return response()->view("errors.$code", [], $code);
            }

            return null;
        });
    })->create();
