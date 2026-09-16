<?php

namespace Tests\Feature\Identity;

use App\Domains\Identity\Domain\Enums\Profil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

/**
 * Garantit que le filet de sécurité de l'API attrape ce que le code ne prévoit pas.
 *
 * Les actions du domaine ne contiennent volontairement aucun try/catch : le rendu
 * centralisé de bootstrap/app.php convertit TOUTE exception en JSON normalisé. Ces
 * tests verrouillent ce comportement, pour qu'un futur changement du gestionnaire ne
 * fasse pas ressortir du HTML ou une trace de pile sur l'API.
 */
class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_une_exception_imprevue_ressort_en_json_normalise(): void
    {
        // APP_DEBUG vaut true en local (hérité du .env) : on force le réglage de
        // production, puisque c'est lui qui doit garantir l'absence de fuite.
        config(['app.debug' => false]);

        Route::get('/api/v1/_test-explosion', fn () => throw new RuntimeException('base de données en feu'));

        $response = $this->getJson('/api/v1/_test-explosion');

        $response->assertStatus(500)
            ->assertJsonPath('code', 'SERVER_ERROR')
            ->assertJsonStructure(['message', 'code']);

        // Ni le message technique ni le chemin du fichier ne ressortent.
        $this->assertSame('Une erreur interne est survenue.', $response->json('message'));
        $this->assertArrayNotHasKey('errors', $response->json());
    }

    public function test_une_exception_imprevue_ne_renvoie_jamais_de_html(): void
    {
        Route::get('/api/v1/_test-explosion-2', fn () => throw new RuntimeException('peu importe'));

        $response = $this->getJson('/api/v1/_test-explosion-2');

        $this->assertStringContainsString('application/json', (string) $response->headers->get('Content-Type'));
    }

    public function test_un_echec_d_envoi_d_email_ne_trahit_pas_l_existence_du_compte(): void
    {
        User::factory()->profil(Profil::Driver)->create(['email' => 'connu@chictuktuk.com']);

        // SMTP en panne : le cas le plus banal en production.
        Mail::shouldReceive('to')->andThrow(new RuntimeException('Connection could not be established'));

        $reponseConnue = $this->postJson('/api/v1/auth/password/forgot', ['email' => 'connu@chictuktuk.com']);
        $reponseInconnue = $this->postJson('/api/v1/auth/password/forgot', ['email' => 'personne@chictuktuk.com']);

        // Sans quoi une panne SMTP transforme l'endpoint en test d'existence de compte :
        // 500 pour une adresse connue, 200 pour une inconnue.
        $this->assertSame($reponseConnue->status(), $reponseInconnue->status());
        $this->assertSame($reponseConnue->json(), $reponseInconnue->json());
    }
}
