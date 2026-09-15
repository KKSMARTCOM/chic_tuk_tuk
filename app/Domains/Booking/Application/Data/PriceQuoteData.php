<?php

namespace App\Domains\Booking\Application\Data;

use App\Shared\Data\BaseData;

/**
 * Devis détaillé d'une course.
 *
 * Le détail complet est renvoyé pour que le front n'ait AUCUN calcul à refaire :
 * le récapitulatif du tunnel de réservation se contente d'afficher ces valeurs.
 *
 * C'est délibéré. Le formulaire Blade actuel recalcule la majoration horaire en
 * JavaScript (pages/index.blade.php) et a divergé du serveur : il applique la
 * tranche 7h–10h alors que Price::NORMAL_WINDOW_START_HOUR vaut 6. Centraliser le
 * calcul côté API empêche cette classe de bug de réapparaître.
 */
class PriceQuoteData extends BaseData
{
    public function __construct(
        /** Distance routière en kilomètres, arrondie au supérieur. */
        public float  $distanceKm,
        /** Prix d'un trajet avant majoration horaire. */
        public int    $basePrice,
        /** Prix de l'aller, majoration horaire comprise. */
        public int    $goPrice,
        /** Prix du retour, null si trajet simple. */
        public ?int   $returnPrice,
        /** Aller (+ retour le cas échéant) pour une journée. */
        public int    $tripPrice,
        public int    $days,
        /** tripPrice × days pour un abonnement, tripPrice sinon. */
        public int    $totalPrice,
        /** Montant de la majoration appliquée hors tranche horaire normale. */
        public int    $surchargeAmount,
        /** Tranche horaire sans majoration, pour l'affichage — ex. « 6h–10h ». */
        public string $surchargeFreeWindow,
    ) {}
}
