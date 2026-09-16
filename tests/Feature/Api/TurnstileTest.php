<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Vérification anti-robot de la réservation publique.
 *
 * Aucun de ces tests ne crée de réservation : soit le middleware refuse la requête,
 * soit la validation métier la rejette parce que le corps est vide.
 */
class TurnstileTest extends TestCase
{
    private const ENDPOINT = '/api/v1/public/bookings';

    public function test_une_reservation_sans_jeton_est_refusee(): void
    {
        config(['services.turnstile.secret' => 'secret-de-test']);
        Http::preventStrayRequests();

        $this->postJson(self::ENDPOINT, [])
            ->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_FAILED')
            ->assertJsonStructure(['errors' => ['cf_turnstile_token']]);
    }

    public function test_un_jeton_refuse_par_cloudflare_bloque_la_reservation(): void
    {
        config(['services.turnstile.secret' => 'secret-de-test']);

        Http::fake([
            'challenges.cloudflare.com/*' => Http::response([
                'success'     => false,
                'error-codes' => ['invalid-input-response'],
            ]),
        ]);

        $this->postJson(self::ENDPOINT, ['cf_turnstile_token' => 'jeton-invalide'])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['cf_turnstile_token']]);

        Http::assertSent(fn ($request) => $request['secret'] === 'secret-de-test'
            && $request['response'] === 'jeton-invalide');
    }

    public function test_un_jeton_valide_laisse_la_main_a_la_validation_metier(): void
    {
        config(['services.turnstile.secret' => 'secret-de-test']);

        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => true]),
        ]);

        // Corps vide : la requête doit être rejetée sur les champs de la réservation,
        // et surtout pas sur le jeton — preuve que le middleware l'a laissée passer.
        $response = $this->postJson(self::ENDPOINT, ['cf_turnstile_token' => 'jeton-valide']);

        $response->assertStatus(422)->assertJsonMissingPath('errors.cf_turnstile_token');
        $this->assertArrayHasKey('from_location', $response->json('errors'));
    }

    public function test_sans_secret_configure_la_verification_est_ignoree(): void
    {
        config(['services.turnstile.secret' => '']);
        Http::preventStrayRequests();

        $this->postJson(self::ENDPOINT, [])
            ->assertStatus(422)
            ->assertJsonMissingPath('errors.cf_turnstile_token');
    }
}
