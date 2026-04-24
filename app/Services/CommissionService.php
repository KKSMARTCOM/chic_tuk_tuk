<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Commission;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    public function create(array $data)
    {
        $commission = Commission::create([
            'driver_id'       => $data['driver_id'],
            'booking_id'      => $data['booking_id'],
            'amount'          => $data['amount'],
            'date'            => $data['date'],
            'is_paid'         => $data['is_paid'] ?? false,
        ]);

        return $commission;
    }

    public function getAllCommissions($filters = [])
    {
        $query = Commission::query()
            ->with(['driver.user', 'booking'])
            ->latest();

        if (isset($filters['driver_id']) && !empty($filters['driver_id'])) {
            $query->where('driver_id', $filters['driver_id']);
        }

        if (isset($filters['is_paid'])) {
            $query->where('is_paid', $filters['is_paid']);
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('driver.user', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            })->orWhereHas('booking', function ($q) use ($search) {
                $q->where('booking_number', 'like', '%' . $search . '%');
            });
        }

        return $query->paginate(15);
    }

    public function getCommissionStats()
    {
        $totalRevenue = Commission::sum('amount');
        $paidCommissions = Commission::where('is_paid', true)->sum('amount');
        $unpaidCommissions = Commission::where('is_paid', false)->sum('amount');
        $totalCommissionsCount = Commission::count();

        return [
            'total_revenue' => $totalRevenue,
            'paid_commissions' => $paidCommissions,
            'unpaid_commissions' => $unpaidCommissions,
            'total_count' => $totalCommissionsCount,
            'paid_count' => Commission::where('is_paid', true)->count(),
            'unpaid_count' => Commission::where('is_paid', false)->count(),
        ];
    }

    public function getDriverCommissions($driverId)
    {
        $driver = Driver::with('user')->findOrFail($driverId);

        $driverEarning = Booking::where('driver_id', $driverId)
            ->where('status', 'completed')
            ->sum('driver_earning');

        $totalRevenue = $driver->commissions()->sum('amount');
        $paidRevenue = $driver->commissions()->where('is_paid', true)->sum('amount');
        $unpaidRevenue = $driver->commissions()->where('is_paid', false)->sum('amount');

        return [
            'driver' => $driver,
            'total_revenue' => $totalRevenue,
            'paid_revenue' => $paidRevenue,
            'unpaid_revenue' => $unpaidRevenue,
            'driver_earning' => $driverEarning,
            'commissions_count' => $driver->commissions()->count(),
        ];
    }

    public function markAsPaid($commissionId)
    {
        $commission = Commission::findOrFail($commissionId);
        $commission->update(['is_paid' => true]);
        return $commission;
    }

    public function markAsUnpaid($commissionId)
    {
        $commission = Commission::findOrFail($commissionId);
        $commission->update(['is_paid' => false]);
        return $commission;
    }
}
