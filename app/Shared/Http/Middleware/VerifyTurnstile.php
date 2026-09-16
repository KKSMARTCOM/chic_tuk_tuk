<?php

namespace App\Shared\Http\Middleware;

use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vérifie le jeton Cloudflare Turnstile joint à une requête publique.
 *
 * Le throttling par IP ne suffit pas sur les endpoints anonymes : les opérateurs
 * mobiles béninois partagent une même IP entre de nombreux abonnés (CGNAT), ce qui
 * interdit de la serrer, et un robot distribué la contourne de toute façon.
 *
 * L'échec est renvoyé comme une erreur de validation (422) portant sur le champ du
 * jeton : le front peut ainsi l'afficher à côté du widget, sans traitement spécial.
 */
final class VerifyTurnstile
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /** Champ attendu dans la requête, aussi bien en JSON qu'en formulaire. */
    public const FIELD = 'cf_turnstile_token';

    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) config('services.turnstile.secret', '');

        // Non configuré : développement local, tests, et staging tant que les clés
        // Cloudflare n'ont pas été créées. On ne bloque pas, le throttling reste actif.
        if ($secret === '') {
            return $next($request);
        }

        $token = trim((string) $request->input(self::FIELD, ''));

        if ($token === '') {
            $this->reject('Vérification anti-robot manquante. Rechargez la page, puis réessayez.');
        }

        try {
            $response = Http::asForm()->timeout(5)->post(self::VERIFY_URL, [
                'secret'   => $secret,
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);
        } catch (ConnectionException $e) {
            // Choix explicite : si Cloudflare est injoignable, on laisse passer plutôt
            // que de perdre des réservations réelles. Inverser ce comportement revient
            // à remplacer ce bloc par un appel à reject().
            Log::warning('Turnstile injoignable : vérification ignorée.', ['message' => $e->getMessage()]);

            return $next($request);
        }

        if ($response->successful() && $response->json('success') === true) {
            return $next($request);
        }

        Log::info('Turnstile a refusé une réservation publique.', [
            'ip'     => $request->ip(),
            'errors' => $response->json('error-codes', []),
        ]);

        $this->reject('Vérification anti-robot échouée. Rechargez la page, puis réessayez.');
    }

    private function reject(string $message): never
    {
        throw ValidationException::withMessages([self::FIELD => [$message]]);
    }
}
