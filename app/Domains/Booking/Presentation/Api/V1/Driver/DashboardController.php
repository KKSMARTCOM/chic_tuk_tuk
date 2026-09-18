<?php

namespace App\Domains\Booking\Presentation\Api\V1\Driver;

use App\Domains\Booking\Application\Actions\BuildDriverDashboard;
use App\Domains\Booking\Application\Data\DriverDashboardData;
use App\Shared\Http\ApiException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/** GET /driver/dashboard — un contrôleur invocable, une seule action. */
final class DashboardController
{
    public function __invoke(Request $request, BuildDriverDashboard $build): JsonResponse
    {
        try {
            $driver = $request->user()?->driver;

            if (! $driver) {
                throw new ApiException(
                    409,
                    'DRIVER_PROFILE_MISSING',
                    'Votre compte agent est incomplet. Contactez un administrateur.',
                );
            }

            return response()->json(DriverDashboardData::fromStats($build($driver)));
        } catch (ApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Erreur sur le tableau de bord agent : '.$e->getMessage(), [
                'exception' => $e,
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'message' => 'Votre tableau de bord n\'a pas pu être chargé. Réessayez.',
                'code' => 'DRIVER_DASHBOARD_FAILED',
            ], 500);
        }
    }
}
