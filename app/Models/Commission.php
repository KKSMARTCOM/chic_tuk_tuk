<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Commission extends Model
{
    use HasUuid, Notifiable, HasFactory;

    protected $fillable = [
        'driver_id',
        'booking_id',
        'amount',
        'is_paid',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'is_paid' => 'boolean',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
