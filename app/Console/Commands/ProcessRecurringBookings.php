<?php

namespace App\Console\Commands;

use App\Services\BookingService;
use Illuminate\Console\Command;

class ProcessRecurringBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-recurring-bookings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crée les courses du jour suivant pour les réservations récurrentes';

    public function __construct(private BookingService $bookingService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = $this->bookingService->createRecurringBookings();
        $this->info("✓ {$count} réservation(s) récurrente(s) traitée(s).");
        return Command::SUCCESS;
    }
}
