<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class VehicleContract extends Model
{
    use HasUuid;

    protected $fillable = [
        'vehicle_id',
        'owner_id',
        'total_amount',
        'monthly_payment',
        'start_date',
        'end_date',
        'status',
        'notes',
        'contract_months',
        'unlimited_internet',
        'spotify_premium',
        'manager_remuneration',
    ];

    protected $casts = [
        'total_amount'    => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'contract_months' => 'integer',
        'unlimited_internet' => 'decimal:2',
        'spotify_premium' => 'decimal:2',
        'manager_remuneration' => 'decimal:2',
        'start_date'      => 'date',
        'end_date'        => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function driverContracts()
    {
        return $this->hasMany(DriverContract::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function pauses()
    {
        return $this->hasMany(VehiclePause::class);
    }

    // Montant total déjà payé
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('net_amount');
    }

    // Montant restant à payer
    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->total_amount - $this->total_paid);
    }

    // Surplus si trop payé
    public function getSurplusAttribute(): float
    {
        $diff = (float) $this->total_paid - (float) $this->total_amount;
        return max(0, $diff);
    }

    // Pourcentage remboursé
    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_amount <= 0) return 0;
        return (int) min(100, round(($this->total_paid / $this->total_amount) * 100));
    }
}
