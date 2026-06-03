@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mt-2">Détails du Paiement</h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.payments.edit', $payment) }}"
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-edit mr-2"></i> Modifier
                </a>
                <a href="{{ route('admin.payments.index') }}"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Retour
                </a>
                {{--  <form action="{{ route('admin.payments.destroy', $payment) }}" method="POST" class="inline-block"
                    onclick="return confirm('Êtes-vous sûr ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg">
                        <i class="fas fa-trash mr-2"></i> Supprimer
                    </button>
                </form> --}}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Payment Details -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Informations du Paiement</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-500 text-sm">Montant</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Moyen de Paiement</p>
                        <p class="text-lg font-semibold text-gray-800">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold
                                {{ $payment->payment_method === 'cash' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $payment->payment_method === 'bank_transfer' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $payment->payment_method === 'check' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $payment->payment_method === 'mobile_money' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $payment->payment_method === 'other' ? 'bg-gray-100 text-gray-800' : '' }}
                            ">
                                {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Date de Paiement</p>
                        <p class="text-lg font-semibold text-gray-800">{{ formatDateFr($payment->payment_date) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Date d'Enregistrement</p>
                        <p class="text-lg font-semibold text-gray-800">{{ formatDateTimeFr($payment->created_at) }}</p>
                    </div>
                    @if ($payment->reference_number)
                        <div>
                            <p class="text-gray-500 text-sm">Numéro de Référence</p>
                            <p class="text-lg font-semibold text-gray-800">{{ $payment->reference_number }}</p>
                        </div>
                    @endif
                    @if ($payment->notes)
                        <div class="col-span-2">
                            <p class="text-gray-500 text-sm">Notes</p>
                            <p class="text-gray-800">{{ $payment->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Driver Info -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Informations de l'Agent</h2>
                <div class="flex items-center mb-4">
                    <div>
                        <p class="text-gray-600">Agent</p>
                        <p class="text-xl font-bold text-gray-800">{{ $payment->driver->user?->name ?? 'N/A' }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.payments.driver-details', $payment->driver_id) }}"
                    class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-chart-line mr-2"></i> Voir les détails de paiement complets
                </a>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Driver Stats -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Résumé de l'Agent</h3>

                <div class="mb-4">
                    <p class="text-gray-500 text-sm">Total Dû</p>
                    <p class="text-2xl font-bold text-red-600">
                        {{ number_format($driverStats['total_due'], 0, ',', ' ') }} FCFA
                    </p>
                </div>

                <div class="mb-4">
                    <p class="text-gray-500 text-sm">Total Payé</p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ number_format($driverStats['total_paid'], 0, ',', ' ') }} FCFA
                    </p>
                </div>

                <div class="mb-4 p-4 bg-orange-50 rounded-lg">
                    <p class="text-gray-500 text-sm">Solde Dû</p>
                    <p class="text-2xl font-bold text-orange-600">
                        {{ number_format($driverStats['balance_due'], 0, ',', ' ') }} FCFA
                    </p>
                </div>

                <hr class="my-4">

                <div class="mb-3">
                    <p class="text-gray-600 text-sm">
                        <i class="fas fa-money-bill mr-2 text-green-600"></i>
                        <strong>{{ $driverStats['payments_count'] }}</strong> paiement(s)
                    </p>
                </div>

                <div>
                    <p class="text-gray-600 text-sm">
                        <i class="fas fa-receipt mr-2 text-blue-600"></i>
                        <strong>{{ $driverStats['commissions_count'] }}</strong> commission(s)
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
