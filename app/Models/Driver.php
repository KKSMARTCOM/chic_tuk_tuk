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
        //'vehicle_number',
        //'vehicle_type',
        'is_available',
        'rating',
        'total_trips',
        'agent_code',
        'agent_id',
        //'contract_type',
        //'start_date',
        //'tricycle_owner',
        //'owner_phone',
        'leave_days_used',
        'leave_dates'
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'rating' => 'decimal:2',
        'leave_dates' => 'array',
    ];

    protected $appends = [
        'available_leave_days',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'driver_id', 'id');
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class, 'driver_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'driver_id', 'id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'driver_id', 'id');
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
    public function ongoingLeaveRequest()
    {
        return $this->hasOne(LeaveRequest::class, 'driver_id', 'id')->where('status', 'ongoing');
    }

    public function isOnLeaveToday(): bool
    {
        $ongoing = $this->ongoingLeaveRequest()->first();
        return $ongoing && $ongoing->start_date->lte(now()->startOfDay());
    }

    public function hasOngoingLeave(): bool
    {
        return $this->leaveRequests()->where('status', 'ongoing')->exists();
    }

    public function getLeaveDaysPerMonth(): int
    {
        return 2; // 2 days per month
    }

    public function getContractMonths(): int
    {
        return (int) ($this->activeDriverContract->contract_months ?? 24); // default 24 months
    }

    public function getLeaveRequestsByStatus(string $status): int
    {
        return $this->leaveRequests()
            ->where('status', $status)
            ->get()
            ->sum(fn($leave) => $leave->effective_days ?? $leave->requested_days ?? 0);
    }

    public function getTotalLeaveDays(): int
    {
        return $this->getLeaveDaysPerMonth() * $this->getContractMonths();
    }

    public function getRemainingLeaveDays(): int
    {
        return $this->getTotalLeaveDays() - ($this->leave_days_used ?? 0);
    }

    public function getContractMonthsElapsed(): int
    {
        if (!$this->activeDriverContract?->start_date) {
            return 0;
        }

        $start = Carbon::parse($this->activeDriverContract->start_date)->startOfDay();

        $now = now()->startOfDay();

        if ($now->lt($start)) {
            return 0;
        }

        return min($start->diffInMonths($now) + 1, $this->getContractMonths());
    }

    public function getAccruedLeaveDays(): int
    {
        return $this->getLeaveDaysPerMonth() * $this->getContractMonthsElapsed();
    }

    public function getAvailableLeaveDaysAttribute(): int
    {
        return $this->getAccruedLeaveDays()
            - $this->getLeaveRequestsByStatus('ongoing')
            - $this->getLeaveRequestsByStatus('pending')
            - $this->getLeaveRequestsByStatus('completed');
    }

    // ── Mise à jour du compteur indicatif (appelée à la clôture d'une pause) ──
    public function markLeaveDaysUsed(int $days): void
    {
        $this->leave_days_used = max(0, ($this->leave_days_used ?? 0) + $days);
        $this->save();
    }

    // Nouvelles relations
    public function driverContracts()
    {
        return $this->hasMany(DriverContract::class);
    }

    public function activeDriverContract()
    {
        return $this->hasOne(DriverContract::class)->where('status', 'active');
    }

    // currentVehicle via le contrat actif
    public function currentVehicle()
    {
        return $this->hasOneThrough(
            Vehicle::class,
            DriverContract::class,
            'driver_id',
            'id',
            'id',
            'vehicle_id'
        )->where('driver_contracts.status', 'active');
    }
}
