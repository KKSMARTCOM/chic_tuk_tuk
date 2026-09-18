<?php

namespace Database\Factories;

use App\Domains\Identity\Domain\Enums\Profil;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            // Un véhicule appartient toujours à un utilisateur de profil `owner` :
            // c'est ce que la garde de l'API vérifie.
            'owner_id' => User::factory()->profil(Profil::Owner),
            // `vehicle_number` est UNIQUE en base. Sans `unique()`, deux véhicules d'un
            // même test se heurtent une fois de temps en temps, et le test devient
            // capricieux au lieu d'être faux — le pire des deux.
            'vehicle_number' => fake()->unique()->numerify('T-####'),
            'vehicle_type' => 'tricycle',
            'is_active' => true,
        ];
    }
}
