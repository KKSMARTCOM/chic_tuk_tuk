<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function index()
    {
        $drivers = User::where('role', 'driver')
            ->with('driver')
            ->get()
            ->map(function ($user) {
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
                    'can_request_leave' => $driver->canRequestLeave(),
                ];
            });

        return view('admin.leaves.index', compact('drivers'));
    }

    public function show(User $driver)
    {
        $driver->load('driver');
        $leaveInfo = [
            'leave_days_per_month' => $driver->driver->getLeaveDaysPerMonth(),
            'total_leave_days' => $driver->driver->getTotalLeaveDays(),
            'leave_days_used' => $driver->driver->leave_days_used,
            'remaining_leave_days' => $driver->driver->getRemainingLeaveDays(),
            'leave_dates' => $driver->driver->leave_dates ?? [],
            'can_request_leave' => $driver->driver->canRequestLeave(),
        ];

        return view('admin.leaves.show', compact('driver', 'leaveInfo'));
    }

    public function approveLeave(Request $request, User $driver)
    {
        $request->validate([
            'leave_dates' => 'required|array',
            'leave_dates.*' => 'date|after:today',
        ]);

        $dates = $request->leave_dates;
        $days = count($dates);

        if (!$driver->driver->canRequestLeave($days)) {
            return redirect()->back()->with('error', 'Le conducteur n\'a pas assez de jours de congé disponibles.');
        }

        // Vérifier que les dates sont dans le mois courant
        $currentMonth = now()->month;
        $currentYear = now()->year;
        foreach ($dates as $date) {
            $dateObj = Carbon::parse($date);
            if ($dateObj->month != $currentMonth || $dateObj->year != $currentYear) {
                return redirect()->back()->with('error', 'Les congés ne peuvent être demandés que pour le mois en cours.');
            }
            if ($driver->driver->hasLeaveOnDate($date)) {
                return redirect()->back()->with('error', 'Le conducteur a déjà un congé prévu pour cette date.');
            }
        }

        $driver->driver->addLeaveDates($dates);

        return redirect()->back()->with('success', 'Demande de congé approuvée avec succès.');
    }

    public function revokeLeave(Request $request, User $driver)
    {
        $request->validate([
            'leave_date' => 'required|date',
        ]);

        $date = $request->leave_date;
        $leaveDates = $driver->driver->leave_dates ?? [];

        if (!in_array($date, $leaveDates)) {
            return redirect()->back()->with('error', 'Aucun congé trouvé pour cette date.');
        }

        // Retirer la date
        $updatedDates = array_diff($leaveDates, [$date]);
        $driver->driver->update([
            'leave_dates' => array_values($updatedDates),
            'leave_days_used' => $driver->driver->leave_days_used - 1,
        ]);

        return redirect()->back()->with('success', 'Congé révoqué avec succès.');
    }
}
