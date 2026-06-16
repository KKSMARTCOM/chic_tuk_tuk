@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 block md:flex items-center justify-between">
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">Gestion des Paiements</h1>
                <p class="text-sm md:text-base text-gray-600">Enregistrez et gérez les paiements des Agents</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('admin.payments.create') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-nowrap font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-plus mr-2"></i> Nouveau Paiement
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Payé -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Total Payé</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ number_format($stats['total_paid'], 0, ',', ' ') }} FCFA
                    </p>
                </div>
                <div class="bg-green-100 rounded-full w-16 h-16 flex justify-center items-center">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-green-600">
                <i class="fas fa-info-circle"></i> {{ $stats['payments_count'] }} paiement(s)
            </div>
        </div>

        <!-- Total Dû -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Total Dû</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">
                        {{ number_format($stats['total_due'], 0, ',', ' ') }} FCFA
                    </p>
                </div>
                <div class="bg-red-100 rounded-full w-16 h-16 flex justify-center items-center">
                    <i class="fas fa-exclamation-circle text-red-600 text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-red-600">
                <i class="fas fa-arrow-up"></i> Commission due
            </div>
        </div>

        <!-- Solde Dû -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Solde Dû</p>
                    <p class="text-3xl font-bold text-orange-600 mt-2">
                        {{ number_format($stats['balance_due'], 0, ',', ' ') }} FCFA
                    </p>
                </div>
                <div class="bg-orange-100 rounded-full w-16 h-16 flex justify-center items-center">
                    <i class="fas fa-balance-scale text-orange-600 text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-orange-600">
                <i class="fas fa-info-circle"></i> Montant encore à payer
            </div>
        </div>

        <!-- Payé Ce Mois -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Payé Ce Mois</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        {{ number_format($stats['paid_this_month'], 0, ',', ' ') }} FCFA
                    </p>
                </div>
                <div class="bg-blue-100 rounded-full w-16 h-16 flex justify-center items-center">
                    <i class="fas fa-calendar text-blue-600 text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-blue-600">
                <i class="fas fa-info-circle"></i> Mois actuel
            </div>
        </div>
    </div>

    <!-- Filters -->
    @if ($payments && $payments->count() > 0)
        <div class="bg-white rounded-lg shadow-md mb-8 p-6">
            <form method="GET" action="{{ route('admin.payments.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Agent</label>
                    <select name="driver_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Tous les agents --</option>
                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                {{ $driver->user?->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Moyen de Paiement</label>
                    <select name="payment_method"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Tous --</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Espèces</option>
                        <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>
                            Virement Bancaire</option>
                        <option value="check" {{ request('payment_method') == 'check' ? 'selected' : '' }}>Chèque</option>
                        <option value="mobile_money" {{ request('payment_method') == 'mobile_money' ? 'selected' : '' }}>
                            Mobile
                            Money</option>
                        <option value="other" {{ request('payment_method') == 'other' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Recherche</label>
                    <input type="text" name="search" placeholder="Nom ou Référence..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        value="{{ request('search') }}">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Du</label>
                    <input type="date" name="date_from"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        value="{{ request('date_from') }}">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Au</label>
                    <input type="date" name="date_to"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        value="{{ request('date_to') }}">
                </div>

                <div class="flex items-end">
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                        <i class="fas fa-search mr-2"></i> Filtrer
                    </button>
                </div>

                <div class="flex items-end">
                    <a href="{{ route('admin.payments.index') }}"
                        class="w-full bg-gray-400 hover:bg-gray-500 text-white font-semibold py-2 px-4 rounded-lg text-center">
                        <i class="fas fa-redo mr-2"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    @endif

    <!-- Payments Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        @if ($payments && $payments->count() > 0)
            <div class="overflow-x-auto p-4">
                <table class="min-w-full divide-y divide-gray-200 display" id="datatable1">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Agent
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Montant
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Moyen
                                de Paiement</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Date du paiement
                            </th>
                            {{-- <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Référence</th> --}}
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <div class="font-semibold">{{ $payment->driver->user?->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">ID: {{ $payment->driver->agent_id ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-green-600">
                                    {{ number_format($payment->amount, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-6 py-4 text-sm">
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
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ formatDateFr($payment->payment_date) }}
                                </td>
                                {{-- <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $payment->reference_number ?? '--' }}
                                </td> --}}
                                <td class="px-6 py-4 text-left text-sm">
                                    <a href="{{ route('admin.payments.show', $payment) }}"
                                        class="text-blue-600 hover:text-blue-800 mr-3">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.payments.edit', $payment) }}"
                                        class="text-green-600 hover:text-green-800 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    {{-- <form action="{{ route('admin.payments.destroy', $payment) }}" method="POST"
                                        class="inline-block" onclick="return confirm('Êtes-vous sûr ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form> --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    Aucun paiement enregistré
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <p class="px-6 py-4 text-center text-gray-500">Aucun paiement enrégistré</p>
                <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            $("#datatable1").DataTable({
                order: [
                    [3, "desc"]
                ],
                columnDefs: [{
                    targets: 3,
                    searchable: false,
                }, ],
                language: {
                    processing: "Traitement en cours...",
                    search: "Rechercher : ",
                    lengthMenu: "Afficher _MENU_ éléments",
                    info: "Affichage de _START_ à _END_ sur _TOTAL_ ",
                    infoEmpty: "Affichage de 0 à 0 sur 0",
                    infoFiltered: "(filtré de _MAX_ éléments au total)",
                    loadingRecords: "Chargement en cours...",
                    zeroRecords: "Aucun élément à afficher",
                    emptyTable: "Aucune donnée disponible dans le tableau",
                },
                // Callback pour appliquer select2 après init
                initComplete: function() {
                    if (typeof $.fn.select2 !== "undefined") {
                        $(".dataTables_length select").select2({
                            minimumResultsForSearch: Infinity,
                        });
                    }
                },
            })
        </script>
    @endpush
@endsection
