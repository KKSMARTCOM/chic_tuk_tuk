<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
        $schedule->command('app:expire-bookings')->dailyAt('01:00')->appendOutputTo(storage_path('logs/commands.log'));
        $schedule->command('app:process-recurring-bookings')->dailyAt('01:00')->appendOutputTo(storage_path('logs/commands.log'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            $code = $e->getStatusCode();

            if (view()->exists("errors.$code")) {
                return response()->view("errors.$code", [], $code);
            }
        });
    })->create();
