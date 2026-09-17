<?php

namespace Tests\Feature\Identity;

use App\Domains\Identity\Domain\Enums\Profil;
use App\Domains\Identity\Domain\PasswordReset\UserKeyedTokenRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

/**
 * Les contrôleurs de l'API attrapent leurs propres échecs imprévus.
 *
 * Deux garanties sont vérifiées ici, et la seconde est la plus fragile : un échec
 * technique doit ressortir avec un code propre à l'opération plutôt qu'en
 * SERVER_ERROR générique, MAIS les exceptions qui sont la réponse voulue de l'API
 * (ValidationException pour un 422, ApiException pour un statut choisi) doivent
 * traverser le try/catch intactes. Sans cette distinction, un simple refus
 * d'identifiants se transformerait en 500.
 */
class InternalFailureTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $attributes = []): User
    {
        return User::factory()->profil(Profil::Driver)->create(array_merge([
            'email' => 'agent@chictuktuk.com',
            'password' => Hash::make('bon-mot-de-passe'),
        ], $attributes));
    }

    public function test_un_echec_technique_a_la_connexion_porte_son_propre_code(): void
    {
        $this->user();
        Log::spy();

        // Panne imprévue au cœur de l'action, après la création de l'utilisateur.
        Hash::shouldReceive('check')->andThrow(new RuntimeException('bcrypt indisponible'));

        $this->postJson('/api/v1/auth/login', [
            'email' => 'agent@chictuktuk.com',
            'password' => 'bon-mot-de-passe',
        ])
            ->assertStatus(500)
            ->assertJsonPath('code', 'LOGIN_FAILED');

        // atLeast : le gestionnaire global journalise aussi l'exception qui remonte.
        Log::shouldHaveReceived('error')->atLeast()->once();
    }

    public function test_un_refus_d_identifiants_traverse_le_try_catch_intact(): void
    {
        $this->user();

        // La ValidationException est la réponse VOULUE : elle ne doit pas être
        // convertie en 500 par le try/catch du contrôleur.
        $this->postJson('/api/v1/auth/login', [
            'email' => 'agent@chictuktuk.com',
            'password' => 'mauvais',
        ])
            ->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_FAILED');
    }

    public function test_un_verrou_de_compte_traverse_le_try_catch_intact(): void
    {
        $this->user(['locked_until' => now()->addMinutes(5), 'failed_login_attempts' => 5]);

        // Même chose pour une ApiException : 423 et non 500.
        $this->postJson('/api/v1/auth/login', [
            'email' => 'agent@chictuktuk.com',
            'password' => 'bon-mot-de-passe',
        ])
            ->assertStatus(423)
            ->assertJsonPath('code', 'ACCOUNT_LOCKED');
    }

    public function test_un_echec_technique_au_changement_de_mot_de_passe_porte_son_code(): void
    {
        $this->user();

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'agent@chictuktuk.com',
            'password' => 'bon-mot-de-passe',
        ])->json('token');

        Hash::shouldReceive('check')->andReturn(true);
        Hash::shouldReceive('make')->andThrow(new RuntimeException('bcrypt indisponible'));

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/password', [
                'current_password' => 'bon-mot-de-passe',
                'password' => 'nouveau-mot-de-passe',
                'password_confirmation' => 'nouveau-mot-de-passe',
            ])
            ->assertStatus(500)
            ->assertJsonPath('code', 'PASSWORD_CHANGE_FAILED');
    }

    public function test_un_echec_technique_a_la_reinitialisation_porte_son_code(): void
    {
        $user = $this->user();
        $jeton = app(UserKeyedTokenRepository::class)->createFor($user);

        Hash::shouldReceive('check')->andThrow(new RuntimeException('base indisponible'));

        $this->postJson('/api/v1/auth/password/reset', [
            'token' => $jeton,
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ])
            ->assertStatus(500)
            ->assertJsonPath('code', 'PASSWORD_RESET_FAILED');
    }

    public function test_un_jeton_invalide_traverse_le_try_catch_intact(): void
    {
        $this->postJson('/api/v1/auth/password/reset', [
            'token' => 'jeton-inexistant',
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    }

    public function test_la_demande_de_reinitialisation_reste_muette_meme_en_panne(): void
    {
        Mail::fake();
        $this->user(['email' => 'connu@chictuktuk.com']);
        Log::spy();

        // Panne interne sur une adresse CONNUE.
        Hash::shouldReceive('make')->andThrow(new RuntimeException('base indisponible'));

        $reponseConnue = $this->postJson('/api/v1/auth/password/forgot', ['email' => 'connu@chictuktuk.com']);
        $reponseInconnue = $this->postJson('/api/v1/auth/password/forgot', ['email' => 'personne@chictuktuk.com']);

        // Seule exception à la règle du code d'erreur propre : ici, une réponse
        // distinguable rouvrirait l'énumération des comptes. L'échec est journalisé,
        // pas renvoyé.
        //
        // NB : LOGOUT_FAILED et PROFILE_READ_FAILED ne sont pas couverts ici — leurs
        // échecs ne se provoquent pas sans contorsion. Le test ci-dessous vérifie en
        // revanche que le catch attrape bien les \Error, ce dont dépendent les six.
        $this->assertSame(200, $reponseConnue->status());
        $this->assertSame($reponseConnue->json(), $reponseInconnue->json());

        // atLeast : le gestionnaire global journalise aussi l'exception qui remonte.
        Log::shouldHaveReceived('error')->atLeast()->once();
    }

    public function test_le_catch_attrape_aussi_les_erreurs_php_et_pas_seulement_les_exceptions(): void
    {
        $this->user();

        // Un TypeError n'hérite pas d'Exception : avec un `catch (\Exception)` il
        // passerait sous le nez du contrôleur et ressortirait en SERVER_ERROR
        // générique, sans le code de l'opération ni son contexte métier au journal.
        Hash::shouldReceive('check')->andThrow(new \TypeError('argument de type inattendu'));

        $this->postJson('/api/v1/auth/login', [
            'email' => 'agent@chictuktuk.com',
            'password' => 'bon-mot-de-passe',
        ])
            ->assertStatus(500)
            ->assertJsonPath('code', 'LOGIN_FAILED');
    }
}
