<?php

namespace App\Domains\Fleet\Application\Data;

use App\Models\VehicleContract;
use App\Shared\Data\BaseData;

final class OwnerContractPauseSummaryData extends BaseData
{
    public function __construct(
        public int $totalContractDays,
        public int $totalPauseDaysTaken,
        public int $remainingContractDays,
        public int $pauseUsagePercentage,
    ) {}

    public static function fromModel(VehicleContract $contract): self
    {
        return new self(
            totalContractDays: $contract->total_contract_days,
            totalPauseDaysTaken: $contract->total_pause_days_taken,
            remainingContractDays: $contract->remaining_contract_days,
            pauseUsagePercentage: $contract->pause_usage_percentage,
        );
    }
}
