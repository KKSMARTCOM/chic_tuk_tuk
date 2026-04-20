<?php

namespace App\Services;

use App\Models\Pricing;
use Illuminate\Support\Facades\Http;

class PricingService
{
    public function getDistance(float $from_lng, float $from_lat, float $to_lng, float $to_lat): float
    {
        // Calcul de la distance
        $response = Http::withHeaders([
            'Authorization' => config('services.openrouteservice.key'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openrouteservice.org/v2/directions/driving-car', [
            'coordinates' => [
                [(float)$from_lng, (float)$from_lat],
                [(float)$to_lng, (float)$to_lat],
            ]
        ]);

        if (!$response->successful()) {
            throw new \Exception('Erreur lors du calcul de l\'itinéraire.');
        }

        $data = $response->json();
        $distance = $data['routes'][0]['summary']['distance'] / 1000;

        return $distance;
    }

    public function getPrice(float $distance): int
    {
        $price_km = 200;

        $commission = 200;

        $price = ($distance * $price_km) + $commission;

        $roundedPrice = ceil($price / 50) * 50;

        return $roundedPrice;
    }
}
