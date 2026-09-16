<?php

namespace Tests\Feature\Identity;

use App\Domains\Identity\Domain\Enums\Profil;
use App\Models\User;
use App\Shared\Http\ApiException;
use App\Shared\Http\ApiExceptionRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryAndApiExceptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_factory_cree_un_utilisateur_pour_chaque_profil(): void
    {
        foreach (Profil::cases() as $profil) {
            $user = User::factory()->profil($profil)->create();

            $this->assertSame($profil->value, $user->profil);
            $this->assertTrue($user->is_active);
            $this->assertNotNull($user->phone);
            $this->assertSame(0, $user->failed_login_attempts);
        }
    }

    public function test_la_factory_peut_creer_un_compte_verrouille_ou_desactive(): void
    {
        $locked = User::factory()->profil(Profil::Driver)->locked()->create();
        $this->assertTrue($locked->locked_until->isFuture());

        $inactive = User::factory()->profil(Profil::Client)->inactive()->create();
        $this->assertFalse($inactive->is_active);
    }

    public function test_api_exception_porte_son_code_et_ses_champs_supplementaires(): void
    {
        $response = ApiExceptionRenderer::render(
            new ApiException(423, 'ACCOUNT_LOCKED', 'Verrouillé.', ['retry_after' => 240], ['Retry-After' => '240']),
        );

        $this->assertSame(423, $response->getStatusCode());
        $this->assertSame('240', $response->headers->get('Retry-After'));
        $this->assertSame(
            ['message' => 'Verrouillé.', 'code' => 'ACCOUNT_LOCKED', 'retry_after' => 240],
            $response->getData(true),
        );
    }

    public function test_la_configuration_identity_expose_les_seuils(): void
    {
        $this->assertSame('api', config('identity.token.name'));
        $this->assertSame(14, config('identity.token.inactivity_days'));
        $this->assertSame(90, config('identity.token.absolute_days'));
        $this->assertSame(5, config('identity.lock.max_attempts'));
        $this->assertSame(5, config('identity.lock.minutes'));
        $this->assertSame(60, config('identity.password_reset.expire_minutes'));
    }
}
