<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DriverService
{
    public function getAllDrivers($filters = [])
    {
        $query = User::query()
            ->where('role', 'driver')
            ->with('driver');

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhereHas('driver', function ($driverQuery) use ($search) {
                        $driverQuery->where('license_number', 'like', '%' . $search . '%')
                            ->orWhere('vehicle_number', 'like', '%' . $search . '%');
                    });
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['is_available'])) {
            $query->whereHas('driver', function ($q) use ($filters) {
                $q->where('is_available', $filters['is_available']);
            });
        }

        return $query->latest()->paginate(10);
    }

    public function getDriverStats()
    {
        $totalDrivers = User::where('role', 'driver')->count();
        $activeDrivers = User::where('role', 'driver')->where('is_active', true)->count();
        $inactiveDrivers = $totalDrivers - $activeDrivers;
        $availableDrivers = Driver::where('is_available', true)->count();

        return [
            'total' => $totalDrivers,
            'active' => $activeDrivers,
            'inactive' => $inactiveDrivers,
            'available' => $availableDrivers,
        ];
    }

    public function getDriverById($driverId)
    {
        return User::where('role', 'driver')
            ->with(['driver', 'driver.bookings' => function ($query) {
                $query->with(['fromZone', 'toZone'])
                    ->orderByRaw("CONCAT(pickup_date, ' ', pickup_time) DESC");
            }])
            ->findOrFail($driverId);
    }

    public function getDriverBookingStats($driverId)
    {
        $driver = Driver::findOrFail($driverId);

        $bookings = $driver->bookings();

        return [
            'total' => (clone $bookings)->count(),
            'completed' => (clone $bookings)->where('status', 'completed')->count(),
            'cancelled' => (clone $bookings)->where('status', 'cancelled')->count(),
            'confirmed' => (clone $bookings)->where('status', 'confirmed')->count(),
            'in_progress' => (clone $bookings)->where('status', 'in_progress')->count(),
            'total_minutes' => $this->calculateTotalDrivingMinutes($driver),
            'average_rating' => $driver->rating ?? 0,
        ];
    }

    private function calculateTotalDrivingMinutes(Driver $driver)
    {
        $completedBookings = $driver->bookings()
            ->where('status', 'completed')
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->get();

        $totalMinutes = 0;

        foreach ($completedBookings as $booking) {
            $totalMinutes += $booking->started_at->diffInMinutes($booking->completed_at);
        }

        return $totalMinutes;
    }

    public function createDriver(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => 'driver',
            'is_active' => $data['is_active'] ?? true,
            'adresse' => $data['adresse'] ?? null,
        ]);

        $driver = Driver::create([
            'user_id' => $user->id,
            'license_number' => $data['license_number'],
            'vehicle_number' => $data['vehicle_number'],
            'vehicle_type' => $data['vehicle_type'],
            'is_available' => $data['is_available'] ?? true,
            'agent_code' => $data['agent_code'] ?? null,
            'agent_id' => $data['agent_id'] ?? null,
            'contract_type' => $data['contract_type'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'tricycle_owner' => $data['tricycle_owner'] ?? null,
            'owner_phone' => $data['owner_phone'] ?? null,
        ]);

        return $user->load('driver');
    }

    public function updateDriver($driverId, array $data)
    {
        $user = User::findOrFail($driverId);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'is_active' => $data['is_active'] ?? $user->is_active,
            'adresse' => $data['adresse'] ?? $user->adresse,
        ]);

        if ($user->driver) {
            $user->driver->update([
                'license_number' => $data['license_number'],
                'vehicle_number' => $data['vehicle_number'],
                'vehicle_type' => $data['vehicle_type'],
                'is_available' => $data['is_available'] ?? $user->driver->is_available,
                'agent_code' => $data['agent_code'] ?? $user->driver->agent_code,
                'agent_id' => $data['agent_id'] ?? $user->driver->agent_id,
                'contract_type' => $data['contract_type'] ?? $user->driver->contract_type,
                'start_date' => $data['start_date'] ?? $user->driver->start_date,
                'tricycle_owner' => $data['tricycle_owner'] ?? $user->driver->tricycle_owner,
                'owner_phone' => $data['owner_phone'] ?? $user->driver->owner_phone,
            ]);
        }

        return $user->load('driver');
    }

    public function deleteDriver($driverId)
    {
        $user = User::findOrFail($driverId);

        // Vérifier s'il a des courses en cours
        if ($user->driver && $user->driver->bookings()->whereIn('status', ['confirmed', 'in_progress'])->exists()) {
            throw new \Exception('Impossible de supprimer un conducteur avec des courses en cours.');
        }

        $user->delete();
    }

    public function getDriverDashboardStats(Driver $driver)
    {
        // Calcul du temps total de courses en minutes
        $total_duration_seconds = Booking::where('driver_id', $driver->id)
            ->where('status', 'completed')
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->get()
            ->sum(function ($booking) {
                return $booking->started_at->diffInSeconds($booking->completed_at);
            });

        $recentBookings = Booking::where('status', 'pending')
            ->with(['fromZone', 'toZone'])
            ->orderByRaw("CONCAT(pickup_date, ' ', pickup_time) DESC")
            ->latest()
            ->take(5)
            ->get();

        $recentBookingsAccepting = Booking::where('driver_id', $driver->id)
            ->where('status', 'confirmed')
            ->with(['fromZone', 'toZone'])
            ->orderByRaw("CONCAT(pickup_date, ' ', pickup_time) DESC")
            ->latest()
            ->take(5)
            ->get();

        $stats = [
            'total_trips' => $driver->total_trips,
            'rating' => $driver->rating,
            'confirmed_trips' => Booking::where('driver_id', $driver->id)
                ->where('status', 'confirmed')->count(),
            'completed_trips' => Booking::where('driver_id', $driver->id)
                ->where('status', 'completed')->count(),
            'cancelled_trips' => Booking::where('driver_id', $driver->id)
                ->where('status', 'cancelled')->count(),
            'earnings_today' => Booking::where('driver_id', $driver->id)
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->sum('driver_earning'),
            'total_earnings' => Booking::where('driver_id', $driver->id)
                ->where('status', 'completed')
                ->sum('driver_earning'),
            'commission_today' => Booking::where('driver_id', $driver->id)
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->sum('commission'),
            'total_commission' => Booking::where('driver_id', $driver->id)
                ->where('status', 'completed')
                ->sum('commission'),
            'total_duration_minutes' => round($total_duration_seconds / 60),
            'recent_bookings' => $recentBookings,
            'recent_bookings_accepting' => $recentBookingsAccepting
        ];

        return $stats;
    }

    public function getAllDriversForExport($filters = [])
    {
        $query = User::where('role', 'driver')->with('driver');

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhereHas('driver', function ($driverQuery) use ($search) {
                        $driverQuery->where('license_number', 'like', '%' . $search . '%')
                            ->orWhere('vehicle_number', 'like', '%' . $search . '%');
                    });
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['is_available'])) {
            $query->whereHas('driver', function ($q) use ($filters) {
                $q->where('is_available', $filters['is_available']);
            });
        }

        return $query->latest()->get();
    }
}
