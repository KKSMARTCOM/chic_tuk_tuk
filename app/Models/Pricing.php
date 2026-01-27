<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Pricing extends Model
{
    use HasUuid, Notifiable, HasFactory;

    protected $table = 'pricing';

    protected $fillable = [
        'from_zone_id',
        'to_zone_id',
        'base_price',
        'price_per_km',
        'estimated_duration'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'price_per_km' => 'decimal:2',
    ];

    public function fromZone()
    {
        return $this->belongsTo(Zone::class, 'from_zone_id');
    }

    public function toZone()
    {
        return $this->belongsTo(Zone::class, 'to_zone_id');
    }
}
