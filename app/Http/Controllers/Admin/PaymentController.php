<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Afficher la liste des paiements
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['driver_id', 'payment_method', 'search', 'date_from', 'date_to']);
            $payments = $this->paymentService->getAllPayments($filters);
            $stats = $this->paymentService->getPaymentStats();
            $drivers = Driver::with('user')->orderBy('id')->get();

            return view('pages.admin.payments.index', compact('payments', 'stats', 'drivers'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l’affichage des paiements : ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire de création de paiement
     */
    public function create()
    {
        try {
            $drivers = Driver::with('user')->orderBy('id')->get();

            return view('pages.admin.payments.create', compact('drivers'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l’affichage du formulaire de paiement : ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Créer un nouveau paiement
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'driver_id' => 'required|exists:drivers,id',
                'amount'           => [
                    'required',
                    'numeric',
                    'min:0.01',
                    function ($attribute, $value, $fail) use ($request) {
                        $driverId  = $request->driver_id;
                        $totalDue  = \App\Models\Commission::where('driver_id', $driverId)->sum('amount');
                        $totalPaid = \App\Models\Payment::where('driver_id', $driverId)->sum('amount');
                        $remaining = $totalDue - $totalPaid;

                        if ($value > $remaining) {
                            $fail("Le montant ne peut pas dépasser la commission restante due ({$remaining}).");
                        }
                    },
                ],
                'payment_method' => 'required|in:cash,bank_transfer,check,mobile_money,other',
                'payment_date' => 'required|date',
                'notes' => 'nullable|string|max:500',
                //'reference_number' => 'nullable|string|max:100|unique:payments',
            ]);

            $this->paymentService->create($validated);

            return redirect()->route('admin.payments.index')
                ->with('success', 'Paiement enregistré avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du paiement : ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'un paiement
     */
    public function show(Payment $payment)
    {
        try {
            $payment->load(['driver.user']);
            $driverStats = $this->paymentService->getDriverPayments($payment->driver_id);

            return view('pages.admin.payments.show', compact('payment', 'driverStats'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Payment $payment)
    {
        try {
            $drivers = Driver::with('user')->orderBy('id')->get();

            return view('pages.admin.payments.edit', compact('payment', 'drivers'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l’affichage du paiement : ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mettre à jour un paiement
     */
    public function update(Request $request, Payment $payment)
    {
        try {
            $validated = $request->validate([
                'driver_id' => 'required|exists:drivers,id',
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|in:cash,bank_transfer,check,mobile_money,other',
                'payment_date' => 'required|date',
                'notes' => 'nullable|string|max:500',
                'reference_number' => 'nullable|string|max:100|unique:payments,reference_number,' . $payment->id,
            ]);

            $this->paymentService->update($payment->id, $validated);

            return redirect()->route('admin.payments.show', $payment)
                ->with('success', 'Paiement mis à jour avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du paiement : ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Supprimer un paiement
     */
    public function destroy(Payment $payment)
    {
        try {
            $this->paymentService->delete($payment->id);

            return redirect()->route('admin.payments.index')
                ->with('success', 'Paiement supprimé avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du paiement : ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Afficher les détails de paiement d'un conducteur
     */
    public function driverPaymentDetails(string $driverId)
    {
        try {
            $driverStats = $this->paymentService->getDriverPayments($driverId);
            $payments = Payment::where('driver_id', $driverId)
                ->latest('payment_date')
                ->paginate(15);
            $commissions = $this->paymentService->getDriverDueCommissions($driverId);

            return view('pages.admin.payments.driver-details', compact('driverStats', 'payments', 'commissions'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l’affichage des détails de paiement : ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
