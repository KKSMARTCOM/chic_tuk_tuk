<?php

namespace App\Shared\Http\Middleware;

use App\Shared\Http\ApiException;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Invalide les jetons d'API laissés inutilisés trop longtemps.
 *
 * Sanctum sait expirer un jeton à date fixe (colonne expires_at, posée à la création
 * comme plafond absolu), mais pas selon l'inactivité. Ce middleware ajoute la fenêtre
 * glissante : un agent qui travaille tous les jours n'est jamais déconnecté, un jeton
 * abandonné sur un poste public meurt de lui-même.
 *
 * ⚠️ À n'appliquer QUE sur les routes d'API. Le réglage global `sanctum.expiration`
 * aurait touché les jetons du chemin Blade, encore en service.
 *
 * ⚠️ Ce middleware doit être placé AVANT `auth:sanctum`, et résout donc le jeton
 * lui-même au lieu de passer par $request->user(). Raison : le garde de Sanctum écrit
 * `last_used_at` à `now()` PENDANT l'authentification, avant que le middleware suivant
 * ne s'exécute. Lu après le garde, `last_used_at` vaut toujours « à l'instant » et la
 * fenêtre d'inactivité n'expirerait jamais personne. Le coût est une recherche
 * indexée de plus, la même que celle que le garde fera juste après.
 */
class EnforceTokenFreshness
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();

        // Pas de jeton, ou jeton inconnu : rien à vérifier ici. C'est auth:sanctum,
        // juste après, qui refusera la requête.
        if ($bearer !== null && ($token = PersonalAccessToken::findToken($bearer)) !== null) {
            // Les jetons du chemin Blade n'ont jamais de last_used_at, d'où le repli.
            $reference = $token->last_used_at ?? $token->created_at;
            $limit = now()->subDays((int) config('identity.token.inactivity_days'));

            if ($reference !== null && $reference->lessThan($limit)) {
                $token->delete();

                throw new ApiException(
                    status: 401,
                    errorCode: 'TOKEN_EXPIRED',
                    message: 'Session expirée. Reconnectez-vous.',
                );
            }
        }

        return $next($request);
    }
}
