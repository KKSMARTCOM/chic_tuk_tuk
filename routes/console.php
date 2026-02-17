<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Traiter les réservations expirées toutes les heures
Schedule::command('bookings:process --expired')->hourly();

// Traiter les réservations récurrentes toutes les heures
Schedule::command('bookings:process --recurring')->hourly();
