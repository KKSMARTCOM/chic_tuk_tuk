<?php

namespace Tests\Feature\Booking;

use App\Domains\Booking\Application\Data\AssignedBookingData;
use App\Domains\Booking\Application\Data\AvailableBookingData;
use App\Domains\Booking\Application\Data\BookingHistoryData;
use App\Models\Booking;
use App\Models\Driver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_course_disponible_ne_porte_aucune_coordonnee_client(): void
    {
        // La règle de confidentialité, vérifiée sur la SÉRIALISATION et pas seulement sur
        // la liste des propriétés : c'est le JSON qui part au front.
        $booking = Booking::factory()->create([
            'phone' => '+22999887766',
            'client_name' => 'Awa Dossou',
            'special_requests' => 'Bagages volumineux',
        ]);

        $json = AvailableBookingData::fromModel($booking)->toArray();

        foreach (['phone', 'client_name', 'special_requests', 'base_price', 'total_price'] as $interdit) {
            $this->assertArrayNotHasKey($interdit, $json, "« {$interdit} » ne doit pas fuir avant acceptation.");
        }
        $this->assertStringNotContainsString('22999887766', json_encode($json));
        $this->assertStringNotContainsString('Awa Dossou', json_encode($json));
    }

    public function test_la_course_acceptee_porte_les_coordonnees(): void
    {
        $driver = Driver::factory()->create();
        $booking = Booking::factory()->confirmed($driver)->create([
            'phone' => '+22999887766',
            'client_name' => 'Awa Dossou',
        ]);

        $json = AssignedBookingData::fromModel($booking)->toArray();

        $this->assertSame('+22999887766', $json['phone']);
        $this->assertSame('Awa Dossou', $json['client_name']);
    }

    public function test_l_heure_de_prise_en_charge_part_sans_decalage_horaire(): void
    {
        // pickup_date et pickup_time décrivent une heure murale. Un suffixe « +00:00 »
        // ferait afficher 09:00 au lieu de 08:00 dans un navigateur béninois.
        $booking = Booking::factory()->create([
            'pickup_date' => '2026-10-05',
            'pickup_time' => '08:00',
        ]);

        $json = AvailableBookingData::fromModel($booking)->toArray();

        $this->assertSame('2026-10-05T08:00:00', $json['pickup_at']);
        $this->assertStringNotContainsString('+', $json['pickup_at']);
        $this->assertStringNotContainsString('Z', $json['pickup_at']);
    }

    public function test_les_montants_sortent_en_nombres_et_non_en_chaines(): void
    {
        // decimal:2 rend « 5000.00 ». Sans conversion, le front additionnerait des
        // chaînes et personne ne s'en apercevrait avant un total aberrant.
        $driver = Driver::factory()->create();
        $booking = Booking::factory()->confirmed($driver)->create(['base_price' => 5000]);

        $json = AssignedBookingData::fromModel($booking)->toArray();

        $this->assertIsFloat($json['base_price']);
        $this->assertSame(5000.0, $json['base_price']);
    }

    public function test_le_gain_d_une_course_annulee_vaut_zero_et_non_le_prix_total(): void
    {
        // Le `?? total_price` du Blade est une branche morte ; la reproduire ferait
        // apparaître un gain jamais perçu.
        $driver = Driver::factory()->create();
        $booking = Booking::factory()->cancelled($driver)->create(['total_price' => 6000]);

        $json = BookingHistoryData::fromModel($booking)->toArray();

        $this->assertSame(0.0, $json['driver_earning']);
        $this->assertSame(6000.0, $json['total_price']);
    }

    public function test_le_libelle_d_abonnement_n_est_jamais_nul(): void
    {
        $json = AvailableBookingData::fromModel(Booking::factory()->create())->toArray();

        $this->assertSame('Course unique', $json['subscription_label']);
    }

    public function test_les_jours_de_circulation_sortent_en_chaine(): void
    {
        // La colonne est un string parmi lun_ven / lun_sam / lun_dim.
        $parent = Booking::factory()->subscriptionParent()->create(['week_days' => 'lun_sam']);

        $json = AvailableBookingData::fromModel($parent)->toArray();

        $this->assertSame('lun_sam', $json['week_days']);
        $this->assertIsString($json['week_days']);
    }

    public function test_la_duree_d_une_course_terminee_est_en_secondes(): void
    {
        // En secondes et non en minutes : la vue Blade affiche « 00:42:07 » via
        // gmdate('H:i:s'), et des minutes perdraient les secondes.
        $driver = Driver::factory()->create();
        $booking = Booking::factory()->completed($driver)->create([
            'started_at' => now()->subMinutes(42)->subSeconds(7),
            'completed_at' => now(),
        ]);

        $json = BookingHistoryData::fromModel($booking)->toArray();

        $this->assertSame(42 * 60 + 7, $json['duration_seconds']);
    }

    public function test_une_course_non_terminee_n_a_pas_de_duree(): void
    {
        $driver = Driver::factory()->create();
        $booking = Booking::factory()->cancelled($driver)->create();

        $this->assertNull(BookingHistoryData::fromModel($booking)->toArray()['duration_seconds']);
    }

    public function test_on_ne_revoque_que_les_enfants_d_abonnement_dont_on_est_titulaire(): void
    {
        // Transposé du Blade au caractère près :
        //   @if ($isChild && $booking->subscription_driver_id === auth()->user()->driver?->id)
        //
        // On n'accepte PAS un abonnement parent pour le révoquer : on le prend comme une
        // course ordinaire, et le titulaire voit ensuite les enfants générés par le cron.
        // Ce sont eux, et eux seuls, qu'il peut rendre.
        $titulaire = Driver::factory()->create();
        $autre = Driver::factory()->create();
        $parent = Booking::factory()->subscriptionParent()->linkedToSubscriptionDriver($titulaire)->create();

        $enfant = Booking::factory()->subscriptionChild($parent)
            ->linkedToSubscriptionDriver($titulaire)->create()->fresh();

        // L'enfant, vu par son titulaire : révocable.
        $this->assertTrue(
            AvailableBookingData::fromModel($enfant, $titulaire->id)->toArray()['can_be_revoked'],
            "Le titulaire doit pouvoir révoquer l'enfant d'abonnement qui lui est lié."
        );

        // Le même enfant, vu par un autre agent : pas révocable.
        $this->assertFalse(
            AvailableBookingData::fromModel($enfant, $autre->id)->toArray()['can_be_revoked'],
            "Un autre agent ne révoque pas l'enfant d'abonnement d'autrui."
        );

        // L'abonnement PARENT, vu par son titulaire : PAS révocable.
        $this->assertFalse(
            AvailableBookingData::fromModel($parent->fresh(), $titulaire->id)->toArray()['can_be_revoked'],
            "Un abonnement parent ne se révoque pas : il s'accepte comme une course."
        );

        // Une course unique : pas révocable non plus.
        $this->assertFalse(
            AvailableBookingData::fromModel(Booking::factory()->create(), $titulaire->id)->toArray()['can_be_revoked']
        );
    }

    public function test_la_course_parente_est_identifiee_pour_l_ecran(): void
    {
        // Le Blade affiche « Abonnement CTT-XXXXXXXX » sur un enfant et
        // « Course aller : CTT-XXXXXXXX · <date> » sur un retour simple. Sans ces
        // champs, l'agent ne sait pas à quoi la course se rattache.
        $aller = Booking::factory()->roundTrip('18:00')->create([
            'pickup_date' => '2026-10-05',
            'pickup_time' => '08:00',
        ]);
        $retour = Booking::factory()->returnOf($aller)->create()->fresh();

        $json = AvailableBookingData::fromModel($retour)->toArray();

        $this->assertSame($aller->booking_number, $json['parent_booking_number']);
        $this->assertSame('2026-10-05T08:00:00', $json['parent_pickup_at']);
    }

    public function test_une_course_sans_parent_ne_porte_aucune_reference(): void
    {
        $json = AvailableBookingData::fromModel(Booking::factory()->create())->toArray();

        $this->assertNull($json['parent_booking_number']);
        $this->assertNull($json['parent_pickup_at']);
    }
}
