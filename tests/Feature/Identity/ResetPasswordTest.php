<?php

namespace Tests\Feature\Identity;

use App\Domains\Identity\Domain\Enums\Profil;
use App\Domains\Identity\Domain\PasswordReset\UserKeyedTokenRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    private function userAvecJeton(): array
    {
        $user = User::factory()->profil(Profil::Driver)->create([
            'email' => 'agent@chictuktuk.com',
            'password' => Hash::make('ancien-mot-de-passe'),
        ]);

        return [$user, app(UserKeyedTokenRepository::class)->createFor($user)];
    }

    public function test_le_mot_de_passe_est_reinitialise(): void
    {
        [$user, $jeton] = $this->userAvecJeton();

        $this->postJson('/api/v1/auth/password/reset', [
            'token' => $jeton,
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ])->assertNoContent();

        $this->assertTrue(Hash::check('nouveau-mot-de-passe', $user->refresh()->password));
    }

    public function test_le_jeton_est_consomme(): void
    {
        [$user, $jeton] = $this->userAvecJeton();

        $this->postJson('/api/v1/auth/password/reset', [
            'token' => $jeton,
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ])->assertNoContent();

        $this->assertSame(0, DB::table('password_reset_tokens')->where('user_id', $user->id)->count());

        // Rejouer le même jeton doit échouer.
        $this->postJson('/api/v1/auth/password/reset', [
            'token' => $jeton,
            'password' => 'encore-un-autre',
            'password_confirmation' => 'encore-un-autre',
        ])->assertStatus(422)->assertJsonValidationErrors(['token']);
    }

    public function test_tous_les_jetons_d_acces_du_compte_sont_revoques(): void
    {
        [$user, $jeton] = $this->userAvecJeton();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'agent@chictuktuk.com',
            'password' => 'ancien-mot-de-passe',
        ])->assertOk();
        $this->assertSame(1, $user->tokens()->count());

        $this->postJson('/api/v1/auth/password/reset', [
            'token' => $jeton,
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ])->assertNoContent();

        // Un mot de passe réinitialisé signifie un accès possiblement compromis :
        // toutes les sessions tombent, sans exception.
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_la_reinitialisation_deverrouille_le_compte(): void
    {
        [$user, $jeton] = $this->userAvecJeton();
        $user->forceFill([
            'failed_login_attempts' => 5,
            'locked_until' => now()->addMinutes(5),
        ])->saveQuietly();

        $this->postJson('/api/v1/auth/password/reset', [
            'token' => $jeton,
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ])->assertNoContent();

        $user->refresh();
        $this->assertSame(0, $user->failed_login_attempts);
        $this->assertNull($user->locked_until);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'agent@chictuktuk.com',
            'password' => 'nouveau-mot-de-passe',
        ])->assertOk();
    }

    public function test_un_jeton_invalide_est_refuse(): void
    {
        $this->postJson('/api/v1/auth/password/reset', [
            'token' => 'jeton-inexistant',
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    }

    #[DataProvider('jetonsMalFormes')]
    public function test_un_jeton_mal_forme_est_refuse_sans_erreur_serveur(string $jeton, string $cas): void
    {
        // Le préfixe du jeton est comparé à `password_reset_tokens.user_id`, une
        // colonne uuid de PostgreSQL. Un préfixe qui n'est pas un uuid — « abc.def »,
        // typiquement un lien tronqué par un client de messagerie — faisait échouer la
        // conversion côté base et ressortait en 500, là où un 422 est attendu.
        // Relevé sur staging le 2026-09-17.
        $this->postJson('/api/v1/auth/password/reset', [
            'token' => $jeton,
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ])
            ->assertStatus(422, $cas)
            ->assertJsonValidationErrors(['token']);
    }

    public static function jetonsMalFormes(): array
    {
        return [
            'préfixe non-uuid' => ['abc.def', 'un préfixe qui n\'est pas un uuid'],
            'préfixe vide' => ['.abc', 'un jeton commençant par le séparateur'],
            'aléa vide' => ['abc.', 'un jeton sans partie aléatoire'],
            'uuid tronqué' => ['a9ff556f-629b.def', 'un uuid coupé au milieu'],
            'point seul' => ['.', 'le séparateur seul'],
            'plusieurs points' => ['abc.def.ghi', 'plusieurs séparateurs'],
        ];
    }

    public function test_la_confirmation_est_exigee(): void
    {
        [, $jeton] = $this->userAvecJeton();

        $this->postJson('/api/v1/auth/password/reset', [
            'token' => $jeton,
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'autre-chose',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
