<?php

namespace App\Domains\Fleet\Application\Data;

use App\Models\Vehicle;
use App\Shared\Data\BaseData;

final class OwnerVehicleSummaryData extends BaseData
{
    public function __construct(
        public string $id,
        public string $vehicleNumber,
        public ?string $vehicleType,
        public bool $isOnPause,
        public ?OwnerContractSummaryData $contract,
    ) {}

    /** Attend un véhicule ayant chargé `activeVehicleContract` et `activePause`. */
    public static function fromModel(Vehicle $vehicle): self
    {
        $contract = $vehicle->activeVehicleContract;

        return new self(
            id: $vehicle->id,
            vehicleNumber: $vehicle->vehicle_number,
            vehicleType: $vehicle->vehicle_type,
            isOnPause: $vehicle->activePause !== null,
            // Le véhicule peut n'avoir aucun contrat actif : c'est un cas réel, que le
            // Blade traite déjà par « Aucun contrat actif pour ce véhicule ».
            contract: $contract ? OwnerContractSummaryData::fromModel($contract) : null,
        );
    }
}
