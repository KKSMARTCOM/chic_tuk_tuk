<?php

namespace App\Domains\Fleet\Application\Data;

use App\Models\Vehicle;
use App\Shared\Data\BaseData;

/**
 * Le cumul et l'historique, réunis parce que l'écran les montre ensemble.
 *
 * ⚠️ Les deux ne mesurent pas la même chose, et c'est ainsi depuis le Blade : le cumul
 * vient d'accesseurs du contrat qui comptent les CONGÉS D'AGENT, tandis que
 * l'historique liste les lignes de `vehicle_pauses`. Ne pas « réconcilier » les deux
 * ici : ce serait changer des chiffres affichés au prétexte d'une migration.
 */
final class OwnerVehiclePausesData extends BaseData
{
    /** @param list<VehiclePauseData> $items */
    public function __construct(
        public ?OwnerContractPauseSummaryData $summary,
        public array $items,
    ) {}

    /** Attend un véhicule ayant chargé `pauses` et `activeVehicleContract`. */
    public static function fromModel(Vehicle $vehicle): self
    {
        $contract = $vehicle->activeVehicleContract;

        return new self(
            summary: $contract ? OwnerContractPauseSummaryData::fromModel($contract) : null,
            items: $vehicle->pauses
                ->map(fn ($pause) => VehiclePauseData::fromModel($pause))
                ->all(),
        );
    }
}
