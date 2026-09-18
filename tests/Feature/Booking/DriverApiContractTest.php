<?php

namespace Tests\Feature\Booking;

use App\Domains\Identity\Domain\Enums\Profil;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DriverApiContractTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: Driver, 1: string} */
    private function loginDriver(): array
    {
        $user = User::factory()->profil(Profil::Driver)->create([
            'password' => Hash::make('bon-mot-de-passe'),
        ]);

        foreach (['view-bookings', 'edit-bookings'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $user->givePermissionTo(['view-bookings', 'edit-bookings']);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'bon-mot-de-passe',
        ])->json('token');

        return [Driver::factory()->create(['user_id' => $user->id]), $token];
    }

    public function test_une_course_disponible_ne_livre_aucune_coordonnee_client(): void
    {
        // La règle de confidentialité, vérifiée sur le JSON réellement transmis.
        [, $token] = $this->loginDriver();
        Booking::factory()->create([
            'phone' => '+22999887766',
            'client_name' => 'Awa Dossou',
            'special_requests' => 'Bagages volumineux',
        ]);

        $reponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/driver/bookings/available')
            ->assertOk()
            ->assertJsonCount(1);

        $corps = $reponse->getContent();
        $this->assertStringNotContainsString('22999887766', $corps);
        $this->assertStringNotContainsString('Awa Dossou', $corps);
        $this->assertStringNotContainsString('Bagages volumineux', $corps);
    }

    public function test_accepter_renvoie_la_course_avec_ses_coordonnees(): void
    {
        [, $token] = $this->loginDriver();
        $booking = Booking::factory()->create([
            'phone' => '+22999887766',
            'base_price' => 5000,
        ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/driver/bookings/{$booking->id}/accept")
            ->assertOk()
            ->assertJsonPath('status', 'confirmed')
            ->assertJsonPath('phone', '+22999887766')
            // ⚠️ Entier et non 5000.0 : avec serialize_precision = -1, PHP encode le
            // flottant 5000.0 en 5000, et assertJsonPath compare avec assertSame.
            ->assertJsonPath('base_price', 5000);
    }

    public function test_une_acceptation_concurrente_donne_409_et_jamais_500(): void
    {
        // En un seul processus, le « perdant » est simplement le second appel :
        // lockForUpdate a déjà tranché. La vraie concurrence — deux navigateurs à la
        // seconde près — se vérifie en ligne. Ce test-ci prouve la TRADUCTION du refus,
        // pas le verrou.
        [, $tokenPremier] = $this->loginDriver();
        [, $tokenSecond] = $this->loginDriver();
        $booking = Booking::factory()->create();

        $this->withHeader('Authorization', "Bearer {$tokenPremier}")
            ->postJson("/api/v1/driver/bookings/{$booking->id}/accept")
            ->assertOk();

        $this->withHeader('Authorization', "Bearer {$tokenSecond}")
            ->postJson("/api/v1/driver/bookings/{$booking->id}/accept")
            ->assertStatus(409)
            ->assertJsonPath('code', 'BOOKING_ALREADY_TAKEN');
    }

    public function test_annuler_sans_motif_est_refuse_en_422(): void
    {
        [$driver, $token] = $this->loginDriver();
        $booking = Booking::factory()->confirmed($driver)->create();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/driver/bookings/{$booking->id}/cancel", [])
            ->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_FAILED');
    }

    public function test_annuler_avec_un_motif_enregistre_ce_motif(): void
    {
        [$driver, $token] = $this->loginDriver();
        $booking = Booking::factory()->confirmed($driver)->create();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/driver/bookings/{$booking->id}/cancel", [
                'cancellation_reason' => 'Client injoignable au départ',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'cancelled');

        $this->assertSame('Client injoignable au départ', $booking->fresh()->cancellation_reason);
    }

    public function test_l_historique_est_pagine_et_cherchable(): void
    {
        [$driver, $token] = $this->loginDriver();
        Booking::factory()->count(12)->completed($driver)->create();
        $cible = Booking::factory()->completed($driver)->create(['from_location' => 'Cadjehoun']);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/driver/bookings/history')
            ->assertOk()
            ->assertJsonPath('per_page', 10)
            ->assertJsonPath('total', 13)
            ->assertJsonCount(10, 'data');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/driver/bookings/history?search=Cadjehoun')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.id', $cible->id);
    }

    public function test_le_tableau_de_bord_porte_ses_douze_champs(): void
    {
        [, $token] = $this->loginDriver();
        Booking::factory()->count(3)->create();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/driver/dashboard')
            ->assertOk()
            ->assertJsonStructure([
                'total_trips', 'rating', 'confirmed_trips', 'completed_trips',
                'cancelled_trips', 'earnings_today', 'total_earnings',
                'commission_today', 'total_commission', 'total_duration_minutes',
                'recent_available', 'recent_assigned',
            ]);
    }

    public function test_demarrer_puis_terminer_enchainent_les_statuts(): void
    {
        [$driver, $token] = $this->loginDriver();
        $booking = Booking::factory()->confirmed($driver)->create(['base_price' => 5000]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/driver/bookings/{$booking->id}/start")
            ->assertOk()
            ->assertJsonPath('status', 'in_progress');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/driver/bookings/{$booking->id}/complete")
            ->assertOk();

        $this->assertSame('completed', $booking->fresh()->status);
        $this->assertSame(750.0, (float) $booking->fresh()->commission);
    }

    public function test_reprendre_une_course_deja_terminee_donne_409_et_non_500(): void
    {
        [$driver, $token] = $this->loginDriver();
        $booking = Booking::factory()->completed($driver)->create();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/driver/bookings/{$booking->id}/complete")
            ->assertStatus(409)
            ->assertJsonPath('code', 'BOOKING_NOT_COMPLETABLE');
    }

    public function test_revoquer_rend_la_course_a_tous(): void
    {
        [$driver, $token] = $this->loginDriver();
        $parent = Booking::factory()->subscriptionParent()->create();
        $enfant = Booking::factory()
            ->subscriptionChild($parent)
            ->linkedToSubscriptionDriver($driver)
            ->create(['driver_id' => $driver->id, 'status' => 'confirmed']);

        // Le retour d'une révocation est une course redevenue `pending` et sans agent :
        // c'est pour ce cas que le statut d'AssignedBookingData n'est pas restreint à
        // `confirmed` et `in_progress`.
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/driver/bookings/{$enfant->id}/revoke-subscription")
            ->assertOk()
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('is_revoked', true);
    }
}
