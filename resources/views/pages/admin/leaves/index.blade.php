@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 block md:flex items-center justify-between">
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">Gestion des Pauses</h1>
                <p class="text-sm md:text-base text-gray-600">Gérez les Pauses de vos agents</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('admin.leave.requests.index') }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Demandes en attente
                    @if (collect($drivers)->sum('pending_requests') > 0)
                        <span class="ml-2 bg-red-500 text-white px-2 py-1 rounded-full text-sm">
                            {{ collect($drivers)->sum('pending_requests') }}
                        </span>
                    @endif
                </a>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-lg shadow-md mb-8 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Filtres et Recherche</h2>
            <a href="{{ route('admin.leaves.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                <i class="fas fa-redo mr-1"></i> Réinitialiser
            </a>
        </div>

        <form method="GET" action="{{ route('admin.leaves.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search by Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher par nom</label>
                    <input type="text" name="search" placeholder="Nom de l'agent..." value="{{ request('search') }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Filter by Available Days -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jours Disponibles</label>
                    <select name="available"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Tous</option>
                        <option value="yes" @if (request('available') === 'yes') selected @endif>Avec jours disponibles
                        </option>
                        <option value="no" @if (request('available') === 'no') selected @endif>Aucun jour disponible
                        </option>
                    </select>
                </div>

                <!-- Filter by Pending Requests -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Demandes en attente</label>
                    <select name="pending"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Tous</option>
                        <option value="yes" @if (request('pending') === 'yes') selected @endif>Avec demandes</option>
                        <option value="no" @if (request('pending') === 'no') selected @endif>Sans demandes</option>
                    </select>
                </div>

                <!-- Filter by Contract Duration -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Durée contrat</label>
                    <select name="contract"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Tous</option>
                        @php
                            $contracts = collect($drivers)->pluck('contract_type')->unique()->sort();
                        @endphp
                        @foreach ($contracts as $contract)
                            <option value="{{ $contract }}" @if (request('contract') == $contract) selected @endif>
                                {{ $contract }} mois</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3">
                <button type="submit"
                    class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 font-medium transition">
                    <i class="fas fa-search mr-2"></i> Filtrer
                </button>
                <a href="{{ route('admin.leaves.index') }}"
                    class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 font-medium transition">
                    <i class="fas fa-times mr-2"></i> Effacer
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        @if ($drivers && $drivers->count() > 0)
            <div class="overflow-x-auto p-4">
                <table class="min-w-full divide-y divide-gray-200 display" id="datatable1">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Agent</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Durée contrat</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Jours/Mois</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Total</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Utilisés</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Disponibles</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Restants</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                En attente</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($drivers as $driver)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $driver['name'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $driver['contract_type'] ?? 'N/A' }} mois
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $driver['leave_days_per_month'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $driver['total_leave_days'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $driver['leave_days_used'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        class="text-sm font-semibold {{ $driver['available_leave_days'] > 0 ? 'text-indigo-600' : 'text-orange-600' }}">
                                        {{ $driver['available_leave_days'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        class="text-sm font-semibold {{ $driver['remaining_leave_days'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $driver['remaining_leave_days'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($driver['pending_requests'] > 0)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                            {{ $driver['pending_requests'] }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('admin.leaves.show', $driver['id']) }}"
                                        class="text-indigo-600 hover:text-indigo-900">Voir détails</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <p class="px-6 py-4 text-center text-gray-500">Aucune demande de pause.</p>
                <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            $("#datatable1").DataTable({
                order: [
                    [5, "desc"]
                ],
                columnDefs: [{
                    targets: 5,
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
