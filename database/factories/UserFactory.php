<?php

namespace Database\Factories;

use App\Domains\Identity\Domain\Enums\Profil;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            // NOT NULL sans valeur par défaut : indispensable, sinon l'insertion échoue.
            'phone' => fake()->numerify('01######'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            // Sous contrainte CHECK users_profil_check : toujours une valeur de l'enum.
            'profil' => Profil::Client->value,
            'is_active' => true,
            'notification_preferences' => [],
            'failed_login_attempts' => 0,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function profil(Profil $profil): static
    {
        return $this->state(fn () => ['profil' => $profil->value]);
    }

    /** Compte verrouillé, comme après cinq échecs de connexion. */
    public function locked(?CarbonInterface $until = null): static
    {
        return $this->state(fn () => [
            'failed_login_attempts' => (int) config('identity.lock.max_attempts'),
            'locked_until' => $until ?? now()->addMinutes((int) config('identity.lock.minutes')),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
