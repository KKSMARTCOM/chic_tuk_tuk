<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\DriverContract;
use App\Models\Vehicle;
use App\Models\VehicleContract;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DriverContract>
 */
class DriverContractFactory extends Factory
{
    protected $model = DriverContract::class;

    public function definition(): array
    {
        return [
            'driver_id' => Driver::factory(),
            'vehicle_id' => Vehicle::factory(),
            'vehicle_contract_id' => VehicleContract::factory(),
            'start_date' => now()->subMonths(6)->startOfMonth(),
            'end_date' => null,
            'contract_months' => 24,
            'status' => 'active',
        ];
    }

    /** Cohérence : le contrat agent porte sur le véhicule du contrat véhicule. */
    public function forVehicleContract(VehicleContract $contract): static
    {
        return $this->state(fn () => [
            'vehicle_contract_id' => $contract->id,
            'vehicle_id' => $contract->vehicle_id,
        ]);
    }
}
