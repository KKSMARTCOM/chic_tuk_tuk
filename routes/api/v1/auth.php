<?php

use App\Domains\Identity\Presentation\Api\V1\AuthController;
use App\Domains\Identity\Presentation\Api\V1\PasswordController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 — authentification
|--------------------------------------------------------------------------
|
| Jeton Bearer Sanctum, sans session ni cookie posé par l'API. Le throttle par IP
| reste volontairement large : les opérateurs mobiles béninois partagent une IP
| entre de nombreux abonnés (CGNAT), donc une limite serrée punirait des
| utilisateurs innocents sans gêner un attaquant distribué. La vraie défense est
| le verrou de compte (cf. config/identity.php).
|
*/

Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:30,1')
        ->name('login');

    Route::post('/password/forgot', [PasswordController::class, 'forgot'])
        ->middleware('throttle:10,60')
        ->name('password.forgot');

    Route::post('/password/reset', [PasswordController::class, 'reset'])
        ->middleware('throttle:10,60')
        ->name('password.reset');

    // token.fresh applique la fenêtre d'inactivité glissante. Il n'est posé QUE sur
    // ce groupe : le réglage global de Sanctum aurait touché les jetons du Blade.
    // Il passe AVANT auth:sanctum, car le garde écrit last_used_at à now() pendant
    // l'authentification : lu après, il vaudrait toujours « à l'instant ».
    Route::middleware(['token.fresh', 'auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::post('/password', [PasswordController::class, 'change'])->name('password.change');
    });
});
