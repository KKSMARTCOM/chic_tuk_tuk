<?php

namespace Tests\Feature\Booking;

use App\Models\Booking;
use App\Models\Driver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_forme_de_base_s_insere(): void
    {
        $booking = Booking::factory()->create();

        $this->assertNotNull($booking->id);
        $this->assertSame('pending', $booking->status);
        $this->assertFalse($booking->is_recurring);
    }

    public function test_le_numero_de_course_est_pose_par_le_modele(): void
    {
        // Le poser à la fabrique n'aurait aucun effet : Booking::boot() l'écrase.
        $booking = Booking::factory()->create();

        $this->assertStringStartsWith('CTT-', $booking->booking_number);
    }

    public function test_un_enfant_d_abonnement_n_est_pas_lui_meme_recurrent(): void
    {
        $parent = Booking::factory()->subscriptionParent()->create();
        $child = Booking::factory()->subscriptionChild($parent)->create();

        $this->assertTrue($parent->is_subscription_parent);
        $this->assertFalse($child->is_recurring);
        $this->assertTrue($child->fresh()->is_subscription_child);
    }

    public function test_une_course_retour_simple_est_reconnue_comme_telle(): void
    {
        $aller = Booking::factory()->roundTrip()->create();
        $retour = Booking::factory()->returnOf($aller)->create();

        $this->assertTrue($retour->fresh()->is_simple_return);
        $this->assertSame($aller->to_location, $retour->from_location);
    }

    public function test_titulaire_d_abonnement_et_agent_acceptant_sont_deux_colonnes(): void
    {
        $driver = Driver::factory()->create();
        $booking = Booking::factory()
            ->subscriptionParent()
            ->linkedToSubscriptionDriver($driver)
            ->create();

        $this->assertSame($driver->id, $booking->subscription_driver_id);
        $this->assertNull($booking->driver_id);
        $this->assertSame('pending', $booking->status);
    }

    public function test_une_course_annulee_porte_un_gain_de_zero_et_non_null(): void
    {
        // Les colonnes sont NOT NULL DEFAULT 0. Le `?? total_price` de la vue Blade
        // d'historique ne se déclenche donc jamais.
        $driver = Driver::factory()->create();
        $booking = Booking::factory()->cancelled($driver)->create()->fresh();

        $this->assertNotNull($booking->driver_earning);
        $this->assertSame(0.0, (float) $booking->driver_earning);
    }
}
