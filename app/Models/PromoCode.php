<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class PromoCode extends Model
{
    use HasUuid, Notifiable, HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'max_uses',
        'used_count',
        'valid_from',
        'valid_until',
        'is_active'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function isValid()
    {
        return $this->is_active &&
            now()->between($this->valid_from, $this->valid_until) &&
            ($this->max_uses === null || $this->used_count < $this->max_uses);
    }

    public function applyDiscount($price)
    {
        if ($this->type === 'percentage') {
            return $price * ($this->value / 100);
        }
        return min($this->value, $price);
    }
}
