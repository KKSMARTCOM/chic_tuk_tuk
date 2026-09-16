<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class TouristCircuit extends Model
{
    use HasUuid, Notifiable, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'locations',
        'price',
        'duration',
        'image',
        'is_active'
    ];

    protected $casts = [
        'locations' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
