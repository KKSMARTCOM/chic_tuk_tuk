<?php

namespace App\Domains\Booking\Domain\Enums;

use App\Shared\Enums\HasOptions;

/**
 * Jours de circulation d'un abonnement — colonne `bookings.week_days`.
 * Les libellés sont ceux affichés dans le formulaire de réservation public.
 */
enum WeekDays: string
{
    use HasOptions;

    case LunVen = 'lun_ven';
    case LunSam = 'lun_sam';
    case LunDim = 'lun_dim';

    public function label(): string
    {
        return match ($this) {
            self::LunVen => 'Lun → Ven (5j/7)',
            self::LunSam => 'Lun → Sam (6j/7)',
            self::LunDim => 'Lun → Dim (7j/7)',
        };
    }

    /**
     * Jours de circulation au format Carbon::dayOfWeek — dimanche = 0, PAS la
     * convention ISO où dimanche = 7.
     *
     * Reproduit à l'identique la table de correspondance de getNextAllowedDay()
     * dans app/Helpers/utils.php, que cette méthode a vocation à remplacer.
     * Changer la convention ici décalerait silencieusement toutes les échéances
     * d'abonnement.
     */
    public function daysOfWeek(): array
    {
        return match ($this) {
            self::LunVen => [1, 2, 3, 4, 5],
            self::LunSam => [1, 2, 3, 4, 5, 6],
            self::LunDim => [1, 2, 3, 4, 5, 6, 0],
        };
    }
}
