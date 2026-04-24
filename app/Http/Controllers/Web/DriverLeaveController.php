<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DriverLeaveController extends Controller
{
    /**
     * Show the form to request leave
     */
    public function create()
    {
        $driver = Auth::user()->driver;

        if (!$driver) {
            return redirect()->route('driver.dashboard')->with('error', 'Profil conducteur non trouvé.');
        }

        $leaveInfo = [
            'leave_days_per_month' => $driver->getLeaveDaysPerMonth(),
            'total_leave_days' => $driver->getTotalLeaveDays(),
            'leave_days_used' => $driver->leave_days_used,
            'remaining_leave_days' => $driver->getRemainingLeaveDays(),
        ];

        // Get pending requests for current month
        $pendingRequests = $driver->getPendingLeaveRequestsForCurrentMonth();
        $approvedRequests = $driver->getApprovedLeaveRequestsForCurrentMonth();
        $rejectedRequests = $driver->leaveRequests()
            ->where('status', 'rejected')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return view('pages.driver.leaves.create', compact('driver', 'leaveInfo', 'pendingRequests', 'approvedRequests', 'rejectedRequests'));
    }

    /**
     * Store a leave request
     */
    public function store(Request $request)
    {
        $driver = Auth::user()->driver;

        if (!$driver) {
            return redirect()->route('driver.dashboard')->with('error', 'Profil conducteur non trouvé.');
        }

        $request->validate([
            'dates' => 'required|array|min:1',
            'dates.*' => 'required|date|after_or_equal:today',
        ], [
            'dates.required' => 'Veuillez sélectionner au moins une date de congé.',
            'dates.array' => 'Les dates de congé doivent être un tableau.',
            'dates.min' => 'Veuillez sélectionner au moins une date de congé.',
            'dates.*.date' => 'Les dates de congé doivent être des dates valides.',
            'dates.*.after_or_equal' => 'Les dates de congé doivent être des dates futures ou aujourd\'hui.',

        ]);

        $dates = $request->dates;
        $days = count($dates);

        // Check if all dates are in current month
        $currentMonth = now()->month;
        $currentYear = now()->year;
        foreach ($dates as $date) {
            $dateObj = Carbon::parse($date);
            if ($dateObj->month != $currentMonth || $dateObj->year != $currentYear) {
                return redirect()->back()->with('error', 'Les dates de congé doivent être dans le mois courant.');
            }
        }

        // Check if has already requested these dates
        $pendingRequests = $driver->getPendingLeaveRequestsForCurrentMonth();
        $requestedDates = [];
        foreach ($pendingRequests as $req) {
            $requestedDates = array_merge($requestedDates, $req->dates);
        }

        foreach ($dates as $date) {
            if (in_array($date, $requestedDates)) {
                return redirect()->back()->with('error', 'Vous avez déjà demandé un congé pour cette date.');
            }
        }

        // Check for existing approved leaves
        foreach ($dates as $date) {
            if ($driver->hasLeaveOnDate($date)) {
                return redirect()->back()->with('error', 'Vous avez déjà un congé approuvé pour cette date.');
            }
        }

        // Create leave request
        $leaveRequest = LeaveRequest::create([
            'driver_id' => $driver->id,
            'dates' => $dates,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Demande de congé soumise avec succès. L\'administrateur l\'examinera bientôt.');
    }

    /**
     * Show leave history and status
     */
    public function index()
    {
        $driver = Auth::user()->driver;

        if (!$driver) {
            return redirect()->route('driver.dashboard')->with('error', 'Profil conducteur non trouvé.');
        }

        $pendingRequests = $driver->getPendingLeaveRequestsForCurrentMonth();
        $approvedRequests = $driver->getApprovedLeaveRequestsForCurrentMonth();
        $rejectedRequests = $driver->leaveRequests()
            ->where('status', 'rejected')
            ->orderBy('created_at', 'desc')
            ->get();

        $leaveInfo = [
            'leave_days_per_month' => $driver->getLeaveDaysPerMonth(),
            'total_leave_days' => $driver->getTotalLeaveDays(),
            'leave_days_used' => $driver->leave_days_used,
            'remaining_leave_days' => $driver->getRemainingLeaveDays(),
        ];

        return view('pages.driver.leaves.index', compact('driver', 'leaveInfo', 'pendingRequests', 'approvedRequests', 'rejectedRequests'));
    }
}
