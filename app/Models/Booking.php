<?php

namespace App\Models;

use App\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasUuid, Notifiable, HasFactory;

    protected $fillable = [
        'booking_number',
        'user_id',
        'driver_id',
        'tourist_circuit_id',
        'from_zone_id',
        'to_zone_id',
        'phone',
        'days',
        'remaining_days',
        'pickup_datetime',
        'passengers',
        'special_requests',
        'base_price',
        'discount',
        'total_price',
        'promo_code_id',
        'status',
        'cancellation_reason',
        'started_at',
        'cancelled_at',
        'completed_at',
        'parent_booking_id',
        'is_recurring',
        'next_recurring_date'
    ];

    protected $casts = [
        'pickup_datetime' => 'datetime',
        'started_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
        'next_recurring_date' => 'datetime',
        'base_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'is_recurring' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            $booking->booking_number = 'GZM-' . strtoupper(Str::random(8));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function touristCircuit()
    {
        return $this->belongsTo(TouristCircuit::class);
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function testimonial()
    {
        return $this->hasOne(Testimonial::class);
    }

    public function canBeCancelled()
    {
        return $this->status !== 'completed' &&
            $this->status !== 'cancelled' &&
            $this->status !== 'expired';
    }

    public function canBeCompleted()
    {
        return $this->status === 'in_progress';
    }

    public function fromZone()
    {
        return $this->belongsTo(Zone::class, 'from_zone_id');
    }

    public function toZone()
    {
        return $this->belongsTo(Zone::class, 'to_zone_id');
    }

    public function parentBooking()
    {
        return $this->belongsTo(Booking::class, 'parent_booking_id');
    }

    public function childBookings()
    {
        return $this->hasMany(Booking::class, 'parent_booking_id');
    }

    public function getStartedAtTimestampAttribute()
    {
        return $this->started_at
            ? Carbon::parse($this->started_at)
            ->timezone(config('app.timezone'))
            ->timestamp
            : null;
    }

    public function getDurationAttribute()
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        $seconds = $this->started_at->diffInSeconds($this->completed_at);

        return gmdate('H:i:s', $seconds);
    }
}
