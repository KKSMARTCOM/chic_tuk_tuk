<?php

namespace App\Models;

use App\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasUuid, HasFactory;

    protected $fillable = [
        'driver_id',
        'driver_contract_id', // nouveau
        'start_date',
        'requested_days',
        'end_date',
        'effective_days',
        'status',
        'source',
        'created_by',
        'rejection_reason',
        'vehicle_pause_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function vehiclePause()
    {
        return $this->belongsTo(VehiclePause::class);
    }

    public function scopePending($q)
    {
        return $q->where('status', 'pending');
    }

    public function scopeOngoing($q)
    {
        return $q->where('status', 'ongoing');
    }

    public function scopeCompleted($q)
    {
        return $q->where('status', 'completed');
    }

    public function getExpectedEndDateAttribute(): ?Carbon
    {
        if (!$this->start_date || !$this->requested_days) {
            return null;
        }

        return self::addBusinessDays($this->start_date, $this->requested_days);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'ongoing'
            && $this->expected_end_date
            && now()->startOfDay()->gt($this->expected_end_date);
    }

    public function getIsHistoricalAttribute(): bool
    {
        return in_array($this->source, ['admin_historical', 'legacy']) && $this->status === 'completed';
    }

    /**
     * Compte le nombre de jours ouvrés (hors samedi/dimanche) entre deux dates, inclus.
     */
    public static function countBusinessDays(Carbon $start, Carbon $end): int
    {
        $start = $start->copy()->startOfDay();
        $end   = $end->copy()->startOfDay();

        if ($end->lt($start)) {
            return 0;
        }

        $count = 0;
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            if (!$cursor->isWeekend()) {
                $count++;
            }
            $cursor->addDay();
        }

        return $count;
    }

    /**
     * Retourne la date du Nième jour ouvré à partir de start_date (start_date inclus s'il est ouvré).
     */
    public static function addBusinessDays(Carbon $start, int $days): Carbon
    {
        $cursor = $start->copy()->startOfDay();
        $remaining = $days;

        // Si le jour de départ n'est pas ouvré, on avance jusqu'au premier jour ouvré
        while ($cursor->isWeekend()) {
            $cursor->addDay();
        }

        while ($remaining > 1) {
            $cursor->addDay();
            if (!$cursor->isWeekend()) {
                $remaining--;
            }
        }

        return $cursor;
    }
}
