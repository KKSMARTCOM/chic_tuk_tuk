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

    public function calculatePrice(Request $request, string $fromZoneId, string $toZoneId)
    {
        try {
            $basePrice = $this->pricingService->getPrice($fromZoneId, $toZoneId);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }

        $days = (int) $request->query('days', 1);

        if ($days < 1) {
            $days = 1;
        }

        // Placeholder for future promo/discount logic
        $discount = 0;

        $totalPrice = $basePrice /* * $days */ - $discount;
        if ($totalPrice < 0) {
            $totalPrice = 0;
        }

        return response()->json([
            'base_price' => (int) $basePrice,
            'days' => $days,
            'discount' => (int) $discount,
            'total_price' => (int) $totalPrice,
        ]);
    }
}
