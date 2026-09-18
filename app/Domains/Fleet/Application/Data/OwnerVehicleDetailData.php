<?php

namespace App\Domains\Fleet\Application\Data;

use App\Models\Vehicle;
use App\Shared\Data\BaseData;

final class OwnerVehicleDetailData extends BaseData
{
    public function __construct(
        public string $id,
        public string $vehicleNumber,
        public ?string $vehicleType,
        public ?ActivePauseData $activePause,
        public ?OwnerContractDetailData $contract,
    ) {}

    /** Attend un véhicule ayant chargé `activeVehicleContract` et `activePause`. */
    public static function fromModel(Vehicle $vehicle): self
    {
        $pause = $vehicle->activePause;
        $contract = $vehicle->activeVehicleContract;

        return new self(
            id: $vehicle->id,
            vehicleNumber: $vehicle->vehicle_number,
            vehicleType: $vehicle->vehicle_type,
            activePause: $pause ? ActivePauseData::fromModel($pause) : null,
            contract: $contract ? OwnerContractDetailData::fromModel($contract) : null,
        );
    }
}
