<?php

namespace Database\Factories;

use App\Consts\VehicleContractConsts;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleContract;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleContract>
 */
class VehicleContractFactory extends Factory
{
    protected $model = VehicleContract::class;

    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'owner_id' => User::factory(),
            'total_amount' => VehicleContractConsts::TOTAL_AMOUNTS[24],
            'monthly_payment' => 130_000,
            'contract_months' => 24,
            'start_date' => now()->subMonths(6)->startOfMonth(),
            'end_date' => null,
            'status' => 'active',
            'unlimited_internet' => VehicleContractConsts::DEFAULT_UNLIMITED_INTERNET,
            'spotify_premium' => VehicleContractConsts::DEFAULT_SPOTIFY_PREMIUM,
            'manager_remuneration' => VehicleContractConsts::DEFAULT_MANAGER_REMUNERATION,
        ];
    }

    /**
     * Rattache le contrat à un véhicule ET reprend son propriétaire.
     *
     * Sans cela, `owner_id` du contrat et `owner_id` du véhicule désignent deux
     * personnes différentes. Cela n'arrive jamais en production, et fausserait
     * silencieusement les tests de portée de l'API.
     */
    public function forVehicle(Vehicle $vehicle): static
    {
        return $this->state(fn () => [
            'vehicle_id' => $vehicle->id,
            'owner_id' => $vehicle->owner_id,
        ]);
    }

    /** Contrat terminé : `status` hors `active`, donc invisible d'`activeVehicleContract`. */
    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'end_date' => now()->subDay(),
        ]);
    }
}
