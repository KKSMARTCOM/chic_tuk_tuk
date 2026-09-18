<?php

namespace App\Domains\Fleet\Application\Actions;

use App\Domains\Fleet\Application\Data\OwnerVehicleSummaryData;
use App\Models\Vehicle;
use Illuminate\Support\Collection;

/**
 * Les véhicules d'un propriétaire, avec ce que la liste affiche.
 *
 * Reprend VehicleService::getOwnerVehiclesWithStats, qui renvoyait des objets anonymes
 * destinés à une vue Blade. Ce service reste en place tant que le Blade sert les
 * propriétaires ; il part avec lui, à la bascule.
 */
final class ListOwnerVehicles
{
    /** @return Collection<int, OwnerVehicleSummaryData> */
    public function __invoke(string $ownerId): Collection
    {
        return Vehicle::query()
            ->where('owner_id', $ownerId)
            ->with(['activeVehicleContract', 'activePause'])
            ->latest()
            ->get()
            ->map(fn (Vehicle $vehicle) => OwnerVehicleSummaryData::fromModel($vehicle));
    }
}
