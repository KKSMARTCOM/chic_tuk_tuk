<?php

namespace App\Models;

use App\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Driver extends Model
{
    use HasUuid, Notifiable, HasFactory;

    protected $fillable = [
        'user_id',
        'license_number',
        'vehicle_number',
        'vehicle_type',
        'is_available',
        'rating',
        'total_trips',
        'agent_code',
        'agent_id',
        'contract_type',
        'start_date',
        'tricycle_owner',
        'owner_phone',
        'leave_days_used',
        'leave_dates'
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'rating' => 'decimal:2',
        'leave_dates' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'driver_id', 'id');
    }

    public function hasConflictWithinTwoHours(Carbon $pickupDatetime): bool
    {
        $windowStart = $pickupDatetime->copy()->subHours(2);
        $windowEnd   = $pickupDatetime->copy()->addHours(2);

        return $this->bookings()
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->whereRaw("CONCAT(pickup_date, ' ', pickup_time) BETWEEN ? AND ?", [$windowStart->format('Y-m-d H:i:s'), $windowEnd->format('Y-m-d H:i:s')])
            ->exists();
    }

    public function hasOngoingTrip(): bool
    {
        return $this->bookings()
            ->where('status', 'in_progress')
            ->exists();
    }

    public function hasBlockingPreviousBookings(Booking $currentBooking): bool
    {
        return $this->bookings()
            ->whereRaw("CONCAT(pickup_date, ' ', pickup_time) < ?", [$currentBooking->pickup_date . ' ' . $currentBooking->pickup_time])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();
    }

    // Leave management methods
    public function getLeaveDaysPerMonth(): int
    {
        return 2; // 2 days per month
    }

    public function getContractMonths(): int
    {
        return (int) $this->contract_type ?? 24; // default 24 months
    }

    public function getTotalLeaveDays(): int
    {
        return $this->getLeaveDaysPerMonth() * $this->getContractMonths();
    }

    public function getRemainingLeaveDays(): int
    {
        return $this->getTotalLeaveDays() - $this->leave_days_used;
    }

    public function canRequestLeave(int $days = 1): bool
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Check if requesting in current month
        if (now()->month != $currentMonth || now()->year != $currentYear) {
            return false;
        }

        // Check remaining days
        return $this->getRemainingLeaveDays() >= $days;
    }

    public function addLeaveDates(array $dates): void
    {
        $existing = $this->leave_dates ?? [];
        $this->leave_dates = array_unique(array_merge($existing, $dates));
        $this->leave_days_used += count($dates);
        $this->save();
    }

    public function hasLeaveOnDate(string $date): bool
    {
        return in_array($date, $this->leave_dates ?? []);
    }
}
