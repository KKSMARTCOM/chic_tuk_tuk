<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaveController extends Controller
{
    /**
     * Display all drivers with their leave information
     */
    public function index(Request $request)
    {
        $query = User::where('profil', 'driver')->with('driver');

        // Filter by search (name)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by contract duration
        if ($request->filled('contract')) {
            $contract = $request->input('contract');
            $query->whereHas('driver', function ($q) use ($contract) {
                $q->where('contract_type', (int) $contract);
            });
        }

        // Get all users and build driver data
        $allUsers = $query->get();

        $drivers = $allUsers->map(function ($user) {
            $driver = $user->driver;
            return [
                'id' => $user->id,
                'name' => $user->name,
                'contract_type' => $driver->contract_type,
                'leave_days_per_month' => $driver->getLeaveDaysPerMonth(),
                'total_leave_days' => $driver->getTotalLeaveDays(),
                'leave_days_used' => $driver->leave_days_used ?? 0,
                'available_leave_days' => $driver->getAvailableLeaveDays(),
                'remaining_leave_days' => $driver->getRemainingLeaveDays(),
                'leave_dates' => $driver->leave_dates ?? [],
                'pending_requests' => $driver->getPendingLeaveRequestsForCurrentMonth()->count(),
            ];
        });

        // Filter by available days (PHP filtering after calculation)
        if ($request->filled('available')) {
            $available = $request->input('available');
            $drivers = $drivers->filter(function ($driver) use ($available) {
                if ($available === 'yes') {
                    return $driver['available_leave_days'] > 0;
                } elseif ($available === 'no') {
                    return $driver['available_leave_days'] <= 0;
                }
                return true;
            });
        }

        // Filter by pending requests (PHP filtering)
        if ($request->filled('pending')) {
            $pending = $request->input('pending');
            $drivers = $drivers->filter(function ($driver) use ($pending) {
                if ($pending === 'yes') {
                    return $driver['pending_requests'] > 0;
                } elseif ($pending === 'no') {
                    return $driver['pending_requests'] === 0;
                }
                return true;
            });
        }

        // Return all results for DataTable (no pagination)
        $drivers = $drivers->values();

        return view('pages.admin.leaves.index', compact('drivers'));
    }

    /**
     * Show leave details and requests for a specific driver
     */
    public function show(User $driver)
    {
        $driver->load('driver');
        $driverModel = $driver->driver;

        $leaveInfo = [
            'leave_days_per_month' => $driverModel->getLeaveDaysPerMonth(),
            'total_leave_days' => $driverModel->getTotalLeaveDays(),
            'leave_days_used' => $driverModel->leave_days_used ?? 0,
            'available_leave_days' => $driverModel->getAvailableLeaveDays(),
            'remaining_leave_days' => $driverModel->getRemainingLeaveDays(),
            'leave_dates' => $driverModel->leave_dates ?? [],
            'contract_start' => $driverModel->start_date,
            'contract_months' => $driverModel->contract_type,
        ];

        // Get pending and approved requests for this month
        $pendingRequests = $driverModel->getPendingLeaveRequestsForCurrentMonth();
        $approvedRequests = $driverModel->getApprovedLeaveRequestsForCurrentMonth();

        return view('pages.admin.leaves.show', compact('driver', 'leaveInfo', 'pendingRequests', 'approvedRequests'));
    }

    /**
     * Display pending leave requests for all drivers
     */
    public function requests()
    {
        $requests = LeaveRequest::with('driver.user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.admin.leaves.requests', compact('requests'));
    }

    /**
     * Approve a leave request
     */
    public function approveRequest(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'rejection_reason' => 'nullable|string',
        ]);

        $driver = $leaveRequest->driver;
        $dates = $leaveRequest->dates;
        $days = count($dates);

        // Validate dates are in current month
        $currentMonth = now()->month;
        $currentYear = now()->year;
        foreach ($dates as $date) {
            $dateObj = Carbon::parse($date);
            if ($dateObj->month != $currentMonth || $dateObj->year != $currentYear) {
                return redirect()->back()->with('error', 'Les Pauses doivent être dans le mois courant.');
            }
        }

        // Check if driver has enough days
        if (!$driver->canRequestLeave($days)) {
            return redirect()->back()->with('error', 'Le Agent n\'a pas assez de jours de Pause disponibles.');
        }

        // Check for conflicts
        foreach ($dates as $date) {
            if ($driver->hasLeaveOnDate($date)) {
                return redirect()->back()->with('error', 'Le Agent a déjà un Pause pour cette date.');
            }
        }

        // Approve the request
        $leaveRequest->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        // Add leave dates to driver
        $driver->addLeaveDates($dates);

        return redirect()->back()->with('success', 'Demande de Pause approuvée avec succès.');
    }

    /**
     * Reject a leave request
     */
    public function rejectRequest(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:5',
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->back()->with('success', 'Demande de Pause rejetée avec succès.');
    }

    /**
     * Add an instant leave directly for a driver
     */
    public function addInstantLeave(Request $request, string $id)
    {
        $driver = Driver::find($id);

        $request->validate([
            'dates' => 'required|array|min:1',
            'dates.*' => 'required|date|after_or_equal:today',
        ], [
            'dates.required' => 'Veuillez sélectionner au moins une date de Pause.',
            'dates.array' => 'Les dates de Pause doivent être un tableau.',
            'dates.min' => 'Veuillez sélectionner au moins une date de Pause.',
            'dates.*.date' => 'Les dates de Pause doivent être des dates valides.',
            'dates.*.after_or_equal' => 'Les dates de Pause ne peuvent pas être dans le passé.',
        ]);

        $dates = collect($request->input('dates', []))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        if (count($dates) !== count($request->input('dates', []))) {
            return redirect()->back()->with('error', 'Les dates de Pause doivent être uniques.');
        }

        $days = count($dates);

        // Check if all dates are in current month
        $currentMonth = now()->month;
        $currentYear = now()->year;
        foreach ($dates as $date) {
            $dateObj = Carbon::parse($date);
            if ($dateObj->month != $currentMonth || $dateObj->year != $currentYear) {
                return redirect()->back()->with('error', 'Les dates de Pause doivent être dans le mois courant.');
            }
        }

        sort($dates);

        // Check if the selected dates are sequential
        for ($i = 1; $i < count($dates); $i++) {
            $previous = Carbon::parse($dates[$i - 1])->startOfDay();
            $current = Carbon::parse($dates[$i])->startOfDay();
            if (!$previous->copy()->addDay()->equalTo($current)) {
                return redirect()->back()->with('error', 'Les jours doivent être consécutifs.');
            }
        }

        if (!$driver->canRequestLeaveNow($days)) {
            return redirect()->back()->with('error', 'Ce conducteur n\'a pas assez de jours de pause disponibles à date.');
        }

        // Check for existing approved or pending leaves
        $existingDates = [];
        $existingDates = array_merge($existingDates, $driver->leave_dates ?? []);
        $pendingRequests = $driver->getPendingLeaveRequestsForCurrentMonth();
        foreach ($pendingRequests as $req) {
            $existingDates = array_merge($existingDates, $req->dates);
        }

        foreach ($dates as $date) {
            if (in_array($date, $existingDates)) {
                return redirect()->back()->with('error', 'Cette date est déjà prise ou en attente.');
            }
        }

        // Add the leave immediately and create an approved record
        $driver->addLeaveDates($dates);
        LeaveRequest::create([
            'driver_id' => $driver->id,
            'dates' => $dates,
            'status' => 'approved',
        ]);

        return redirect()->back()->with('success', 'Pause instantanée ajoutée avec succès.');
    }

    /**
     * Revoke an approved leave date (for a driver)
     */
    public function revokeLeave(Request $request, User $driver)
    {
        $request->validate([
            'leave_date' => 'required|date',
        ]);

        $date = $request->leave_date;
        $driverModel = $driver->driver;
        $leaveDates = $driverModel->leave_dates ?? [];

        if (!in_array($date, $leaveDates)) {
            return redirect()->back()->with('error', 'Aucun Pause trouvé pour cette date.');
        }

        // Remove the date
        $driverModel->removeLeaveDates([$date]);

        return redirect()->back()->with('success', 'Pause révoqué avec succès.');
    }
}
