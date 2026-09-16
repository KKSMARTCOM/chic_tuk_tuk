<?php

namespace App\Domains\Identity\Presentation\Api\V1;

use App\Domains\Identity\Application\Actions\ChangePassword;
use App\Domains\Identity\Application\Actions\ResetPassword;
use App\Domains\Identity\Application\Actions\SendPasswordResetLinks;
use App\Domains\Identity\Application\Data\ChangePasswordData;
use App\Domains\Identity\Application\Data\ForgotPasswordData;
use App\Domains\Identity\Application\Data\ResetPasswordData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PasswordController
{
    public function change(ChangePasswordData $data, Request $request, ChangePassword $change): Response
    {
        $change($request->user(), $data);

        return response()->noContent();
    }

    public function forgot(ForgotPasswordData $data, SendPasswordResetLinks $send): JsonResponse
    {
        $send($data);

        // Réponse volontairement identique, que l'adresse existe ou non : sinon
        // l'endpoint devient un test d'existence de compte.
        return response()->json([
            'message' => "Si un compte existe pour cette adresse, un email vient d'être envoyé.",
        ]);
    }

    public function reset(ResetPasswordData $data, ResetPassword $reset): Response
    {
        $reset($data);

        return response()->noContent();
    }
}
