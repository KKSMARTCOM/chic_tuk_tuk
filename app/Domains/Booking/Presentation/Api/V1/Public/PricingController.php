<?php

namespace App\Domains\Booking\Presentation\Api\V1\Public;

use App\Domains\Booking\Application\Actions\QuotePrice;
use App\Domains\Booking\Application\Data\CalculatePriceData;
use App\Domains\Booking\Application\Data\PriceQuoteData;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

final class PricingController extends Controller
{
    /**
     * POST /api/v1/public/pricing/quote
     *
     * Endpoint public : la validation est déclenchée par l'injection de
     * CalculatePriceData, le throttling est posé sur la route.
     */
    public function quote(CalculatePriceData $data, QuotePrice $action): PriceQuoteData
    {
        try {
            return $action->execute($data);
        } catch (\Throwable $e) {
            // OpenRouteService injoignable, en quota ou itinéraire introuvable : ne pas
            // exposer le détail au public, mais le tracer pour le diagnostic.
            Log::error('Devis indisponible : ' . $e->getMessage(), ['exception' => $e]);

            throw new ServiceUnavailableHttpException(
                null,
                'Le calcul de l\'itinéraire est momentanément indisponible. Veuillez réessayer.',
            );
        }
    }
}
