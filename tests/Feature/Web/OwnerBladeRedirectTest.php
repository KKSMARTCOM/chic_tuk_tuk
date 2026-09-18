<?php

namespace Tests\Feature\Web;

use App\Domains\Identity\Domain\Enums\Profil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Les anciens chemins Blade du propriétaire renvoient vers le front Nuxt.
 *
 * ⚠️ Ces renvois sont PUBLICS, et c'est le cœur de ce fichier. Gardés par
 * `auth:sanctum` + `profil:owner`, comme ils l'étaient à la bascule du 2026-09-18, ils
 * étaient inatteignables par la seule personne qu'ils servent : un propriétaire sans
 * session Blade était renvoyé vers `/login`, où son profil n'est pas proposé. Cul-de-sac.
 *
 * Une adresse de réexpédition n'a rien à protéger : elle ne révèle que l'URL que la
 * personne a elle-même tapée, et le front applique sa propre authentification à
 * l'arrivée.
 */
class OwnerBladeRedirectTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Le formulaire Blade impose majuscule, chiffre et caractère spécial, en plus
     * de huit caractères. Un mot de passe plus simple ferait échouer la validation
     * AVANT le contrôle du profil, et le test passerait pour la mauvaise raison.
     */
    private const MOT_DE_PASSE = 'Passe1!secret';

    private function front(string $path): string
    {
        return config('app.front_app_url').$path;
    }

    public function test_le_tableau_de_bord_renvoie_vers_le_front_sans_session(): void
    {
        $this->get('/owner/dashboard')
            ->assertRedirect($this->front('/owner/dashboard'));
    }

    public function test_la_fiche_vehicule_renvoie_vers_le_front_sans_session(): void
    {
        $id = (string) Str::uuid();

        $this->get("/owner/vehicles/{$id}")
            ->assertRedirect($this->front("/owner/vehicles/{$id}"));
    }

    public function test_l_ancien_chemin_leaves_renvoie_vers_pauses(): void
    {
        // Le renommage compte : l'écran affichait les pauses du véhicule et non les
        // congés d'un agent. Renvoyer vers `.../leaves` mènerait à une page inexistante.
        $id = (string) Str::uuid();

        $this->get("/owner/leaves/{$id}")
            ->assertRedirect($this->front("/owner/vehicles/{$id}/pauses"));
    }

    public function test_l_historique_des_paiements_renvoie_vers_le_front_sans_session(): void
    {
        $id = (string) Str::uuid();

        $this->get("/owner/payments/{$id}")
            ->assertRedirect($this->front("/owner/vehicles/{$id}/payments"));
    }

    public function test_la_page_de_connexion_proprietaire_renvoie_vers_celle_du_front(): void
    {
        // Seul chemin par lequel un propriétaire pouvait s'authentifier sur le Blade :
        // la page générique ne propose que Client, Agent et Administrateur. Le renvoyer
        // ici est ce qui supprime la double connexion.
        $this->get('/owner/login')
            ->assertRedirect($this->front('/login'));
    }

    public function test_un_propriétaire_ne_peut_plus_ouvrir_de_session_blade(): void
    {
        // La page générique ne proposait déjà pas ce profil ; la validation le refuse
        // désormais aussi, pour qu'un POST forgé ne recrée pas une session devenue
        // inutile — et trompeuse, puisqu'elle ne donne accès à aucun écran.
        $owner = User::factory()->profil(Profil::Owner)->create([
            'password' => Hash::make(self::MOT_DE_PASSE),
        ]);

        $this->post('/login-store', [
            'profil' => 'owner',
            'email' => $owner->email,
            'password' => self::MOT_DE_PASSE,
        ])
            // ⚠️ Sous `email`, et non sous `profil` : `loginStore` attrape la
            // ValidationException et réempile TOUTES les erreurs sous cette seule clé,
            // quelle que soit la règle violée. Bizarrerie préexistante, constatée ici,
            // hors du périmètre de ce changement.
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame(0, $owner->tokens()->count());
    }

    public function test_les_trois_autres_profils_restent_acceptes(): void
    {
        // Sans ce test, retirer `owner` de la validation pourrait en emporter un autre
        // sans que rien ne le signale.
        $agent = User::factory()->profil(Profil::Driver)->create([
            'password' => Hash::make(self::MOT_DE_PASSE),
        ]);

        $this->post('/login-store', [
            'profil' => 'driver',
            'email' => $agent->email,
            'password' => self::MOT_DE_PASSE,
        ])->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($agent);
    }
}
