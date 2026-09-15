<?php

namespace App\Domains\Finance\Domain\Enums;

use App\Shared\Enums\HasOptions;

/**
 * Nature d'un paiement — colonne `payments.payment_type`.
 *
 * Source de vérité : contrainte CHECK `payments_payment_type_check`, qui autorise
 * quatre valeurs. Les règles de validation actuelles n'en exposent que deux
 * (commission, contract) ; « bonus » et « other » restent réservés.
 */
enum PaymentType: string
{
    use HasOptions;

    case Commission = 'commission';
    case Contract   = 'contract';
    case Bonus      = 'bonus';
    case Other      = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Commission => 'Commission',
            self::Contract   => 'Contrat',
            self::Bonus      => 'Prime',
            self::Other      => 'Autre',
        };
    }
}
