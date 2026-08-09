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
        'payment_type',         // nouveau
        'vehicle_contract_id',  // nouveau
        'driver_contract_id',   // nouveau
        'payment_month',        // nouveau
        'gross_amount',         // nouveau
        'status',               // nouveau
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
        'gross_amount' => 'decimal:2', // nouveau
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
