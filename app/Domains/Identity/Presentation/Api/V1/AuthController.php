<?php

namespace App\Domains\Identity\Presentation\Api\V1;

use App\Domains\Identity\Application\Actions\AuthenticateUser;
use App\Domains\Identity\Application\Data\LoginData;
use App\Domains\Identity\Application\Data\UserData;
use App\Shared\Http\ApiException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chaque méthode attrape ses propres échecs, comme les contrôleurs Blade du projet,
 * mais renvoie du JSON.
 *
 * ⚠️ Deux règles à respecter dans chacun de ces try/catch :
 *
 *   1. `ValidationException` et `ApiException` sont relancées EN PREMIER. Elles sont la
 *      réponse voulue de l'API (422 d'identifiants, 423 de verrou, 409 d'ambiguïté,
 *      403 de compte désactivé) et `ApiExceptionRenderer` sait déjà les rendre. Les
 *      attraper avec le reste transformerait un refus légitime en 500.
 *   2. Le message de l'exception va au JOURNAL, jamais dans la réponse. Contrairement
 *      au chemin Blade où il finit en message flash, ici il part au client, et il
 *      contient régulièrement un fragment SQL ou un chemin de fichier.
 *   3. On attrape `\Throwable` et non `\Exception` : les `\Error` (TypeError,
 *      ValueError, DivisionByZeroError) n'héritent pas d'`Exception` et passeraient
 *      sous le nez du catch pour ressortir en `SERVER_ERROR` générique.
 */
final class AuthController
{
    public function login(LoginData $data, Request $request, AuthenticateUser $authenticate): JsonResponse
    {
        try {
            $issued = $authenticate($data, (string) $request->ip());

            return response()->json([
                'token' => $issued->plainTextToken,
                'user' => UserData::fromModel($issued->user),
            ]);
        } catch (ValidationException|ApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Erreur lors de la connexion : '.$e->getMessage(), [
                'exception' => $e,
                'email' => $data->email,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'La connexion a échoué pour une raison technique. Réessayez dans un instant.',
                'code' => 'LOGIN_FAILED',
            ], 500);
        }
    }

    public function logout(Request $request): Response
    {
        try {
            // Seul le jeton courant : les autres appareils restent connectés.
            $request->user()->currentAccessToken()->delete();

            return response()->noContent();
        } catch (ValidationException|ApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Erreur lors de la déconnexion : '.$e->getMessage(), [
                'exception' => $e,
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'message' => 'La déconnexion a échoué pour une raison technique. Réessayez.',
                'code' => 'LOGOUT_FAILED',
            ], 500);
        }
    }

    public function me(Request $request): JsonResponse
    {
        try {
            return response()->json(UserData::fromModel($request->user()));
        } catch (ValidationException|ApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Erreur lors de la lecture du profil courant : '.$e->getMessage(), [
                'exception' => $e,
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'message' => 'Votre profil n\'a pas pu être chargé. Réessayez.',
                'code' => 'PROFILE_READ_FAILED',
            ], 500);
        }
    }
}
