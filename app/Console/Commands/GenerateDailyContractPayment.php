<?php

namespace App\Console\Commands;

use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateDailyContractPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-daily {--date= : Date au format Y-m-d (défaut: aujourd\'hui)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génère les paiements journaliers sur contrat pour chaque agent actif';

    public function __construct(private PaymentService $paymentService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::today();

        $this->info("[{$date->toDateString()}] Génération des paiements journaliers...");

        $result = $this->paymentService->generateDailyContractPayments($date);

        foreach ($result['errors'] as $error) {
            $this->error("  ✗ Contrat #{$error['contract_id']} — {$error['message']}");
        }

        $this->info("Terminé — {$result['generated']} généré(s), {$result['skipped']} ignoré(s), " . count($result['errors']) . " erreur(s).");

        return count($result['errors']) > 0 ? self::FAILURE : self::SUCCESS;
    }
}
