<?php

use App\Shared\Http\ApiExceptionRenderer;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Derrière Traefik (Coolify) : sans ceci, Laravel lit l'IP du proxy au lieu de
        // celle du client, ce qui fausse le rate limiting par IP d'AuthService.
        $middleware->trustProxies(at: '*');

        $middleware->prepend(\App\Http\Middleware\InjectSanctumTokenFromCookie::class);

        $middleware->alias([
            'guest'         => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'role'          => \App\Http\Middleware\CheckRole::class,
            'permission'    => \App\Http\Middleware\CheckPermission::class,
            'profil'        => \App\Http\Middleware\CheckProfil::class,
        ]);
    })
    ->withSchedule(function ($schedule) {
        $schedule->command('app:expire-bookings')->dailyAt('01:00')->appendOutputTo(storage_path('logs/commands.log'));
        $schedule->command('app:process-recurring-bookings')->dailyAt('01:00')->appendOutputTo(storage_path('logs/commands.log'));
        $schedule->command('app:generate-daily')->weekdays()->dailyAt('23:30')->appendOutputTo(storage_path('logs/commands.log'));
        $schedule->command('app:activate-leave-pauses')->everyTwoHours()->appendOutputTo(storage_path('logs/commands.log'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            // ----------------------------------------------------------------
            // API : toujours du JSON, jamais de destruction de session.
            // ----------------------------------------------------------------
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiExceptionRenderer::render($e, (bool) config('app.debug'));
            }

            // ----------------------------------------------------------------
            // Web (Blade) : purge de session, restreinte aux cas légitimes.
            //
            // Historiquement déclenchée sur toute HttpException hors 404/500, ce qui
            // déconnectait l'utilisateur sur une simple 403 (permission refusée) ou
            // une 429 (rate limit). Seules une authentification absente (401) ou une
            // session expirée (419) justifient de révoquer les jetons.
            // ----------------------------------------------------------------
            if ($e instanceof HttpException) {
                $code = $e->getStatusCode();

                if (in_array($code, [401, 419], true)) {
                    foreach (['admin', 'driver', 'client', 'owner'] as $profil) {
                        $cookieName = 'ctt_' . $profil . '_token';

                        if ($rawToken = $request->cookie($cookieName)) {
                            PersonalAccessToken::findToken($rawToken)?->delete();
                            Cookie::queue(Cookie::forget($cookieName));
                        }
                    }

                    // Seul le guard "web" (session PHP) supporte logout().
                    if (Auth::guard('web')->check()) {
                        Auth::guard('web')->logout();
                    }

                    try {
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();
                    } catch (\Exception $sessionException) {
                        // La session peut déjà être invalide selon le contexte.
                    }
                }

                if (view()->exists("errors.$code")) {
                    return response()->view("errors.$code", [], $code);
                }
            }

            return null;
        });
    })->create();
