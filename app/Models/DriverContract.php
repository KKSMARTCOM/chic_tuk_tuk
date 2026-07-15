<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class DriverContract extends Model
{
    use HasUuid;

    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'vehicle_contract_id',
        'start_date',
        'end_date',
        'contract_months',
        'status',
        'end_reason',
        'end_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function vehicleContract()
    {
        return $this->belongsTo(VehicleContract::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function vehiclePauses()
    {
        return $this->hasMany(VehiclePause::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && is_null($this->end_date);
    }

    // Mois écoulés depuis le début du contrat agent
    public function getMonthsElapsedAttribute(): int
    {
        $end = $this->end_date ?? now();
        return min(
            $this->start_date->diffInMonths($end) + 1,
            $this->contract_months
        );
    }

    // Jours de pause acquis sur ce contrat
    public function getAccruedLeaveDaysAttribute(): int
    {
        return 2 * $this->months_elapsed; // 2j/mois
    }

    // Jours de pause utilisés sur ce contrat
    public function getUsedLeaveDaysAttribute(): int
    {
        return $this->leaveRequests()
            ->where('status', 'approved')
            ->get()
            ->sum(fn($lr) => count($lr->dates ?? []));
    }

    // Jours disponibles (sans restriction de dépassement)
    public function getAvailableLeaveDaysAttribute(): int
    {
        return $this->accrued_leave_days - $this->used_leave_days;
        // Peut être négatif si surplus
    }

    // Paiements effectués sur ce contrat agent
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }
}
