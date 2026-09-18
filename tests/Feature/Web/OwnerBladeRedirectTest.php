<?php

namespace Tests\Feature\Web;

use App\Domains\Identity\Domain\Enums\Profil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Les anciens chemins Blade du propriétaire renvoient vers le front Nuxt.
 *
 * Ces quatre chemins ne servent plus d'écran, mais ils restent la destination de
 * signets et, pour le tableau de bord, de la barre latérale Blade et de la redirection
 * post-connexion d'AuthService. Ce test est ce qui empêche de les supprimer par
 * inadvertance en croyant qu'ils ne servent plus à rien.
 */
class OwnerBladeRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->profil(Profil::Owner)->create();
    }

    public function test_le_tableau_de_bord_renvoie_vers_le_front(): void
    {
        $this->actingAs($this->owner(), 'sanctum')
            ->get('/owner/dashboard')
            ->assertRedirect(config('app.front_app_url').'/owner/dashboard');
    }

    public function test_la_fiche_vehicule_renvoie_vers_le_front(): void
    {
        $id = (string) Str::uuid();

        $this->actingAs($this->owner(), 'sanctum')
            ->get("/owner/vehicles/{$id}")
            ->assertRedirect(config('app.front_app_url')."/owner/vehicles/{$id}");
    }

    public function test_l_ancien_chemin_leaves_renvoie_vers_pauses(): void
    {
        // Le renommage compte : l'écran affichait les pauses du véhicule et non les
        // congés d'un agent. Renvoyer vers `.../leaves` mènerait à une page inexistante.
        $id = (string) Str::uuid();

        $this->actingAs($this->owner(), 'sanctum')
            ->get("/owner/leaves/{$id}")
            ->assertRedirect(config('app.front_app_url')."/owner/vehicles/{$id}/pauses");
    }

    public function test_l_historique_des_paiements_renvoie_vers_le_front(): void
    {
        $id = (string) Str::uuid();

        $this->actingAs($this->owner(), 'sanctum')
            ->get("/owner/payments/{$id}")
            ->assertRedirect(config('app.front_app_url')."/owner/vehicles/{$id}/payments");
    }

    public function test_un_profil_autre_que_proprietaire_reste_refuse(): void
    {
        // La garde `profil:owner` n'a pas sauté avec le contrôleur qu'elle protégeait.
        $this->actingAs(User::factory()->profil(Profil::Driver)->create(), 'sanctum')
            ->get('/owner/dashboard')
            ->assertForbidden();
    }
}
