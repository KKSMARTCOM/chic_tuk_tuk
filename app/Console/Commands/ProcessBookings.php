<?php

namespace App\Console\Commands;

use App\Services\BookingService;
use Illuminate\Console\Command;

class ProcessBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:process {--expired} {--recurring}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Traite les réservations expirées et récurrentes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(private BookingService $bookingService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hasOptions = $this->option('expired') || $this->option('recurring');

        if (!$hasOptions || $this->option('expired')) {
            $expiredCount = $this->bookingService->markExpiredBookings();
            $this->info("✓ {$expiredCount} réservations marquées comme expirées.");
        }

        if (!$hasOptions || $this->option('recurring')) {
            $recurringCount = $this->bookingService->createRecurringBookings();
            $this->info("✓ {$recurringCount} réservations récurrentes traitées.");
        }

        return Command::SUCCESS;
    }
}
