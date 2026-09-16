<?php

namespace App\Shared\Http;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Traduit les exceptions en réponses JSON normalisées pour l'API.
 *
 * Enveloppe d'erreur :
 *   { "message": "...", "code": "VALIDATION_FAILED", "errors": { "champ": ["..."] } }
 *
 * `errors` n'est présent que pour les erreurs de validation. `code` est un identifiant
 * stable destiné au front (à la différence de `message`, qui est destiné à l'utilisateur
 * et peut évoluer).
 *
 * Contrairement au rendu web historique, ce rendu ne détruit JAMAIS la session ni les
 * tokens : une 403 ou une 422 ne doit pas déconnecter l'appelant.
 */
final class ApiExceptionRenderer
{
    public static function render(Throwable $e, bool $debug = false): JsonResponse
    {
        // En tête : une ApiException porte déjà son statut, son code et ses champs
        // supplémentaires. Rien à traduire, contrairement aux exceptions du framework.
        if ($e instanceof ApiException) {
            $payload = array_merge(
                ['message' => $e->getMessage(), 'code' => $e->errorCode],
                $e->extra,
            );

            $response = response()->json($payload, $e->status);

            foreach ($e->headers as $header => $value) {
                $response->headers->set($header, $value);
            }

            return $response;
        }

        if ($e instanceof ValidationException) {
            return self::json(422, 'VALIDATION_FAILED', $e->getMessage(), $e->errors());
        }

        if ($e instanceof AuthenticationException) {
            return self::json(401, 'UNAUTHENTICATED', 'Authentification requise.');
        }

        if ($e instanceof AuthorizationException) {
            return self::json(403, 'FORBIDDEN', $e->getMessage() ?: 'Action non autorisée.');
        }

        if ($e instanceof ModelNotFoundException) {
            return self::json(404, 'NOT_FOUND', 'Ressource introuvable.');
        }

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();

            // Sur 404, 405 et 429, le message vient du framework : il est en anglais
            // (« Too Many Attempts. ») et, pour 404/405, divulgue les chemins de l'API.
            // On lui préfère le message générique. Ailleurs, un message explicite passé
            // à abort() est conservé.
            $message = in_array($status, [404, 405, 429], true)
                ? self::messageForStatus($status)
                : ($e->getMessage() ?: self::messageForStatus($status));

            $response = self::json($status, self::codeForStatus($status), $message);

            // Conserve Retry-After sur les 429, le front en a besoin pour temporiser.
            foreach ($e->getHeaders() as $header => $value) {
                $response->headers->set($header, $value);
            }

            return $response;
        }

        return self::json(
            500,
            'SERVER_ERROR',
            $debug ? $e->getMessage() : 'Une erreur interne est survenue.',
            $debug ? ['exception' => [$e::class . ' @ ' . $e->getFile() . ':' . $e->getLine()]] : null,
        );
    }

    private static function json(int $status, string $code, string $message, ?array $errors = null): JsonResponse
    {
        $payload = ['message' => $message, 'code' => $code];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    private static function codeForStatus(int $status): string
    {
        return match ($status) {
            401 => 'UNAUTHENTICATED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            405 => 'METHOD_NOT_ALLOWED',
            419 => 'SESSION_EXPIRED',
            422 => 'VALIDATION_FAILED',
            423 => 'ACCOUNT_LOCKED',
            429 => 'TOO_MANY_REQUESTS',
            default => $status >= 500 ? 'SERVER_ERROR' : 'REQUEST_ERROR',
        };
    }

    private static function messageForStatus(int $status): string
    {
        return match ($status) {
            401 => 'Authentification requise.',
            403 => 'Action non autorisée.',
            404 => 'Ressource introuvable.',
            405 => 'Méthode HTTP non autorisée.',
            429 => 'Trop de requêtes. Réessayez dans quelques instants.',
            default => $status >= 500 ? 'Une erreur interne est survenue.' : 'Requête invalide.',
        };
    }
}
