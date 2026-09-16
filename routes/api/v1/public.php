<?php

use App\Domains\Booking\Presentation\Api\V1\Public\BookingController;
use App\Domains\Booking\Presentation\Api\V1\Public\PricingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 — routes publiques (landing)
|--------------------------------------------------------------------------
|
| Endpoints anonymes consommés par chictuktuk.com. Sans session ni jeton CSRF,
| ils sont appelables depuis n'importe où : le throttling n'est donc pas une
| option de confort.
|
|   - /pricing/quote consomme le quota OpenRouteService pour chaque nouveau
|     trajet (la distance est ensuite en cache) ; sans limite, faire varier les
|     coordonnées suffirait à l'épuiser ;
|   - /bookings crée des courses visibles par les agents ; sans limite, elle
|     permettrait d'inonder l'application de réservations fantômes.
|
*/

Route::prefix('public')->name('public.')->group(function () {
    // GET volontairement : un devis est une lecture, et une requête GET sans en-tête
    // personnalisé n'entraîne aucun préflight CORS — le prix est recalculé à chaque
    // sélection de ville, l'aller-retour économisé est perceptible sur mobile.
    Route::get('/pricing/quote', [PricingController::class, 'quote'])
        // 60/min : le front redemande un devis à chaque changement d'horaire, et les
        // opérateurs mobiles béninois partagent souvent une même IP entre de nombreux
        // abonnés (CGNAT). Le coût réel est borné par le cache de distance.
        ->middleware('throttle:60,1')
        ->name('pricing.quote');

    Route::post('/bookings', [BookingController::class, 'store'])
        ->middleware('throttle:10,60')
        ->name('bookings.store');
});
