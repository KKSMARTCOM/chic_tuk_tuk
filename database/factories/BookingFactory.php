<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Les dix formes de course que la matrice de visibilité distingue.
 *
 * Volontairement en états nommés et non en champs à composer : la visibilité d'une
 * course dépend de six colonnes à la fois, et une combinaison écrite à la main dans un
 * test se trompe sans bruit — la course n'apparaît simplement pas là où on l'attendait,
 * et le test conclut à une régression qui n'existe pas.
 *
 * ⚠️ AUCUN appel à `fake()` ici, et ce n'est pas un choix de style.
 *
 * `fakerphp/faker` est une dépendance de DÉVELOPPEMENT, et le Dockerfile déploie avec
 * `composer install --no-dev` : `fake()` lève « Class "Faker\Factory" not found » dès
 * qu'on quitte le poste de développement. Or `DriverScenarioSeeder` s'appuie sur cette
 * fabrique et doit tourner sur staging. Constaté le 2026-09-18, en production de
 * l'erreur exacte.
 *
 * Les valeurs varient par un compteur plutôt que par un générateur aléatoire — ce qui
 * rend au passage les tests reproductibles.
 *
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /** Fait varier les libellés sans tirer au sort, et sans Faker. */
    private static int $compteur = 0;

    public function definition(): array
    {
        $n = ++self::$compteur;

        return [
            'user_id' => User::factory(),
            // Les sept colonnes NOT NULL sans défaut. `booking_number` n'y figure pas :
            // Booking::boot() l'écrase à la création, le poser ici ne sert à rien.
            'from_location' => "Lieu de départ {$n}",
            'to_location' => "Lieu d'arrivée {$n}",
            'distance' => 5,
            'base_price' => 5000,
            'total_price' => 5000,
            'pickup_date' => now()->addDay()->toDateString(),
            'pickup_time' => '08:00',
            // Les colonnes à défaut, répétées pour que chaque état parte d'un socle
            // explicite plutôt que du défaut de la base.
            'phone' => '+22997000000',
            'client_name' => "Client {$n}",
            'status' => 'pending',
            'days' => 1,
            'remaining_days' => 1,
            'trip_type' => 'go',
            'round_trip' => false,
            'is_recurring' => false,
            'is_revoked' => false,
            'parent_booking_id' => null,
            'subscription_driver_id' => null,
            'driver_id' => null,
            // Posés EXPLICITEMENT alors que la base a déjà DEFAULT 0. Sans cela, le
            // modèle renvoyé par create() ne porte pas l'attribut — les valeurs par
            // défaut de PostgreSQL ne sont pas hydratées — et `$booking->commission`
            // vaut null en PHP tant qu'on n'a pas fait `fresh()`. Un test écrit là-dessus
            // passerait ou échouerait selon qu'il a rechargé le modèle, ce qui est le
            // genre de caprice qu'on met une heure à comprendre.
            'commission' => 0,
            'driver_earning' => 0,
        ];
    }

    // ----- Statuts -------------------------------------------------------------

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending', 'driver_id' => null]);
    }

    /** Course acceptée par un agent : c'est `driver_id` qui la lui rattache. */
    public function confirmed(Driver $driver): static
    {
        return $this->state(fn () => ['status' => 'confirmed', 'driver_id' => $driver->id]);
    }

    public function inProgress(Driver $driver): static
    {
        return $this->state(fn () => [
            'status' => 'in_progress',
            'driver_id' => $driver->id,
            'started_at' => now()->subMinutes(20),
        ]);
    }

    public function completed(Driver $driver): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'driver_id' => $driver->id,
            'started_at' => now()->subHour(),
            'completed_at' => now()->subMinutes(30),
            'commission' => 750,
            'driver_earning' => 4250,
        ]);
    }

    /**
     * Course annulée.
     *
     * `commission` et `driver_earning` restent à 0 : ce sont des colonnes NOT NULL
     * DEFAULT 0, jamais nulles. Une course annulée avant `complete()` porte donc bien
     * un gain de 0, et c'est ce que l'API doit renvoyer.
     */
    public function cancelled(Driver $driver): static
    {
        return $this->state(fn () => [
            'status' => 'cancelled',
            'driver_id' => $driver->id,
            'cancelled_at' => now()->subHour(),
            'cancellation_reason' => 'Client injoignable',
        ]);
    }

    // ----- Formes de la matrice de visibilité ----------------------------------

    /** Forme 2 : course unique aller, avec aller-retour annoncé. */
    public function roundTrip(string $returnTime = '18:00'): static
    {
        return $this->state(fn () => ['round_trip' => true, 'return_time' => $returnTime]);
    }

    /** Formes 3 et 4 : abonnement parent. Sans titulaire par défaut. */
    public function subscriptionParent(): static
    {
        return $this->state(fn () => [
            'is_recurring' => true,
            'parent_booking_id' => null,
            'trip_type' => 'go',
            'days' => 20,
            'remaining_days' => 20,
            'week_days' => 'lun_ven',
            'subscription_end_date' => now()->addDays(30)->toDateString(),
            'next_recurring_date' => now()->addDay(),
        ]);
    }

    /**
     * Formes 5 et 6 : course enfant d'un abonnement.
     *
     * ⚠️ `is_recurring` vaut FALSE sur un enfant — c'est le parent qui est récurrent.
     * C'est exactement ce que `getIsSubscriptionChildAttribute()` vérifie.
     */
    public function subscriptionChild(Booking $parent): static
    {
        return $this->state(fn () => [
            'parent_booking_id' => $parent->id,
            'is_recurring' => false,
            'trip_type' => 'go',
            'user_id' => $parent->user_id,
        ]);
    }

    /** Formes 7, 8 et 9 : course retour cachée, rattachée à son aller. */
    public function returnOf(Booking $parent): static
    {
        return $this->state(fn () => [
            'parent_booking_id' => $parent->id,
            'is_recurring' => false,
            'trip_type' => 'return',
            'round_trip' => true,
            'return_time' => null,
            'from_location' => $parent->to_location,
            'to_location' => $parent->from_location,
            'pickup_date' => $parent->pickup_date,
            'pickup_time' => $parent->return_time ?? '18:00',
            'user_id' => $parent->user_id,
        ]);
    }

    /**
     * Rattache la course à un agent d'abonnement.
     *
     * Distinct de `confirmed()` : `subscription_driver_id` réserve une course qui reste
     * `pending`, alors que `driver_id` désigne l'agent qui l'a acceptée. Les confondre
     * fait basculer toute la matrice.
     */
    public function linkedToSubscriptionDriver(Driver $driver): static
    {
        return $this->state(fn () => ['subscription_driver_id' => $driver->id]);
    }

    /** Forme 8 bis et 10 : course d'abonnement révoquée, redevenue libre. */
    public function revoked(): static
    {
        return $this->state(fn () => [
            'is_revoked' => true,
            'revoked_at' => now()->subHour(),
            'subscription_driver_id' => null,
            'driver_id' => null,
            'status' => 'pending',
        ]);
    }
}
