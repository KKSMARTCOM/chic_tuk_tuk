<?php

namespace Tests\Feature\Identity;

use App\Domains\Identity\Domain\Enums\Profil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    private function login(): array
    {
        $user = User::factory()->profil(Profil::Driver)->create([
            'email' => 'agent@chictuktuk.com',
            'password' => Hash::make('ancien-mot-de-passe'),
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'agent@chictuktuk.com',
            'password' => 'ancien-mot-de-passe',
        ])->json('token');

        return [$user, $token];
    }

    public function test_le_mot_de_passe_est_remplace(): void
    {
        [$user, $token] = $this->login();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/password', [
                'current_password' => 'ancien-mot-de-passe',
                'password' => 'nouveau-mot-de-passe',
                'password_confirmation' => 'nouveau-mot-de-passe',
            ])
            ->assertNoContent();

        $this->assertTrue(Hash::check('nouveau-mot-de-passe', $user->refresh()->password));
    }

    public function test_un_mot_de_passe_actuel_faux_est_refuse(): void
    {
        [$user, $token] = $this->login();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/password', [
                'current_password' => 'pas-le-bon',
                'password' => 'nouveau-mot-de-passe',
                'password_confirmation' => 'nouveau-mot-de-passe',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);

        $this->assertTrue(Hash::check('ancien-mot-de-passe', $user->refresh()->password));
    }

    public function test_la_confirmation_est_exigee(): void
    {
        [, $token] = $this->login();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/password', [
                'current_password' => 'ancien-mot-de-passe',
                'password' => 'nouveau-mot-de-passe',
                'password_confirmation' => 'autre-chose',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_les_autres_jetons_sont_revoques_et_le_courant_survit(): void
    {
        [$user, $premier] = $this->login();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'agent@chictuktuk.com',
            'password' => 'ancien-mot-de-passe',
        ])->assertOk();

        $this->assertSame(2, $user->tokens()->count());

        $this->withHeader('Authorization', "Bearer {$premier}")
            ->postJson('/api/v1/auth/password', [
                'current_password' => 'ancien-mot-de-passe',
                'password' => 'nouveau-mot-de-passe',
                'password_confirmation' => 'nouveau-mot-de-passe',
            ])
            ->assertNoContent();

        // Un mot de passe changé doit couper les autres appareils, sans déconnecter
        // celui qui vient de faire la demande.
        $this->assertSame(1, $user->tokens()->count());

        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', "Bearer {$premier}")
            ->getJson('/api/v1/auth/me')
            ->assertOk();
    }

    public function test_l_endpoint_exige_une_authentification(): void
    {
        $this->postJson('/api/v1/auth/password', [])
            ->assertStatus(401);
    }
}
