<?php

namespace App\Domains\Booking\Application\Data;

use App\Shared\Data\BaseData;
use Spatie\LaravelData\Support\Validation\ValidationContext;

/**
 * Paramètres d'un devis de course (endpoint public du tunnel de réservation).
 *
 * Les coordonnées sont bornées au Bénin : le formulaire public ne propose que des
 * villes béninoises (autocomplétion Nominatim avec countrycodes=bj) et chaque appel
 * consomme le quota OpenRouteService — refuser tôt les coordonnées hors zone évite
 * de le gaspiller.
 */
class CalculatePriceData extends BaseData
{
    public function __construct(
        public float   $fromLng,
        public float   $fromLat,
        public float   $toLng,
        public float   $toLat,
        public ?string $pickupTime = null,
        public ?string $returnTime = null,
        public bool    $roundTrip = false,
        public int     $days = 1,
    ) {}

    /**
     * En query string, tout arrive sous forme de chaîne : `round_trip=true` est ce que
     * produit naturellement URLSearchParams côté JS, alors que la règle `boolean` de
     * Laravel ne reconnaît que 1/0/true/false typés. On normalise avant validation.
     */
    public static function prepareForPipeline(array $properties): array
    {
        if (isset($properties['round_trip']) && is_string($properties['round_trip'])) {
            $properties['round_trip'] = filter_var(
                $properties['round_trip'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE,
            );
        }

        return $properties;
    }

    public static function rules(ValidationContext $context): array
    {
        return [
            'from_lng'     => ['required', 'numeric', 'between:0,4'],
            'from_lat'     => ['required', 'numeric', 'between:6,13'],
            'to_lng'       => ['required', 'numeric', 'between:0,4'],
            'to_lat'       => ['required', 'numeric', 'between:6,13'],
            'pickup_time'  => ['nullable', 'date_format:H:i'],
            'return_time'  => ['nullable', 'date_format:H:i'],
            'round_trip'   => ['nullable', 'boolean'],
            'days'         => ['nullable', 'integer', 'min:1', 'max:366'],
        ];
    }

    public static function messages(): array
    {
        return [
            'from_lng.required' => 'Veuillez choisir une ville de départ dans la liste de suggestions.',
            'from_lat.required' => 'Veuillez choisir une ville de départ dans la liste de suggestions.',
            'to_lng.required'   => 'Veuillez choisir une destination dans la liste de suggestions.',
            'to_lat.required'   => 'Veuillez choisir une destination dans la liste de suggestions.',
            'from_lng.between' => 'Le point de départ est en dehors de la zone desservie.',
            'from_lat.between' => 'Le point de départ est en dehors de la zone desservie.',
            'to_lng.between'   => 'La destination est en dehors de la zone desservie.',
            'to_lat.between'   => 'La destination est en dehors de la zone desservie.',
        ];
    }
}
