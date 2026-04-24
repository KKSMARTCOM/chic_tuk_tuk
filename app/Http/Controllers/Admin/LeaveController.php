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
    public function index()
    {
        $drivers = User::where('role', 'driver')
            ->with('driver')
            ->paginate(10)
            ->through(function ($user) {
                $driver = $user->driver;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'contract_type' => $driver->contract_type,
                    'leave_days_per_month' => $driver->getLeaveDaysPerMonth(),
                    'total_leave_days' => $driver->getTotalLeaveDays(),
                    'leave_days_used' => $driver->leave_days_used,
                    'remaining_leave_days' => $driver->getRemainingLeaveDays(),
                    'leave_dates' => $driver->leave_dates ?? [],
                    'pending_requests' => $driver->getPendingLeaveRequestsForCurrentMonth()->count(),
                ];
            });

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
            'leave_days_used' => $driverModel->leave_days_used,
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
                return redirect()->back()->with('error', 'Les congés doivent être dans le mois courant.');
            }
        }

        // Check if driver has enough days
        if (!$driver->canRequestLeave($days)) {
            return redirect()->back()->with('error', 'Le conducteur n\'a pas assez de jours de congé disponibles.');
        }

        // Check for conflicts
        foreach ($dates as $date) {
            if ($driver->hasLeaveOnDate($date)) {
                return redirect()->back()->with('error', 'Le conducteur a déjà un congé pour cette date.');
            }
        }

        // Approve the request
        $leaveRequest->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        // Add leave dates to driver
        $driver->addLeaveDates($dates);

        return redirect()->back()->with('success', 'Demande de congé approuvée avec succès.');
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

        return redirect()->back()->with('success', 'Demande de congé rejetée avec succès.');
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
            return redirect()->back()->with('error', 'Aucun congé trouvé pour cette date.');
        }

        // Remove the date
        $driverModel->removeLeaveDates([$date]);

        return redirect()->back()->with('success', 'Congé révoqué avec succès.');
    }
}
