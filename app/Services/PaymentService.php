<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\Driver;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Créer un paiement
     */
    public function create(array $data)
    {
        $payment = Payment::create([
            'driver_id' => $data['driver_id'],
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'payment_date' => $data['payment_date'],
            'notes' => $data['notes'] ?? null,
            'reference_number' => $data['reference_number'] ?? null,
        ]);

        return $payment;
    }

    /**
     * Récupérer tous les paiements avec filtres
     */
    public function getAllPayments($filters = [])
    {
        $query = Payment::query()
            ->with(['driver.user'])
            ->latest('payment_date');

        if (isset($filters['driver_id']) && !empty($filters['driver_id'])) {
            $query->where('driver_id', $filters['driver_id']);
        }

        if (isset($filters['payment_method']) && !empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('driver.user', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            })->orWhere('reference_number', 'like', '%' . $search . '%');
        }

        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $query->whereDate('payment_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $query->whereDate('payment_date', '<=', $filters['date_to']);
        }

        return $query->latest()->get();
    }

    /**
     * Obtenir les statistiques des paiements
     */
    public function getPaymentStats()
    {
        $totalPaid = Payment::sum('amount');
        $totalDue = Commission::sum('amount');
        $totalPaidThisMonth = Payment::whereMonth('payment_date', Carbon::now()->month)
            ->whereYear('payment_date', Carbon::now()->year)
            ->sum('amount');
        $paymentsCount = Payment::count();

        return [
            'total_paid' => $totalPaid,
            'total_due' => $totalDue,
            'balance_due' => $totalDue - $totalPaid,
            'paid_this_month' => $totalPaidThisMonth,
            'payments_count' => $paymentsCount,
        ];
    }

    /**
     * Obtenir les paiements d'un conducteur
     */
    public function getDriverPayments($driverId)
    {
        $driver = Driver::with('user')->findOrFail($driverId);

        $totalDue = $driver->commissions()->sum('amount');
        $totalPaid = $driver->payments()->sum('amount');
        $balanceDue = $totalDue - $totalPaid;

        return [
            'driver' => $driver,
            'total_due' => $totalDue,
            'total_paid' => $totalPaid,
            'balance_due' => $balanceDue,
            'payments_count' => $driver->payments()->count(),
            'commissions_count' => $driver->commissions()->count(),
        ];
    }

    /**
     * Mettre à jour un paiement
     */
    public function update($paymentId, array $data)
    {
        $payment = Payment::findOrFail($paymentId);
        $payment->update($data);
        return $payment;
    }

    /**
     * Supprimer un paiement
     */
    public function delete($paymentId)
    {
        $payment = Payment::findOrFail($paymentId);
        $payment->delete();
        return true;
    }

    /**
     * Récupérer les commissions dues d'un conducteur (non payées)
     */
    public function getDriverDueCommissions($driverId)
    {
        return Commission::where('driver_id', $driverId)
            ->with(['booking'])
            ->orderBy('date', 'desc')
            ->get();
    }
}
