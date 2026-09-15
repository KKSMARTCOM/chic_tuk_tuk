<?php

namespace App\Domains\Finance\Domain\Enums;

use App\Shared\Enums\HasOptions;

/**
 * Statut d'un paiement — colonne `payments.status`.
 * Source de vérité : contrainte CHECK `payments_status_check`.
 */
enum PaymentStatus: string
{
    use HasOptions;

    case Pending   = 'pending';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Failed    = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'En attente',
            self::Completed => 'Validé',
            self::Cancelled => 'Annulé',
            self::Failed    => 'Échoué',
        };
    }
}
