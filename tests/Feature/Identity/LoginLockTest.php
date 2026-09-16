<?php

namespace Tests\Feature\Identity;

use App\Domains\Identity\Domain\Enums\Profil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class LoginLockTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $attributes = []): User
    {
        return User::factory()->profil(Profil::Driver)->create(array_merge([
            'email' => 'agent@chictuktuk.com',
            'password' => Hash::make('bon-mot-de-passe'),
        ], $attributes));
    }

    private function attempt(string $password): TestResponse
    {
        return $this->postJson('/api/v1/auth/login', [
            'email' => 'agent@chictuktuk.com',
            'password' => $password,
        ]);
    }

    public function test_cinq_echecs_verrouillent_le_compte(): void
    {
        $user = $this->user();

        for ($i = 0; $i < 5; $i++) {
            $this->attempt('mauvais')->assertStatus(422);
        }

        $user->refresh();
        $this->assertSame(5, $user->failed_login_attempts);
        $this->assertTrue($user->locked_until->isFuture());
    }

    public function test_un_compte_verrouille_refuse_meme_le_bon_mot_de_passe(): void
    {
        $this->user(['locked_until' => now()->addMinutes(5), 'failed_login_attempts' => 5]);

        $response = $this->attempt('bon-mot-de-passe');

        $response->assertStatus(423)
            ->assertJsonPath('code', 'ACCOUNT_LOCKED')
            ->assertJsonStructure(['message', 'code', 'retry_after']);

        $this->assertGreaterThan(0, $response->json('retry_after'));
        $this->assertNotNull($response->headers->get('Retry-After'));
    }

    public function test_un_verrou_expire_ne_bloque_plus(): void
    {
        $this->user(['locked_until' => now()->subMinute(), 'failed_login_attempts' => 5]);

        $this->attempt('bon-mot-de-passe')->assertOk();
    }

    public function test_un_echec_incremente_tous_les_comptes_de_l_email(): void
    {
        $driver = $this->user();
        $owner = User::factory()->profil(Profil::Owner)->create([
            'email' => 'agent@chictuktuk.com',
            'password' => Hash::make('autre-mot-de-passe'),
        ]);

        $this->attempt('mauvais')->assertStatus(422);

        // Même personne derrière les deux comptes (unicité email+profil) :
        // ils se verrouillent ensemble.
        $this->assertSame(1, $driver->refresh()->failed_login_attempts);
        $this->assertSame(1, $owner->refresh()->failed_login_attempts);
    }

    public function test_un_seul_compte_verrouille_sur_deux_laisse_passer_l_autre(): void
    {
        $this->user(['locked_until' => now()->addMinutes(5), 'failed_login_attempts' => 5]);
        User::factory()->profil(Profil::Owner)->create([
            'email' => 'agent@chictuktuk.com',
            'password' => Hash::make('mot-de-passe-owner'),
        ]);

        // Sinon, verrouiller un compte suffirait à bloquer l'accès à l'autre.
        $this->attempt('mot-de-passe-owner')
            ->assertOk()
            ->assertJsonPath('user.profil', 'owner');
    }

    public function test_un_compte_desactive_est_refuse(): void
    {
        $user = $this->user(['is_active' => false]);

        $this->attempt('bon-mot-de-passe')
            ->assertStatus(403)
            ->assertJsonPath('code', 'ACCOUNT_DISABLED');

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_email_inconnu_et_mot_de_passe_faux_donnent_la_meme_reponse(): void
    {
        $this->user();

        $mauvaisMotDePasse = $this->attempt('mauvais');
        $emailInconnu = $this->postJson('/api/v1/auth/login', [
            'email' => 'personne@chictuktuk.com',
            'password' => 'peu-importe',
        ]);

        $this->assertSame(422, $mauvaisMotDePasse->status());
        $this->assertSame(422, $emailInconnu->status());
        // Strictement identiques : sinon l'endpoint devient un test d'existence.
        $this->assertSame($mauvaisMotDePasse->json(), $emailInconnu->json());
    }
}
