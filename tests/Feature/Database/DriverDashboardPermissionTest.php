<?php

namespace Tests\Feature\Database;

use Database\Seeders\ReferenceRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Le rôle `driver` doit porter `view-dashboard`.
 *
 * Signalé au test visuel du 2026-09-18 : l'agent n'avait pas d'entrée « Tableau de
 * bord » dans son menu. `buildNavigation` filtre sur les permissions EFFECTIVES, et le
 * rôle `driver` de référence ne portait pas celle-ci — alors que l'espace agent a bien
 * un tableau de bord, en Blade comme dans le front Nuxt.
 */
class DriverDashboardPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_role_driver_porte_view_dashboard(): void
    {
        $this->seed(ReferenceRolesAndPermissionsSeeder::class);

        $driver = Role::where('name', 'driver')->firstOrFail();

        $this->assertTrue(
            $driver->hasPermissionTo('view-dashboard'),
            'Sans cette permission, l\'agent n\'a pas d\'entrée « Tableau de bord » dans son menu.',
        );
    }

    public function test_view_dashboard_n_ouvre_aucun_espace_d_un_autre_profil(): void
    {
        // Le garde-fou : `view-dashboard` garde /admin/dashboard et /client/dashboard,
        // mais ces routes portent AUSSI `profil:admin` et `profil:client`. La permission
        // seule n'ouvre donc rien à un agent. Ce test échouerait si quelqu'un retirait
        // un jour le garde de profil en s'appuyant sur la permission seule.
        $this->seed(ReferenceRolesAndPermissionsSeeder::class);

        foreach (['admin', 'client'] as $espace) {
            $fichier = file_get_contents(base_path("routes/{$espace}.php"));

            $this->assertStringContainsString(
                "profil:{$espace}",
                $fichier,
                "Les routes de l'espace {$espace} doivent rester gardées par leur profil.",
            );
        }
    }
}
