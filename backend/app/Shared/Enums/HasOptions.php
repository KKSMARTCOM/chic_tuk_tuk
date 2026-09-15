<?php

namespace App\Shared\Enums;

/**
 * Utilitaires communs aux énumérations métier.
 *
 * Les valeurs portées par ces énumérations sont celles réellement contraintes en base
 * (contraintes CHECK PostgreSQL) ou imposées par les règles de validation existantes.
 * Elles ne doivent pas être renommées sans migration de données correspondante.
 */
trait HasOptions
{
    /** Valeurs brutes — pour les règles de validation, les filtres et les whereIn(). */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** value => libellé — pour les <select> et les badges du front. */
    public static function options(): array
    {
        return array_combine(
            self::values(),
            array_map(static fn (self $case) => $case->label(), self::cases()),
        );
    }

    abstract public function label(): string;
}
