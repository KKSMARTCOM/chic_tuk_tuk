<?php

namespace App\Console\Commands;

use App\Services\BookingService;
use Illuminate\Console\Command;

class ExpireBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expire-bookings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marque comme expirées les courses dont la date de départ est dépassée';

    public function __construct(private BookingService $bookingService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = $this->bookingService->markExpiredBookings();
        $this->info("✓ {$count} réservation(s) marquée(s) comme expirées.");
        return Command::SUCCESS;
    }
}
