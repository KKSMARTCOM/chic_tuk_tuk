<?php

namespace App\Domains\Booking\Application\Data;

use App\Shared\Data\BaseData;

/**
 * Devis détaillé d'une course.
 *
 * Le détail complet est renvoyé pour que le front n'ait AUCUN calcul à refaire :
 * le récapitulatif du tunnel de réservation se contente d'afficher ces valeurs.
 *
 * C'est délibéré. Le formulaire Blade recopie les constantes de Price dans son
 * JavaScript : deux implémentations de la même règle tarifaire, qu'il faut penser à
 * modifier ensemble. Le texte affiché avait d'ailleurs fini par annoncer une tranche
 * (7h–10h) différente de celle réellement appliquée (6h–10h). Centraliser le calcul
 * côté API empêche cette classe de bug de réapparaître.
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
