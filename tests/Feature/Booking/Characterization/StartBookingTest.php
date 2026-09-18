<?php

namespace Tests\Feature\Booking\Characterization;

use App\Models\Booking;
use App\Models\Driver;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Caractérisation de BookingService::start().
 *
 * ⚠️ Trois refus, dont deux se ressemblent. « Vous avez déjà une course en cours » vient
 * d'une requête sur status = 'in_progress' ; « Vous devez terminer ou annuler toutes les
 * courses précédentes » vient de Driver::hasBlockingPreviousBookings(), qui compare
 * CONCAT(pickup_date, ' ', pickup_time). Une course ANTÉRIEURE encore `confirmed` suffit
 * à bloquer, sans qu'aucune course ne soit en cours.
 */
class StartBookingTest extends TestCase
{
    use RefreshDatabase;

    private function service(): BookingService
    {
        return app(BookingService::class);
    }

    public function test_une_course_confirmee_passe_in_progress_et_date_son_depart(): void
    {
        $driver = Driver::factory()->create();
        $booking = Booking::factory()->confirmed($driver)->create();

        $this->service()->start($booking->id, $driver->id);

        $booking->refresh();
        $this->assertSame('in_progress', $booking->status);
        $this->assertNotNull($booking->started_at);
    }

    public function test_la_course_d_un_autre_agent_est_refusee(): void
    {
        $proprietaire = Driver::factory()->create();
        $autre = Driver::factory()->create();
        $booking = Booking::factory()->confirmed($proprietaire)->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Démarrage non autorisé.');

        $this->service()->start($booking->id, $autre->id);
    }

    public function test_une_course_deja_en_cours_bloque_le_demarrage_d_une_autre(): void
    {
        // Premier refus : une course `in_progress` existe pour cet agent.
        $driver = Driver::factory()->create();
        Booking::factory()->inProgress($driver)->create([
            'pickup_date' => now()->addDays(2)->toDateString(),
        ]);
        $suivante = Booking::factory()->confirmed($driver)->create([
            'pickup_date' => now()->addDays(3)->toDateString(),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Vous avez déjà une course en cours.');

        $this->service()->start($suivante->id, $driver->id);
    }

    public function test_une_course_d_un_jour_anterieur_non_soldee_bloque_le_demarrage(): void
    {
        // Second refus, DISTINCT du premier : aucune course n'est en cours, mais une
        // course d'un jour antérieur reste `confirmed`.
        $driver = Driver::factory()->create();
        Booking::factory()->confirmed($driver)->create([
            'pickup_date' => now()->addDay()->toDateString(),
            'pickup_time' => '07:00',
        ]);
        $visee = Booking::factory()->confirmed($driver)->create([
            'pickup_date' => now()->addDays(2)->toDateString(),
            'pickup_time' => '10:00',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Vous devez terminer ou annuler toutes les courses précédentes avant de démarrer celle-ci.');

        $this->service()->start($visee->id, $driver->id);
    }

    public function test_une_course_anterieure_du_MEME_JOUR_ne_bloque_pas(): void
    {
        // ⚠️ Ce test verrouille un DÉFAUT du code existant, pas une règle voulue.
        //
        // hasBlockingPreviousBookings() compare deux chaînes qui ne sont pas construites
        // de la même façon :
        //   - à gauche, SQL : CONCAT(pickup_date, ' ', pickup_time) → "2026-09-19 07:00:00"
        //   - à droite, PHP : $booking->pickup_date . ' ' . $booking->pickup_time
        //
        // `pickup_date` est casté en `date`, donc en Carbon, et sa conversion en chaîne
        // rend "2026-09-19 00:00:00". Le côté droit vaut donc
        // "2026-09-19 00:00:00 10:00" — une date, minuit, puis l'heure collée derrière.
        //
        // La comparaison lexicographique bute alors au douzième caractère : '7' > '0'.
        // Une course du même jour à 07:00 n'est PAS vue comme antérieure à celle de
        // 10:00. Seul un jour strictement antérieur bloque.
        //
        // Transposé tel quel, signalé dans le plan (écart E8). Le corriger changerait
        // qui peut démarrer quoi, et mérite sa propre décision.
        $driver = Driver::factory()->create();
        Booking::factory()->confirmed($driver)->create([
            'pickup_date' => now()->addDay()->toDateString(),
            'pickup_time' => '07:00',
        ]);
        $visee = Booking::factory()->confirmed($driver)->create([
            'pickup_date' => now()->addDay()->toDateString(),
            'pickup_time' => '10:00',
        ]);

        $this->service()->start($visee->id, $driver->id);

        $this->assertSame('in_progress', $visee->fresh()->status);
    }
}
