<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Driver;
use Illuminate\Database\Seeder;

/**
 * Les dix formes de course de la matrice de visibilité, pour la vérification en ligne.
 *
 * ⚠️ RÉSERVÉ À STAGING. Jamais appelé par DatabaseSeeder ni par une commande planifiée :
 * il se lance à la main.
 *
 *     SCENARIO_DRIVER_ID=<uuid> php artisan db:seed --class=DriverScenarioSeeder
 *
 * L'identifiant passe par une variable d'environnement et non par une option de ligne de
 * commande : `db:seed` ne déclare pas d'option `--driver`, et `$this->command
 * ->option('driver')` lèverait une InvalidArgumentException. À défaut, le seeder demande
 * interactivement.
 *
 * Il refuse de s'exécuter hors des environnements `local` et `staging` : un seeder qui
 * crée des courses `pending` en production les rendrait visibles d'agents réels.
 */
class DriverScenarioSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'staging'])) {
            $this->command->error('DriverScenarioSeeder est réservé à local et staging.');

            return;
        }

        $driverId = env('SCENARIO_DRIVER_ID') ?: $this->command->ask(
            'Identifiant de l\'agent de test (colonne drivers.id)',
        );

        $driver = Driver::findOrFail($driverId);
        $autre = Driver::where('id', '!=', $driver->id)->firstOrFail();

        $heure = fn (int $n) => sprintf('%02d:00', 6 + $n);
        $marque = fn (string $forme) => ['special_requests' => "[SCENARIO] {$forme}"];

        // 1 — course unique aller simple : visible de tous.
        Booking::factory()->create($marque('unique_simple') + ['pickup_time' => $heure(1)]);

        // 2 et 3 — course unique aller-retour, et sa course retour cachée sans titulaire.
        $allerRetour = Booking::factory()->roundTrip('20:00')
            ->create($marque('unique_aller_retour') + ['pickup_time' => $heure(2)]);
        Booking::factory()->returnOf($allerRetour)
            ->create($marque('retour_cachee_libre') + ['pickup_time' => $heure(3)]);

        // 4 — course retour cachée après acceptation de l'aller par l'agent de test.
        $allerPris = Booking::factory()->roundTrip('21:00')->confirmed($driver)
            ->create($marque('aller_pris') + ['pickup_time' => $heure(4)]);
        Booking::factory()->returnOf($allerPris)->linkedToSubscriptionDriver($driver)
            ->create($marque('retour_cachee_prise') + ['pickup_time' => $heure(5)]);

        // 5 — abonnement parent sans titulaire.
        Booking::factory()->subscriptionParent()
            ->create($marque('abo_parent_libre') + ['pickup_time' => $heure(6)]);

        // 6 — abonnement parent lié à l'agent de test.
        $aboLie = Booking::factory()->subscriptionParent()->linkedToSubscriptionDriver($driver)
            ->create($marque('abo_parent_lie') + ['pickup_time' => $heure(7)]);

        // 7 — enfant d'abonnement lié à l'agent de test.
        Booking::factory()->subscriptionChild($aboLie)->linkedToSubscriptionDriver($driver)
            ->create($marque('abo_enfant_lie') + ['pickup_time' => $heure(8)]);

        // 8 — enfant d'abonnement révoqué : redevenu libre.
        Booking::factory()->subscriptionChild($aboLie)->revoked()
            ->create($marque('abo_enfant_revoque') + ['pickup_time' => $heure(9)]);

        // 9 — course retour d'abonnement liée à l'agent de test.
        Booking::factory()->returnOf($aboLie)->linkedToSubscriptionDriver($driver)
            ->create($marque('abo_retour_lie') + ['pickup_time' => $heure(10)]);

        // 10 — course retour révoquée.
        Booking::factory()->returnOf($aboLie)->revoked()
            ->create($marque('abo_retour_revoque') + ['pickup_time' => $heure(11)]);

        // Un abonnement lié à QUELQU'UN D'AUTRE : c'est lui qui prouve, en ligne, que
        // l'agent de test ne voit pas l'abonnement d'un autre.
        Booking::factory()->subscriptionParent()->linkedToSubscriptionDriver($autre)
            ->create($marque('abo_d_un_autre_agent') + ['pickup_time' => $heure(12)]);

        $this->command->info('Onze courses de scénario créées pour l\'agent '.$driver->id);
        $this->command->info('Elles portent « [SCENARIO] » dans special_requests.');
    }
}
