<?php

namespace Tests\Feature\Booking;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\User;
use Database\Seeders\DriverScenarioSeeder;
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
                "{$chemin} appelle fake(), qui n'existe pas hors développement.",
            );
        }
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
}
