<?php

namespace Tests\Feature\Booking;

use App\Domains\Booking\Application\Actions\AcceptBooking;
use App\Domains\Booking\Application\Actions\CancelBooking;
use App\Domains\Booking\Application\Actions\CompleteBooking;
use App\Domains\Booking\Application\Actions\RevokeFromSubscription;
use App\Domains\Booking\Application\Actions\StartBooking;
use App\Models\Booking;
use App\Models\Driver;
use App\Shared\Http\ApiException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La table des refus, éprouvée au niveau des actions.
 *
 * Testée ici plutôt qu'à travers HTTP : une action est appelable directement, et
 * l'assertion porte alors sur le couple (statut, code) sans dépendre du routage.
 */
class RefusalCodesTest extends TestCase
{
    use RefreshDatabase;

    private function assertRefus(callable $appel, int $statut, string $code): void
    {
        try {
            $appel();
            $this->fail("Un refus était attendu : {$statut} {$code}.");
        } catch (ApiException $e) {
            $this->assertSame($statut, $e->status);
            $this->assertSame($code, $e->errorCode);
            $this->assertNotSame('', $e->getMessage(), 'Le message reste destiné à l\'utilisateur.');
        }
    }

    public function test_accept_sur_une_course_deja_prise(): void
    {
        $premier = Driver::factory()->create();
        $second = Driver::factory()->create();
        $booking = Booking::factory()->confirmed($premier)->create();

        $this->assertRefus(
            fn () => app(AcceptBooking::class)($booking->id, $second->id),
            409,
            'BOOKING_ALREADY_TAKEN',
        );
    }

    public function test_accept_sur_une_course_invisible_pour_cet_agent(): void
    {
        $titulaire = Driver::factory()->create();
        $autre = Driver::factory()->create();
        $parent = Booking::factory()
            ->subscriptionParent()
            ->linkedToSubscriptionDriver($titulaire)
            ->create();

        $this->assertRefus(
            fn () => app(AcceptBooking::class)($parent->id, $autre->id),
            404,
            'NOT_FOUND',
        );
    }

    public function test_cancel_sur_la_course_d_un_autre_agent(): void
    {
        $proprietaire = Driver::factory()->create();
        $autre = Driver::factory()->create();
        $booking = Booking::factory()->confirmed($proprietaire)->create();

        $this->assertRefus(
            fn () => app(CancelBooking::class)($booking->id, $autre->id, 'Motif'),
            404,
            'NOT_FOUND',
        );
    }

    public function test_cancel_sur_une_course_terminee(): void
    {
        $driver = Driver::factory()->create();
        $booking = Booking::factory()->completed($driver)->create();

        $this->assertRefus(
            fn () => app(CancelBooking::class)($booking->id, $driver->id, 'Motif'),
            409,
            'BOOKING_NOT_CANCELLABLE',
        );
    }

    public function test_start_sur_une_course_qui_n_est_pas_la_sienne(): void
    {
        $proprietaire = Driver::factory()->create();
        $autre = Driver::factory()->create();
        $booking = Booking::factory()->confirmed($proprietaire)->create();

        $this->assertRefus(
            fn () => app(StartBooking::class)($booking->id, $autre->id),
            409,
            'BOOKING_NOT_STARTABLE',
        );
    }

    public function test_start_avec_une_course_deja_en_cours(): void
    {
        $driver = Driver::factory()->create();
        Booking::factory()->inProgress($driver)->create(['pickup_date' => now()->addDays(2)->toDateString()]);
        $suivante = Booking::factory()->confirmed($driver)->create(['pickup_date' => now()->addDays(3)->toDateString()]);

        $this->assertRefus(
            fn () => app(StartBooking::class)($suivante->id, $driver->id),
            409,
            'BOOKING_ALREADY_IN_PROGRESS',
        );
    }

    public function test_start_avec_des_courses_precedentes_a_solder(): void
    {
        // Depuis la correction du défaut de hasBlockingPreviousBookings, une course
        // antérieure du MÊME JOUR bloque elle aussi. Ce test prend ce cas, qui est le
        // plus fréquent en pratique.
        $driver = Driver::factory()->create();
        Booking::factory()->confirmed($driver)->create([
            'pickup_date' => now()->addDay()->toDateString(), 'pickup_time' => '07:00',
        ]);
        $visee = Booking::factory()->confirmed($driver)->create([
            'pickup_date' => now()->addDay()->toDateString(), 'pickup_time' => '10:00',
        ]);

        $this->assertRefus(
            fn () => app(StartBooking::class)($visee->id, $driver->id),
            409,
            'BOOKING_PREVIOUS_PENDING',
        );
    }

    public function test_complete_sur_une_course_non_demarree(): void
    {
        $driver = Driver::factory()->create();
        $booking = Booking::factory()->confirmed($driver)->create();

        $this->assertRefus(
            fn () => app(CompleteBooking::class)($booking->id, $driver->id),
            409,
            'BOOKING_NOT_COMPLETABLE',
        );
    }

    public function test_revoke_par_quelqu_un_qui_n_est_pas_le_titulaire(): void
    {
        $titulaire = Driver::factory()->create();
        $autre = Driver::factory()->create();
        $parent = Booking::factory()->subscriptionParent()->create();
        $enfant = Booking::factory()
            ->subscriptionChild($parent)
            ->linkedToSubscriptionDriver($titulaire)
            ->create();

        $this->assertRefus(
            fn () => app(RevokeFromSubscription::class)($enfant->id, $autre->id),
            404,
            'NOT_FOUND',
        );
    }

    public function test_revoke_sur_un_statut_hors_pending_et_confirmed(): void
    {
        $titulaire = Driver::factory()->create();
        $parent = Booking::factory()->subscriptionParent()->create();
        $enfant = Booking::factory()
            ->subscriptionChild($parent)
            ->linkedToSubscriptionDriver($titulaire)
            ->completed($titulaire)
            ->create();

        $this->assertRefus(
            fn () => app(RevokeFromSubscription::class)($enfant->id, $titulaire->id),
            409,
            'BOOKING_NOT_REVOCABLE',
        );
    }
}
