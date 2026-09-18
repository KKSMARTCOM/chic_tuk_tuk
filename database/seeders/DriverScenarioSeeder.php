<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Driver;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Les dix formes de course de la matrice de visibilité, pour la vérification en ligne.
 *
 * ⚠️ RÉSERVÉ À STAGING. Jamais appelé par DatabaseSeeder ni par une commande planifiée :
 * il se lance à la main.
 *
 *     php artisan db:seed --class=DriverScenarioSeeder
 *
 * Sans argument, il LISTE les agents et demande lequel utiliser : personne ne connaît
 * un UUID de tête. On peut aussi le désigner par son e-mail, son nom ou son UUID :
 *
 *     SCENARIO_DRIVER=agent@exemple.bj php artisan db:seed --class=DriverScenarioSeeder
 *
 * ⚠️ AUCUNE FABRIQUE ICI, et ce n'est pas un choix de style.
 *
 * `Illuminate\Database\Eloquent\Factories\Factory::__construct` fait
 * `$this->faker = $this->withFaker()` INCONDITIONNELLEMENT : instancier une fabrique
 * résout `Faker\Generator` par le conteneur, que sa `definition()` appelle `fake()` ou
 * non. Or `fakerphp/faker` est en `require-dev` et le Dockerfile déploie avec
 * `composer install --no-dev` — d'où « Class "Faker\Factory" not found » levée depuis
 * `DatabaseServiceProvider`. Retirer `fake()` des définitions ne suffit donc pas : il
 * faut n'appeler aucune fabrique du tout.
 *
 * Les courses sont créées par `Booking::create()`, sur un socle explicite. La
 * correspondance avec les formes que `ListAvailableBookings` distingue est verrouillée
 * par `DriverScenarioSeederTest`, qui compare ce que le seeder produit à ce que l'action
 * montre réellement — les deux ne partageant aucun code, une divergence se verrait.
 *
 * ⚠️ Il refuse la production plutôt que d'autoriser une liste d'environnements :
 * `api-staging` tourne en réalité avec `APP_ENV=development`, et une liste blanche
 * `['local', 'staging']` l'aurait fait taire sur la seule machine où il sert.
 */
class DriverScenarioSeeder extends Seeder
{
    /** Repère posé dans `special_requests`, seul champ libre qu'aucune requête de visibilité ne lit. */
    private const MARQUE = '[SCENARIO]';

    public function run(): void
    {
        if (app()->isProduction() || app()->environment(['production', 'prod'])) {
            $this->command->error('DriverScenarioSeeder ne doit JAMAIS tourner en production.');

            return;
        }

        $driver = $this->resoudreAgent();

        if (! $driver) {
            return;
        }

        // Il faut un SECOND agent : c'est l'abonnement qui lui est lié qui prouve, en
        // ligne, que l'agent de test ne voit pas l'abonnement d'un autre.
        $autre = Driver::where('id', '!=', $driver->id)->first();

        if (! $autre) {
            $this->command->error(
                'Il faut au moins DEUX agents en base : le second porte l\'abonnement '
                .'qui doit rester invisible à l\'agent de test.'
            );

            return;
        }

        $this->creerLesFormes($driver, $autre);

        $total = Booking::where('special_requests', 'LIKE', self::MARQUE.'%')->count();

        $this->command->info("{$total} courses de scénario créées pour l'agent {$driver->id}.");
        $this->command->info('Elles portent « '.self::MARQUE.' » dans special_requests.');
        $this->command->info(
            'Pour les retirer : DELETE FROM bookings WHERE special_requests LIKE \''.self::MARQUE.'%\';'
        );
    }

    /**
     * Les douze créations : les dix formes de la matrice, plus la course aller déjà
     * acceptée qui porte la forme n°4, plus l'abonnement d'un autre agent.
     */
    private function creerLesFormes(Driver $driver, Driver $autre): void
    {
        // 1 — course unique aller simple : visible de tous.
        $this->course('unique_simple', ['pickup_time' => $this->heure(1)]);

        // 2 et 3 — course unique aller-retour, et sa course retour cachée sans titulaire.
        $allerRetour = $this->course('unique_aller_retour', [
            'pickup_time' => $this->heure(2),
            'round_trip' => true,
            'return_time' => '20:00',
        ]);
        $this->courseRetour('retour_cachee_libre', $allerRetour, ['pickup_time' => $this->heure(3)]);

        // 4 et 5 — un aller déjà accepté, et sa course retour réservée à cet agent.
        $allerPris = $this->course('aller_pris', [
            'pickup_time' => $this->heure(4),
            'round_trip' => true,
            'return_time' => '21:00',
            'status' => 'confirmed',
            'driver_id' => $driver->id,
        ]);
        $this->courseRetour('retour_cachee_prise', $allerPris, [
            'pickup_time' => $this->heure(5),
            'subscription_driver_id' => $driver->id,
        ]);

        // 6 — abonnement parent sans titulaire : visible de tous.
        $this->abonnement('abo_parent_libre', ['pickup_time' => $this->heure(6)]);

        // 7 — abonnement parent lié à l'agent de test : lui seul.
        $aboLie = $this->abonnement('abo_parent_lie', [
            'pickup_time' => $this->heure(7),
            'subscription_driver_id' => $driver->id,
        ]);

        // 8 — enfant d'abonnement lié à l'agent de test : lui seul.
        $this->course('abo_enfant_lie', [
            'pickup_time' => $this->heure(8),
            'parent_booking_id' => $aboLie->id,
            'subscription_driver_id' => $driver->id,
        ]);

        // 9 — enfant d'abonnement révoqué : redevenu libre.
        $this->course('abo_enfant_revoque', [
            'pickup_time' => $this->heure(9),
            'parent_booking_id' => $aboLie->id,
            'is_revoked' => true,
            'revoked_at' => now()->subHour(),
        ]);

        // 10 — course retour d'abonnement liée à l'agent de test.
        $this->courseRetour('abo_retour_lie', $aboLie, [
            'pickup_time' => $this->heure(10),
            'subscription_driver_id' => $driver->id,
        ]);

        // 11 — course retour d'abonnement révoquée : libre.
        $this->courseRetour('abo_retour_revoque', $aboLie, [
            'pickup_time' => $this->heure(11),
            'is_revoked' => true,
            'revoked_at' => now()->subHour(),
        ]);

        // 12 — l'abonnement d'un AUTRE agent. C'est lui qui prouve, en ligne, qu'aucune
        // course ne fuit : il ne doit jamais apparaître à l'agent de test.
        $this->abonnement('abo_d_un_autre_agent', [
            'pickup_time' => $this->heure(12),
            'subscription_driver_id' => $autre->id,
        ]);
    }

    /** Une heure de prise en charge distincte par forme, pour un ordre déterministe. */
    private function heure(int $n): string
    {
        return sprintf('%02d:00', 6 + $n);
    }

    /**
     * Le socle commun d'une course de scénario.
     *
     * Les sept colonnes NOT NULL sans défaut y figurent toutes : `from_location`,
     * `to_location`, `distance`, `base_price`, `total_price`, `pickup_date` et
     * `pickup_time`. `booking_number` en est absent — `Booking::boot()` l'écrase.
     *
     * @param  array<string, mixed>  $attributs
     */
    private function course(string $forme, array $attributs = []): Booking
    {
        return Booking::create(array_merge([
            'from_location' => 'Cadjehoun',
            'to_location' => 'Fidjrosse',
            'distance' => 5,
            'base_price' => 5000,
            'total_price' => 5000,
            'pickup_date' => now()->addDay()->toDateString(),
            'pickup_time' => '08:00',
            'phone' => '+22997000000',
            // `user_id` reste null : la colonne est nullable, et les écrans lisent
            // `client_name` en premier.
            'user_id' => null,
            'client_name' => 'Client de scénario',
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
            'commission' => 0,
            'driver_earning' => 0,
            'special_requests' => self::MARQUE.' '.$forme,
        ], $attributs));
    }

    /**
     * Une course retour cachée, rattachée à son aller : lieux inversés, heure de retour
     * du parent, et `trip_type = 'return'`.
     *
     * @param  array<string, mixed>  $attributs
     */
    private function courseRetour(string $forme, Booking $parent, array $attributs = []): Booking
    {
        return $this->course($forme, array_merge([
            'parent_booking_id' => $parent->id,
            'trip_type' => 'return',
            'round_trip' => true,
            'return_time' => null,
            'from_location' => $parent->to_location,
            'to_location' => $parent->from_location,
            'pickup_date' => $parent->pickup_date,
        ], $attributs));
    }

    /**
     * Un abonnement parent : `is_recurring` vrai et aucun parent.
     *
     * ⚠️ Ses ENFANTS, eux, portent `is_recurring = false` — c'est le parent qui est
     * récurrent. C'est exactement ce que `getIsSubscriptionChildAttribute()` vérifie.
     *
     * @param  array<string, mixed>  $attributs
     */
    private function abonnement(string $forme, array $attributs = []): Booking
    {
        return $this->course($forme, array_merge([
            'is_recurring' => true,
            'parent_booking_id' => null,
            'days' => 20,
            'remaining_days' => 20,
            'week_days' => 'lun_ven',
            'subscription_end_date' => now()->addDays(30)->toDateString(),
            'next_recurring_date' => now()->addDay(),
        ], $attributs));
    }

    /**
     * L'agent de test, désigné par UUID, e-mail ou nom — ou choisi dans une liste.
     *
     * Le seeder demandait un UUID et rien d'autre, ce qui le rendait inutilisable :
     * personne n'a un UUID d'agent en tête. Un outil réservé au diagnostic doit se
     * lancer sans préparation.
     */
    private function resoudreAgent(): ?Driver
    {
        $recherche = env('SCENARIO_DRIVER') ?: env('SCENARIO_DRIVER_ID');

        if ($recherche) {
            $driver = $this->chercher($recherche);

            if (! $driver) {
                $this->command->error("Aucun agent ne correspond à « {$recherche} ».");

                return null;
            }

            return $driver;
        }

        $agents = Driver::with('user')->get();

        if ($agents->isEmpty()) {
            $this->command->error('Aucun agent en base. Créez-en un avant de jouer ce seeder.');

            return null;
        }

        $this->command->info('Agents disponibles :');
        foreach ($agents as $agent) {
            $this->command->line(sprintf(
                '  %s  %-30s %s',
                $agent->id,
                $agent->user?->name ?? '(sans nom)',
                $agent->user?->email ?? ''
            ));
        }

        $choix = $this->command->ask('Lequel ? (UUID, e-mail ou nom)');

        return $choix ? $this->chercher($choix) : null;
    }

    /**
     * Cherche un agent par UUID, e-mail ou nom.
     *
     * ⚠️ La colonne `drivers.id` est de type `uuid`, et PostgreSQL est STRICT : lui
     * comparer une adresse e-mail ne rend pas « aucun résultat », cela lève
     * « invalid input syntax for type uuid » et fait échouer toute la requête, y compris
     * la partie qui aurait trouvé. D'où le test d'UUID avant la clause.
     */
    private function chercher(string $valeur): ?Driver
    {
        return Driver::query()
            ->when(Str::isUuid($valeur), fn ($q) => $q->orWhere('id', $valeur))
            ->orWhereHas('user', fn ($q) => $q->where('email', $valeur)->orWhere('name', $valeur))
            ->first();
    }
}
