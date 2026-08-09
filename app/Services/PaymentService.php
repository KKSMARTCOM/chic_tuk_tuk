<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\Driver;
use App\Models\DriverContract;
use App\Models\Payment;
use App\Models\VehicleContract;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Créer un paiement
     */
    public function create(array $data)
    {
        $this->validatePaymentData($data);

        $payment = Payment::create([
            'driver_id'            => $data['driver_id'],
            'payment_type'         => $data['payment_type'] ?? 'commission',
            'amount'               => $data['amount'],
            'vehicle_contract_id'  => $data['vehicle_contract_id'] ?? null,
            'driver_contract_id'   => $data['driver_contract_id']  ?? null,
            'payment_month'        => $data['payment_month']        ?? null,
            'payment_method'       => $data['payment_method'],
            'payment_date'         => $data['payment_date'],
            'notes'                => $data['notes'] ?? null,
            'reference_number'     => $data['reference_number'] ?? null,
            'status'               => 'completed',
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

        if (isset($filters['payment_type']) && !empty($filters['payment_type'])) {
            $query->where('payment_type', $filters['payment_type']);
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
        $totalPaid = Payment::where('status', 'completed')->sum('amount');
        $totalPaidCommission = Payment::where('payment_type', 'commission')->where('status', 'completed')->sum('amount');
        $totalPaidContract = Payment::where('payment_type', 'contract')->where('status', 'completed')->sum('amount');
        $totalDue = Commission::where('status', 'active')->sum('amount');
        $totalPaidThisMonth = Payment::whereMonth('payment_date', Carbon::now()->month)
            ->whereYear('payment_date', Carbon::now()->year)
            ->where('status', 'completed')
            ->sum('amount');
        $paymentsCount = Payment::count();

        return [
            'total_paid' => $totalPaid,
            'total_paid_commission' => $totalPaidCommission,
            'total_paid_contract' => $totalPaidContract,
            'total_due' => $totalDue,
            'balance_due' => $totalDue - $totalPaidCommission,
            'paid_this_month' => $totalPaidThisMonth,
            'payments_count' => $paymentsCount,
        ];
    }

    /**
     * Obtenir les paiements d'un conducteur
     */
    public function getDriverPayments(string $driverId)
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
    public function update(string $paymentId, array $data)
    {
        $payment = Payment::findOrFail($paymentId);
        $this->validatePaymentData($data, $paymentId);
        $payment->update([
            'driver_id' => $data['driver_id'],
            'payment_type' => $data['payment_type'] ?? 'commission',
            'amount' => $data['amount'],
            'vehicle_contract_id' => $data['vehicle_contract_id'] ?? null,
            'driver_contract_id' => $data['driver_contract_id'] ?? null,
            'payment_month' => $data['payment_month'] ?? null,
            'payment_method' => $data['payment_method'],
            'payment_date' => $data['payment_date'],
            'notes' => $data['notes'] ?? null,
            'reference_number' => $data['reference_number'] ?? null,
            'status' => $data['status'] ?? 'pending',
        ]);

        return $payment;
    }

    /**
     * Valider les données de paiement selon le type
     */
    private function validatePaymentData(array &$data, ?string $existingPaymentId = null): void
    {
        $data['payment_type'] = $data['payment_type'] ?? 'commission';

        if ($data['payment_type'] === 'commission') {
            $driver = Driver::findOrFail($data['driver_id']);
            $totalDue = Commission::where('driver_id', $data['driver_id'])->where('status', 'active')->sum('amount');
            $totalPaid = Payment::where('driver_id', $data['driver_id'])->where('payment_type', 'commission');

            if ($existingPaymentId) {
                $totalPaid->where('id', '!=', $existingPaymentId);
            }

            $totalPaid = $totalPaid->sum('amount');
            $remaining = $totalDue - $totalPaid;

            if ($data['amount'] > $remaining) {
                throw new \Exception(
                    "Le montant saisi ({$data['amount']}) dépasse la commission restante due ({$remaining})."
                );
            }

            return;
        }

        if ($data['payment_type'] === 'contract') {
            if (empty($data['vehicle_contract_id']) && empty($data['driver_contract_id'])) {
                throw new \Exception('Un paiement contractuel doit être lié à un contrat agent ou véhicule.');
            }

            if (!empty($data['driver_contract_id'])) {
                $driverContract = DriverContract::findOrFail($data['driver_contract_id']);

                if (empty($data['vehicle_contract_id'])) {
                    $data['vehicle_contract_id'] = $driverContract->vehicle_contract_id;
                }
            }

            if (!empty($data['vehicle_contract_id'])) {
                $vehicleContract = VehicleContract::findOrFail($data['vehicle_contract_id']);
                $contractPaid = Payment::where('vehicle_contract_id', $vehicleContract->id)
                    ->where('payment_type', 'contract');

                if ($existingPaymentId) {
                    $contractPaid->where('id', '!=', $existingPaymentId);
                }

                $contractPaid = $contractPaid->sum('amount');
                $remaining = max(0, (float) $vehicleContract->total_amount - $contractPaid);

                if ($data['amount'] > $remaining) {
                    throw new \Exception(
                        "Le montant saisi ({$data['amount']}) dépasse le solde restant du contrat véhicule ({$remaining})."
                    );
                }
            }
        }
    }

    /**
     * Supprimer un paiement
     */
    public function delete(string $paymentId)
    {
        $payment = Payment::findOrFail($paymentId);
        $payment->delete();
        return true;
    }

    /**
     * Récupérer les commissions dues d'un conducteur (non payées)
     */
    public function getDriverDueCommissions(string $driverId)
    {
        return Commission::where('driver_id', $driverId)
            ->where('status', 'active')
            ->with(['booking'])
            ->orderBy('date', 'desc')
            ->get();
    }
}
