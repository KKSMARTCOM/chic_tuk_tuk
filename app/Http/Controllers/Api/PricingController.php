<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PricingService;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    protected $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    public function calculatePrice(Request $request)
    {
        try {
            $distance = $this->pricingService->getDistance($request->fromLng, $request->fromLat, $request->toLng, $request->toLat);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }

        $days = (int) $request->query('days', 1);

        if ($days < 1) {
            $days = 1;
        }

        // Placeholder for future promo/discount logic
        $discount = 0;

        $price = $this->pricingService->getPrice($distance);

        return response()->json([
            'distance' => $distance,
            'days' => $days,
            'price' =>  $price,
        ]);
    }
}
