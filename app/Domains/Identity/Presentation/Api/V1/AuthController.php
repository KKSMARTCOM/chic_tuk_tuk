<?php

namespace App\Domains\Identity\Presentation\Api\V1;

use App\Domains\Identity\Application\Actions\AuthenticateUser;
use App\Domains\Identity\Application\Data\LoginData;
use App\Domains\Identity\Application\Data\UserData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthController
{
    public function login(LoginData $data, Request $request, AuthenticateUser $authenticate): JsonResponse
    {
        $issued = $authenticate($data, (string) $request->ip());

        return response()->json([
            'token' => $issued->plainTextToken,
            'user' => UserData::fromModel($issued->user),
        ]);
    }

    public function logout(Request $request): Response
    {
        // Seul le jeton courant : les autres appareils restent connectés.
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(UserData::fromModel($request->user()));
    }
}
