<?php

namespace App\Services;

use App\Models\VehicleContract;

class VehicleContractService
{
    public function create(array $data): VehicleContract
    {
        return VehicleContract::create([
            'vehicle_id'      => $data['vehicle_id'],
            'owner_id'        => $data['owner_id'],
            'total_amount'    => $data['total_amount'],
            'monthly_payment' => $data['monthly_payment'],
            'start_date'      => $data['start_date'],
            'end_date'        => $data['end_date'] ?? null,
            'status'          => 'active',
            'notes'           => $data['notes'] ?? null,
        ]);
    }

    public function getStats(VehicleContract $contract): array
    {
        $totalPaid     = (float) $contract->payments()->sum('amount');
        $totalAmount   = (float) $contract->total_amount;
        $remaining     = $totalAmount - $totalPaid;
        $surplus       = $remaining < 0 ? abs($remaining) : 0;
        $remaining     = max(0, $remaining);

        return [
            'total_amount'       => $totalAmount,
            'total_paid'         => $totalPaid,
            'remaining'          => $remaining,
            'surplus'            => $surplus,
            'progress_percent'   => $totalAmount > 0 ? min(100, round(($totalPaid / $totalAmount) * 100)) : 0,
            'payments_count'     => $contract->payments()->count(),
            'monthly_payment'    => (float) $contract->monthly_payment,
        ];
    }
}
