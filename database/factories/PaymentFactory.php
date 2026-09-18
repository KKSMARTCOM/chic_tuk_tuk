<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\Payment;
use App\Models\VehicleContract;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            // NOT NULL avec clé étrangère vers `drivers` : un paiement sans agent n'est
            // pas insérable, même quand le test ne s'intéresse qu'au contrat véhicule.
            'driver_id' => Driver::factory(),
            'vehicle_contract_id' => VehicleContract::factory(),
            'driver_contract_id' => null,
            'payment_type' => 'contract',
            'status' => 'completed',
            'amount' => 6112,
            'net_amount' => 6112,
            'payment_method' => 'cash',
            'payment_date' => now()->startOfMonth(),
            'payment_month' => now()->startOfMonth(),
        ];
    }

    /** Un paiement à une date précise, rattaché au mois de cette date. */
    public function onDay(string $date): static
    {
        return $this->state(fn () => [
            'payment_date' => $date,
            'payment_month' => Carbon::parse($date)->startOfMonth(),
        ]);
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
