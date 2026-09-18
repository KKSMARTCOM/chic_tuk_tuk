<?php

namespace App\Domains\Fleet\Application\Data;

use App\Models\VehicleContract;
use App\Shared\Data\BaseData;

/**
 * Le contrat véhicule complet, tel que l'onglet Aperçu l'affiche.
 *
 * Tous ces champs existent déjà comme accesseurs sur VehicleContract. Cette classe les
 * EXPOSE, elle ne les recalcule pas : dupliquer ici le calcul des mois écoulés ou de la
 * date de fin ajustée créerait deux vérités pour un même chiffre.
 */
final class OwnerContractDetailData extends BaseData
{
    public function __construct(
        public int $contractMonths,
        public string $startDate,
        public ?string $plannedEndDate,
        public ?string $extendedEndDate,
        public float $totalAmount,
        public float $totalPaid,
        public float $remainingAmount,
        public float $dailyNetAmount,
        public int $progressPercentage,
        public int $monthsElapsed,
        public int $monthsRemaining,
        public float $totalCharges,
        public float $unlimitedInternet,
        public float $spotifyPremium,
        public float $managerRemuneration,
        public int $totalContractDays,
        public int $totalPauseDaysTaken,
        public int $remainingContractDays,
        public int $pauseUsagePercentage,
    ) {}

    public static function fromModel(VehicleContract $contract): self
    {
        return new self(
            contractMonths: (int) $contract->contract_months,
            startDate: $contract->start_date->toDateString(),
            plannedEndDate: $contract->planned_end_date?->toDateString(),
            extendedEndDate: $contract->extended_end_date?->toDateString(),
            totalAmount: (float) $contract->total_amount,
            totalPaid: $contract->total_paid,
            remainingAmount: $contract->remaining_amount,
            dailyNetAmount: $contract->daily_net_amount,
            progressPercentage: $contract->progress_percentage,
            monthsElapsed: $contract->months_elapsed,
            monthsRemaining: $contract->months_remaining,
            totalCharges: $contract->total_charges,
            // `?? 0` : les colonnes ont un défaut à 0 en base, mais les contrats créés
            // avant la migration du 5 août portent des nulls.
            unlimitedInternet: (float) ($contract->unlimited_internet ?? 0),
            spotifyPremium: (float) ($contract->spotify_premium ?? 0),
            managerRemuneration: (float) ($contract->manager_remuneration ?? 0),
            totalContractDays: $contract->total_contract_days,
            // ⚠️ Malgré son nom, cet accesseur compte les jours de CONGÉ D'AGENT sur les
            // contrats agents rattachés au contrat véhicule, et non les lignes de
            // `vehicle_pauses`. Le Blade l'affiche déjà sous l'étiquette « Jours de
            // pause pris ». Transposé tel quel : le corriger changerait un chiffre
            // affiché, au prétexte d'une migration.
            totalPauseDaysTaken: $contract->total_pause_days_taken,
            remainingContractDays: $contract->remaining_contract_days,
            pauseUsagePercentage: $contract->pause_usage_percentage,
        );
    }
}
