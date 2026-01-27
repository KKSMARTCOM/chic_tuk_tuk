<?php

namespace App\Services;

use App\Models\Pricing;

class PricingService
{
    public function getPrice(string $fromZoneId, string $toZoneId)
    {
        $price = Pricing::where('from_zone_id', $fromZoneId)
            ->where('to_zone_id', $toZoneId)
            ->first();

        if (! $price) {
            throw new \Exception("Aucun tarif défini pour ce trajet.");
        }

        return $price->base_price;
    }
}
