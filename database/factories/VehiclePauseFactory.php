<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\VehicleContract;
use App\Models\VehiclePause;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehiclePause>
 */
class VehiclePauseFactory extends Factory
{
    protected $model = VehiclePause::class;

    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            // NOT NULL en base, contrairement à `driver_contract_id`.
            'vehicle_contract_id' => VehicleContract::factory(),
            'driver_contract_id' => null,
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDays(5),
            // Une des six clés de VehiclePause::$reasonTypes, d'où l'accesseur
            // `reason_label` tire « Problème technique ».
            'reason_type' => 'technical',
            'reason_notes' => null,
            'is_auto' => false,
        ];
    }

    public function forContract(VehicleContract $contract): static
    {
        return $this->state(fn () => [
            'vehicle_contract_id' => $contract->id,
            'vehicle_id' => $contract->vehicle_id,
        ]);
    }

    /** Pause toujours ouverte : `end_date` nulle, ce qui la rend « active ». */
    public function ongoing(): static
    {
        return $this->state(fn () => ['end_date' => null]);
    }
}
