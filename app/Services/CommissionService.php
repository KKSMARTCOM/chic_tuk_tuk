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

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('driver.user', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            })->orWhereHas('booking', function ($q) use ($search) {
                $q->where('booking_number', 'like', '%' . $search . '%');
            });
        }

        return $query->latest()->get();
    }

    public function getCommissionStats()
    {
        $totalRevenue = Commission::sum('amount');
        $totalCommissionsCount = Commission::count();

        return [
            'total_revenue' => $totalRevenue,
            'total_count' => $totalCommissionsCount,
        ];
    }

    public function getDriverCommissions($driverId)
    {
        $driver = Driver::with('user')->findOrFail($driverId);

        $driverEarning = Booking::where('driver_id', $driverId)
            ->where('status', 'completed')
            ->sum('driver_earning');

        return [
            'driver' => $driver,
            'driver_earning' => $driverEarning,
            'commissions_count' => $driver->commissions()->count(),
            'paid_revenue' => $driver->payments()->sum('amount'),
            'unpaid_revenue' => $driver->commissions()->sum('amount') - $driver->payments()->sum('amount'),
        ];
    }
}
