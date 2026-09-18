<?php

namespace Tests\Feature\Booking;

use App\Domains\Booking\Application\Actions\ListBookingHistory;
use App\Models\Booking;
use App\Models\Driver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_ne_renvoie_que_les_courses_terminees_et_annulees_de_l_agent(): void
    {
        $driver = Driver::factory()->create();
        $autre = Driver::factory()->create();
        $terminee = Booking::factory()->completed($driver)->create();
        $annulee = Booking::factory()->cancelled($driver)->create();
        $enCours = Booking::factory()->inProgress($driver)->create();
        $celleDunAutre = Booking::factory()->completed($autre)->create();

        $vu = app(ListBookingHistory::class)($driver->id)->pluck('id')->all();

        $this->assertContains($terminee->id, $vu);
        $this->assertContains($annulee->id, $vu);
        $this->assertNotContains($enCours->id, $vu);
        $this->assertNotContains($celleDunAutre->id, $vu);
    }

    public function test_pagine_par_dix_comme_le_blade(): void
    {
        $driver = Driver::factory()->create();
        Booking::factory()->count(12)->completed($driver)->create();

        $page = app(ListBookingHistory::class)($driver->id);

        $this->assertSame(10, $page->perPage());
        $this->assertSame(12, $page->total());
        $this->assertCount(10, $page->items());
    }

    public function test_la_recherche_porte_sur_le_numero_le_telephone_et_les_deux_lieux(): void
    {
        $driver = Driver::factory()->create();
        $cible = Booking::factory()->completed($driver)->create([
            'phone' => '+22999887766',
            'from_location' => 'Cadjehoun',
            'to_location' => 'Fidjrosse',
        ]);
        Booking::factory()->completed($driver)->create([
            'phone' => '+22900000000',
            'from_location' => 'Akpakpa',
            'to_location' => 'Godomey',
        ]);

        foreach (['99887766', 'Cadjehoun', 'Fidjrosse', $cible->booking_number] as $terme) {
            $vu = app(ListBookingHistory::class)($driver->id, $terme)->pluck('id')->all();

            $this->assertSame([$cible->id], $vu, "La recherche sur « {$terme} » doit isoler la course cible.");
        }
    }

    public function test_l_ordre_est_descendant_le_plus_recent_d_abord(): void
    {
        $driver = Driver::factory()->create();
        $ancienne = Booking::factory()->completed($driver)->create([
            'pickup_date' => now()->subDays(5)->toDateString(),
        ]);
        $recente = Booking::factory()->completed($driver)->create([
            'pickup_date' => now()->subDay()->toDateString(),
        ]);

        $vu = app(ListBookingHistory::class)($driver->id)->pluck('id')->all();

        $this->assertSame([$recente->id, $ancienne->id], $vu);
    }
}
