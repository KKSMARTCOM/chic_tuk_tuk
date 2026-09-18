<?php

namespace App\Domains\Finance\Application\Data;

use App\Shared\Data\BaseData;

/**
 * Un mois du récapitulatif que reçoit un propriétaire.
 *
 * `fixedAmount` vaut `validé − charges` et PEUT ÊTRE NÉGATIF quand les charges d'un
 * mois dépassent les paiements validés. C'est le comportement du chemin Blade, transposé
 * sans correction : le corriger au sein d'une migration rendrait impossible d'attribuer
 * un chiffre qui change à la migration plutôt qu'à la correction.
 */
final class MonthlyPayoutData extends BaseData
{
    public function __construct(
        public string $month,
        public bool $isCurrent,
        public float $validatedAmount,
        public float $pendingAmount,
        public float $cancelledAmount,
        public float $totalCharges,
        public float $fixedAmount,
        public int $workedDays,
        public int $agentLeaveDays,
        public int $immobilizationDays,
    ) {}
}
