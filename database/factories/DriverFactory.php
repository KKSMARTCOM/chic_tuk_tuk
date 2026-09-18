<?php

namespace Database\Factories;

use App\Domains\Identity\Domain\Enums\Profil;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Driver>
 */
class DriverFactory extends Factory
{
    protected $model = Driver::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->profil(Profil::Driver),
            // UNIQUE en base, comme `vehicle_number`.
            'license_number' => fake()->unique()->numerify('P-######'),
            'is_available' => true,
            'rating' => 0,
            'total_trips' => 0,
        ];
    }
}
