<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasUuid, HasFactory;

    protected $fillable = [
        'driver_id',
        'vehicle_contract_id',  // nouveau
        'driver_contract_id',   // nouveau
        'payment_month',        // nouveau
        'amount',
        'payment_method',
        'payment_date',
        'notes',
        'reference_number',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'payment_month' => 'date', // nouveau
        'amount' => 'decimal:2',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicleContract()
    {
        return $this->belongsTo(VehicleContract::class);
    }

    public function driverContract()
    {
        return $this->belongsTo(DriverContract::class);
    }
}
