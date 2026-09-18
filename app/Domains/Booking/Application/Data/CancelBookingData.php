<?php

namespace App\Domains\Booking\Application\Data;

use App\Shared\Data\BaseData;
use Spatie\LaravelData\Support\Validation\ValidationContext;

/**
 * Le corps de POST /driver/bookings/{id}/cancel.
 *
 * Le motif est OBLIGATOIRE côté API, alors que le chemin Blade lui substitue « Annulée
 * par le Agent » quand le champ est vide. C'est délibéré : le front demande le motif dans
 * la fenêtre de confirmation et ne laisse pas valider à vide, et un motif par défaut
 * rendrait indiscernables une annulation motivée et une annulation muette.
 */
final class CancelBookingData extends BaseData
{
    public function __construct(
        public string $cancellationReason,
    ) {}

    /**
     * @return array<string, array<int, string>>
     *
     * La signature prend un ValidationContext, comme CreatePublicBookingData : c'est ce
     * que spatie/laravel-data ^4 passe, et une méthode sans paramètre serait ignorée en
     * silence — la validation ne s'appliquerait alors jamais.
     */
    public static function rules(ValidationContext $context): array
    {
        return [
            'cancellation_reason' => ['required', 'string', 'min:3', 'max:500'],
        ];
    }
}
