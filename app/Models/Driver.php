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

    /**
     * L'agent a-t-il des courses antérieures non soldées ?
     *
     * ⚠️ Corrigé le 2026-09-18. La version antérieure comparait deux chaînes qui
     * n'étaient pas construites de la même façon :
     *
     *   - à gauche, PostgreSQL rendait CONCAT(pickup_date, ' ', pickup_time), soit
     *     « 2026-09-19 07:00:00 » ;
     *   - à droite, PHP concaténait $booking->pickup_date . ' ' . $booking->pickup_time.
     *     Or `pickup_date` est casté en `date`, donc en Carbon, et sa conversion en
     *     chaîne rend « 2026-09-19 00:00:00 ». Le repère valait donc
     *     « 2026-09-19 00:00:00 10:00 » — la date, MINUIT, puis l'heure collée derrière.
     *
     * La comparaison lexicographique butait au douzième caractère ('7' > '0') et aucune
     * course du même jour n'était jamais vue comme antérieure : un agent pouvait démarrer
     * sa course de 10:00 en laissant celle de 07:00 en plan, ce que ce refus existe
     * précisément pour empêcher.
     *
     * La comparaison porte désormais sur des TIMESTAMPS et non sur des chaînes. C'est
     * l'idiome déjà utilisé par ListAvailableBookings pour trier, et il supprime la
     * classe entière de ces bugs plutôt que cette seule instance : le résultat ne dépend
     * plus ni du réglage DateStyle de PostgreSQL, ni de la façon dont PHP rend un Carbon.
     *
     * La comparaison reste STRICTE : la course visée figure dans $this->bookings() et
     * porte le même repère qu'elle-même, donc un `<=` ferait qu'aucune course ne pourrait
     * plus jamais démarrer.
     */
    public function hasBlockingPreviousBookings(Booking $currentBooking): bool
    {
        $repere = Carbon::parse($currentBooking->pickup_date)->format('Y-m-d')
            . ' ' . Carbon::parse($currentBooking->pickup_time)->format('H:i:s');

        return $this->bookings()
            ->whereRaw('(pickup_date::date + pickup_time::time) < ?::timestamp', [$repere])
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
