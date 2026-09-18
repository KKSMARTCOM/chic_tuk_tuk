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
 * un UUID de tête, et la première tentative de lancement a échoué pour cette seule
 * raison. On peut aussi le désigner par son e-mail, son nom ou son UUID :
 *
 *     SCENARIO_DRIVER=agent@exemple.bj php artisan db:seed --class=DriverScenarioSeeder
 *
 * L'identifiant passe par une variable d'environnement et non par une option de ligne de
 * commande : `db:seed` ne déclare pas d'option `--driver`, et `$this->command
 * ->option('driver')` lèverait une InvalidArgumentException. À défaut, le seeder demande
 * interactivement.
 *
 * ⚠️ La garde REFUSE LA PRODUCTION au lieu d'autoriser une liste d'environnements. Une
 * liste blanche `['local', 'staging']` paraissait plus sûre et ne l'était pas :
 * `api-staging` tourne en réalité avec `APP_ENV=development`, donc le seeder s'y serait
 * tu poliment sans rien créer, et la vérification en ligne aurait été impossible à mener
 * sans qu'on comprenne pourquoi. Constaté sur la sonde de santé le 2026-09-18.
 *
 * Refuser explicitement ce qui est dangereux couvre les noms d'environnement qu'on ne
 * connaît pas ; autoriser une liste ne couvre que ceux auxquels on a pensé.
 */
class DriverScenarioSeeder extends Seeder
{
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
                .'qui doit rester invisible à l\'agent de test.',
            );

            return;
        }

        $heure = fn (int $n) => sprintf('%02d:00', 6 + $n);

        // ⚠️ `user_id` est fourni EXPLICITEMENT — ici null, la colonne étant nullable.
        // BookingFactory pose sinon `User::factory()`, une valeur paresseuse qui ne
        // s'évalue que faute de colonne fournie… et UserFactory, elle, appelle fake(),
        // absent hors développement. Le nom du client vient de `client_name`, que les
        // écrans lisent en premier.
        $marque = fn (string $forme) => [
            'special_requests' => "[SCENARIO] {$forme}",
            'user_id' => null,
            'client_name' => 'Client de scénario',
        ];

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

        // DOUZE créations, pour DIX formes : la matrice compte dix formes, auxquelles
        // s'ajoutent la course aller déjà acceptée qui porte la forme n°4, et
        // l'abonnement d'un autre agent qui doit rester invisible. Le compte annoncé
        // était de onze — il était faux, et un message faux trompe son lecteur.
        $total = Booking::where('special_requests', 'LIKE', '[SCENARIO]%')->count();

        $this->command->info("{$total} courses de scénario créées pour l'agent {$driver->id}.");
        $this->command->info('Elles portent « [SCENARIO] » dans special_requests.');
        $this->command->info('Pour les retirer : DELETE FROM bookings WHERE special_requests LIKE \'[SCENARIO]%\';');
    }

    /**
     * L'agent de test, désigné par UUID, e-mail ou nom — ou choisi dans une liste.
     *
     * Le seeder demandait un UUID et rien d'autre, ce qui le rendait inutilisable :
     * personne n'a un UUID d'agent en tête, et la première tentative de lancement a
     * échoué pour cette seule raison. Un outil réservé au diagnostic doit se lancer
     * sans préparation.
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
                $agent->user?->email ?? '',
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
     * la partie qui aurait trouvé. C'est pourquoi le test d'UUID précède la clause, au
     * lieu d'être laissé au moteur. Constaté le 2026-09-18.
     */
    private function chercher(string $valeur): ?Driver
    {
        return Driver::query()
            ->when(Str::isUuid($valeur), fn ($q) => $q->orWhere('id', $valeur))
            ->orWhereHas('user', fn ($q) => $q->where('email', $valeur)->orWhere('name', $valeur))
            ->first();
    }
}
