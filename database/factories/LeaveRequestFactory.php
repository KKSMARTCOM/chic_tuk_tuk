<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        return [
            'driver_id' => Driver::factory(),
            'driver_contract_id' => null,
            'start_date' => now()->subDays(5),
            'end_date' => now()->subDays(1),
            'requested_days' => 5,
            'effective_days' => 5,
            // Contrainte CHECK `leave_requests_status_check` : pending, rejected,
            // ongoing, completed. `approved` n'existe plus depuis la migration du
            // 12 août — l'y mettre ferait échouer l'insertion.
            'status' => 'completed',
            'source' => 'admin_historical',
        ];
    }

    /** Congé toujours en cours : `end_date` nulle, `status` à `ongoing`. */
    public function ongoing(): static
    {
        return $this->state(fn () => ['status' => 'ongoing', 'end_date' => null]);
    }
}
