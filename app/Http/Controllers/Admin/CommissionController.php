<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Services\CommissionService;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['driver_id', 'is_paid', 'search']);
            $commissions = $this->commissionService->getAllCommissions($filters);
            $stats = $this->commissionService->getCommissionStats();

            return view('pages.admin.commissions.index', compact('commissions', 'stats'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function show(Commission $commission)
    {
        try {
            $commission->load(['driver.user', 'booking']);
            return view('pages.admin.commissions.show', compact('commission'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function markAsPaid(Request $request, Commission $commission)
    {
        try {
            $this->commissionService->markAsPaid($commission->id);

            return response()->json([
                'success' => true,
                'message' => 'Commission marquée comme payée'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function markAsUnpaid(Request $request, Commission $commission)
    {
        try {
            $this->commissionService->markAsUnpaid($commission->id);

            return response()->json([
                'success' => true,
                'message' => 'Commission marquée comme non payée'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
