<?php

namespace Tests\Feature\Booking;

use App\Domains\Booking\Application\Actions\ListAvailableBookings;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\User;
use Database\Seeders\DriverScenarioSeeder;
use Faker\Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Le seeder de scénarios doit tourner SUR STAGING, où Faker n'existe pas.
 *
 * `fakerphp/faker` est en `require-dev` et le Dockerfile fait
 * `composer install --no-dev` : tout code qui appelle `fake()` lève
 * « Class "Faker\Factory" not found » dès qu'il quitte le poste de développement.
 * Signalé au second test visuel du 2026-09-18.
 */
class DriverScenarioSeederTest extends TestCase
{
    use RefreshDatabase;

    /** Les fichiers qui doivent tourner hors du développement. */
    private const SANS_FAKER = [
        'database/seeders/DriverScenarioSeeder.php',
        'database/factories/BookingFactory.php',
    ];

    public function test_aucun_appel_a_faker_dans_la_chaine_du_seeder(): void
    {
        foreach (self::SANS_FAKER as $chemin) {
            // On ignore les commentaires : ces fichiers EXPLIQUENT pourquoi ils
            // n'appellent pas fake(), et doivent pouvoir le nommer.
            $code = collect(file(base_path($chemin)))
                ->reject(fn (string $ligne) => preg_match('#^\s*(\*|//|/\*)#', $ligne))
                ->implode('');

            $this->assertStringNotContainsString(
                'fake(',
                $code,
                "{$chemin} appelle fake(), qui n'existe pas hors développement."
            );
        }
    }

    public function test_le_seeder_n_instancie_aucune_fabrique(): void
    {
        // ⚠️ Le critère décisif, et il est plus fort que « pas de fake() ».
        // Factory::__construct fait `$this->faker = $this->withFaker()` INCONDITIONNEL-
        // LEMENT : instancier une fabrique résout Faker\Generator par le conteneur, que
        // sa definition() appelle fake() ou non. Retirer fake() des définitions ne
        // suffit donc pas — c'est ce qui a fait échouer le premier correctif.
        $code = collect(file(base_path('database/seeders/DriverScenarioSeeder.php')))
            ->reject(fn (string $ligne) => preg_match('#^\s*(\*|//|/\*)#', $ligne))
            ->implode('');

        $this->assertStringNotContainsString(
            '::factory()',
            $code,
            'Le seeder instancie une fabrique, ce qui exige Faker — absent hors développement.'
        );
    }

    public function test_le_seeder_ne_declenche_aucune_autre_fabrique(): void
    {
        // Le piège suivant, si on ne le ferme pas : BookingFactory pose
        // `user_id => User::factory()`, une valeur PARESSEUSE qui ne s'évalue que si
        // l'appelant ne fournit pas la colonne. UserFactory, elle, appelle fake().
        // Le seeder doit donc fournir `user_id` lui-même — et ne créer aucun
        // utilisateur au passage.
        $agent = Driver::factory()->create();
        Driver::factory()->create();
        $utilisateursAvant = User::count();

        putenv("SCENARIO_DRIVER={$agent->id}");
        Artisan::call('db:seed', ['--class' => DriverScenarioSeeder::class, '--no-interaction' => true]);
        putenv('SCENARIO_DRIVER');

        $this->assertSame(
            $utilisateursAvant,
            User::count(),
            'Le seeder a créé un utilisateur, donc UserFactory a tourné, donc fake() aussi.',
        );
    }

    public function test_le_seeder_cree_les_douze_courses_pour_l_agent_designe(): void
    {
        $agent = Driver::factory()->create();
        Driver::factory()->create(); // le second agent, dont l'abonnement doit rester caché

        putenv("SCENARIO_DRIVER={$agent->id}");
        Artisan::call('db:seed', ['--class' => DriverScenarioSeeder::class, '--no-interaction' => true]);
        putenv('SCENARIO_DRIVER');

        $this->assertSame(12, Booking::where('special_requests', 'LIKE', '[SCENARIO]%')->count());
    }

    public function test_l_agent_se_designe_par_son_email(): void
    {
        // Le motif du second échec : un e-mail comparé à une colonne `uuid` fait lever
        // PostgreSQL — « invalid input syntax for type uuid ». La recherche ne doit
        // toucher la colonne `id` que si la valeur EST un UUID.
        $agent = Driver::factory()->create();
        Driver::factory()->create();

        putenv("SCENARIO_DRIVER={$agent->user->email}");
        Artisan::call('db:seed', ['--class' => DriverScenarioSeeder::class, '--no-interaction' => true]);
        putenv('SCENARIO_DRIVER');

        $this->assertSame(12, Booking::where('special_requests', 'LIKE', '[SCENARIO]%')->count());
    }

    public function test_un_agent_introuvable_ne_fait_pas_lever_postgresql(): void
    {
        Driver::factory()->count(2)->create();

        putenv('SCENARIO_DRIVER=personne@nulle-part.bj');
        Artisan::call('db:seed', ['--class' => DriverScenarioSeeder::class, '--no-interaction' => true]);
        putenv('SCENARIO_DRIVER');

        $this->assertSame(0, Booking::where('special_requests', 'LIKE', '[SCENARIO]%')->count());
    }

    public function test_les_formes_produites_sont_celles_que_l_action_distingue(): void
    {
        // Le seeder ne partage AUCUN code avec ListAvailableBookings : ce test est donc
        // la seule chose qui empêche les deux de diverger. Sans lui, la vérification en
        // ligne pourrait porter sur des formes que l'action ne distingue plus.
        $agent = Driver::factory()->create();
        $autre = Driver::factory()->create();

        putenv("SCENARIO_DRIVER={$agent->id}");
        Artisan::call('db:seed', ['--class' => DriverScenarioSeeder::class, '--no-interaction' => true]);
        putenv('SCENARIO_DRIVER');

        $forme = fn (string $nom) => Booking::where('special_requests', '[SCENARIO] '.$nom)->firstOrFail()->id;

        $vuParLAgent = app(ListAvailableBookings::class)($agent->id)
            ->pluck('id')->all();
        $vuParLAutre = app(ListAvailableBookings::class)($autre->id)
            ->pluck('id')->all();

        // Visibles de tout agent.
        foreach (['unique_simple', 'unique_aller_retour', 'abo_parent_libre', 'abo_enfant_revoque', 'abo_retour_revoque'] as $nom) {
            $this->assertContains($forme($nom), $vuParLAgent, "« {$nom} » doit être visible de l'agent de test.");
            $this->assertContains($forme($nom), $vuParLAutre, "« {$nom} » doit être visible de tout agent.");
        }

        // Réservées à l'agent de test : ce sont elles qui prouvent qu'aucune course ne fuit.
        foreach (['retour_cachee_prise', 'abo_parent_lie', 'abo_enfant_lie', 'abo_retour_lie'] as $nom) {
            $this->assertContains($forme($nom), $vuParLAgent, "« {$nom} » doit être visible de son titulaire.");
            $this->assertNotContains($forme($nom), $vuParLAutre, "« {$nom} » NE DOIT PAS fuir vers un autre agent.");
        }

        // Cachée de tous : aucun titulaire ne lui est encore rattaché.
        $this->assertNotContains($forme('retour_cachee_libre'), $vuParLAgent);
        $this->assertNotContains($forme('retour_cachee_libre'), $vuParLAutre);

        // L'abonnement de l'autre agent, qui ne doit jamais apparaître à l'agent de test.
        $this->assertNotContains($forme('abo_d_un_autre_agent'), $vuParLAgent);
        $this->assertContains($forme('abo_d_un_autre_agent'), $vuParLAutre);
    }

    public function test_le_seeder_tourne_sans_que_faker_soit_resolvable(): void
    {
        // LE test décisif, et le seul qui reproduise vraiment la panne de staging.
        //
        // Les deux tests statiques au-dessus cherchent des motifs dans le source ; ils
        // passeraient encore si une dépendance INDIRECTE résolvait Faker. Ici on pose un
        // piège dans le conteneur : toute tentative de résolution lève. Si le seeder
        // aboutit, c'est qu'il ne touche jamais Faker — ce que le déploiement
        // `--no-dev` exige.
        //
        // Les agents sont créés AVANT le piège : les fabriques de test, elles, ont
        // parfaitement le droit d'utiliser Faker.
        $agent = Driver::factory()->create();
        Driver::factory()->create();

        $this->app->bind(
            Generator::class,
            fn () => throw new \RuntimeException('Faker a été résolu : le seeder ne tournera pas sur staging.')
        );

        putenv("SCENARIO_DRIVER={$agent->id}");
        Artisan::call('db:seed', ['--class' => DriverScenarioSeeder::class, '--no-interaction' => true]);
        putenv('SCENARIO_DRIVER');

        $this->assertSame(12, Booking::where('special_requests', 'LIKE', '[SCENARIO]%')->count());
    }
}
