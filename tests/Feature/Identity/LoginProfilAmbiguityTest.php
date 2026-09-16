<?php

namespace Tests\Feature\Identity;

use App\Domains\Identity\Domain\Enums\Profil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginProfilAmbiguityTest extends TestCase
{
    use RefreshDatabase;

    private const EMAIL = 'double@chictuktuk.com';

    private function account(Profil $profil, string $password): User
    {
        return User::factory()->profil($profil)->create([
            'email' => self::EMAIL,
            'password' => Hash::make($password),
        ]);
    }

    public function test_deux_comptes_au_meme_mot_de_passe_renvoient_409_avec_les_profils(): void
    {
        $this->account(Profil::Admin, 'partage');
        $this->account(Profil::Owner, 'partage');

        $this->postJson('/api/v1/auth/login', [
            'email' => self::EMAIL,
            'password' => 'partage',
        ])
            ->assertStatus(409)
            ->assertJsonPath('code', 'PROFIL_AMBIGUOUS')
            ->assertJsonPath('profils', ['admin', 'owner']);
    }

    public function test_l_ambiguite_n_emet_aucun_jeton_et_n_incremente_aucun_compteur(): void
    {
        $admin = $this->account(Profil::Admin, 'partage');
        $owner = $this->account(Profil::Owner, 'partage');

        $this->postJson('/api/v1/auth/login', [
            'email' => self::EMAIL,
            'password' => 'partage',
        ])->assertStatus(409);

        $this->assertSame(0, $admin->tokens()->count());
        $this->assertSame(0, $owner->tokens()->count());
        // Le mot de passe était bon : ce n'est pas un échec de connexion.
        $this->assertSame(0, $admin->refresh()->failed_login_attempts);
        $this->assertSame(0, $owner->refresh()->failed_login_attempts);
    }

    public function test_deux_comptes_aux_mots_de_passe_differents_connectent_directement(): void
    {
        $this->account(Profil::Admin, 'mot-de-passe-admin');
        $this->account(Profil::Owner, 'mot-de-passe-owner');

        $this->postJson('/api/v1/auth/login', [
            'email' => self::EMAIL,
            'password' => 'mot-de-passe-owner',
        ])
            ->assertOk()
            ->assertJsonPath('user.profil', 'owner');
    }

    public function test_le_second_appel_avec_profil_leve_l_ambiguite(): void
    {
        $this->account(Profil::Admin, 'partage');
        $this->account(Profil::Owner, 'partage');

        $this->postJson('/api/v1/auth/login', [
            'email' => self::EMAIL,
            'password' => 'partage',
            'profil' => 'admin',
        ])
            ->assertOk()
            ->assertJsonPath('user.profil', 'admin')
            ->assertJsonPath('user.dashboard_path', '/admin/dashboard');
    }
}
