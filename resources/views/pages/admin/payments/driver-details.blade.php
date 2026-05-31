@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <a href="{{ route('admin.payments.index') }}" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-arrow-left mr-2"></i> Retour
                </a>
                <h1 class="text-2xl font-bold text-gray-800 mt-2">Détails de Paiement -
                    {{ $driverStats['driver']->user?->name ?? 'N/A' }}</h1>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Total Dû -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Total Dû</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">
                        {{ number_format($driverStats['total_due'], 0, ',', ' ') }} FCFA
                    </p>
                </div>
                <div class="bg-red-100 rounded-full w-16 h-16 flex justify-center items-center">
                    <i class="fas fa-exclamation-circle text-red-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Payé -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Total Payé</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ number_format($driverStats['total_paid'], 0, ',', ' ') }} FCFA
                    </p>
                </div>
                <div class="bg-green-100 rounded-full w-16 h-16 flex justify-center items-center">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Solde Dû -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Solde Dû</p>
                    <p class="text-3xl font-bold text-orange-600 mt-2">
                        {{ number_format($driverStats['balance_due'], 0, ',', ' ') }} FCFA
                    </p>
                </div>
                <div class="bg-orange-100 rounded-full w-16 h-16 flex justify-center items-center">
                    <i class="fas fa-balance-scale text-orange-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div>
                <p class="text-gray-600 text-sm mb-3">
                    <i class="fas fa-money-bill mr-2 text-green-600"></i>
                    <strong>{{ $driverStats['payments_count'] }}</strong> paiement(s)
                </p>
                <p class="text-gray-600 text-sm">
                    <i class="fas fa-receipt mr-2 text-blue-600"></i>
                    <strong>{{ $driverStats['commissions_count'] }}</strong> commission(s)
                </p>
            </div>
        </div>
    </div>

    <!-- Paiements -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Paiements Enregistrés -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-800">Paiements Enregistrés</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Montant</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Méthode</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-semibold text-green-600">
                                    {{ number_format($payment->amount, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold
                                        {{ $payment->payment_method === 'cash' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $payment->payment_method === 'bank_transfer' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $payment->payment_method === 'check' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $payment->payment_method === 'mobile_money' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $payment->payment_method === 'other' ? 'bg-gray-100 text-gray-800' : '' }}
                                    ">
                                        {{ substr(str_replace('_', ' ', $payment->payment_method), 0, 10) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $payment->payment_date->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <a href="{{ route('admin.payments.show', $payment) }}"
                                        class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-center text-gray-500">
                                    Aucun paiement enregistré
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($payments->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>

        <!-- Commissions Dues -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-800">Commissions Dues</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Course</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Montant</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($commissions as $commission)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $commission->booking?->booking_number ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-orange-600">
                                    {{ number_format($commission->amount, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $commission->date->format('d/m/Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-4 text-center text-gray-500">
                                    Aucune commission due
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
