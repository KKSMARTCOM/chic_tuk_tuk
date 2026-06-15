@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">Détails de l'Agent</h1>
                <p class="text-sm md:text-base text-gray-600">{{ $driverData->name }}</p>
            </div>
            <div class="block md:flex gap-3">
                <a href="{{ route('admin.drivers.edit', $driverData) }}"
                    class="bg-purple-600 block text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-edit mr-2"></i> Modifier
                </a>
                <a href="{{ route('admin.drivers.index') }}"
                    class="bg-gray-600 block mt-2 md:mt-0 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Informations principales -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Statistiques des courses -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Statistiques des Courses</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ $bookingStats['total'] }}</div>
                            <div class="text-sm text-gray-600">Total</div>
                        </div>
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">{{ $bookingStats['completed'] }}</div>
                            <div class="text-sm text-gray-600">Terminées</div>
                        </div>
                        <div class="text-center p-4 bg-yellow-50 rounded-lg">
                            <div class="text-2xl font-bold text-yellow-600">{{ $bookingStats['confirmed'] }}</div>
                            <div class="text-sm text-gray-600">Confirmées</div>
                        </div>
                        <div class="text-center p-4 bg-red-50 rounded-lg">
                            <div class="text-2xl font-bold text-red-600">{{ $bookingStats['cancelled'] }}</div>
                            <div class="text-sm text-gray-600">Annulées</div>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-purple-50 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600">
                                {{ number_format($bookingStats['total_minutes'], 2) }}</div>
                            <div class="text-sm text-gray-600">Minutes conduites</div>
                        </div>
                        <div class="text-center p-4 bg-indigo-50 rounded-lg">
                            <div class="text-2xl font-bold text-indigo-600">
                                {{ number_format($bookingStats['average_rating'], 1) }}</div>
                            <div class="text-sm text-gray-600">Note moyenne</div>
                        </div>
                        <div class="text-center p-4 bg-orange-50 rounded-lg">
                            <div class="text-2xl font-bold text-orange-600">{{ $bookingStats['in_progress'] }}</div>
                            <div class="text-sm text-gray-600">En cours</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques des Commissions -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Statistiques des Commissions</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-yellow-50 rounded-lg">
                            <div class="text-2xl font-bold text-yellow-600">
                                {{ number_format($commissionStats['driver_earning'], 0, ',', ' ') }}</div>
                            <div class="text-sm text-gray-600">Revenue Total Agent (FCFA)</div>
                        </div>
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">
                                {{ number_format($commissionStats['unpaid_revenue'], 0, ',', ' ') }}</div>
                            <div class="text-sm text-gray-600">Commission Due (FCFA)</div>
                        </div>
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">
                                {{ number_format($commissionStats['paid_revenue'], 0, ',', ' ') }}</div>
                            <div class="text-sm text-gray-600">Commission Payée (FCFA)</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dernières courses -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Dernières Courses</h3>
                </div>
                <div class="px-6 py-4">
                    @if ($driverData->driver && $driverData->driver->bookings->count() > 0)
                        <div class="space-y-4">
                            @foreach ($driverData->driver->bookings->take(5) as $booking)
                                <div
                                    class="block md:flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                    <div class="block md:flex items-center space-x-4">
                                        <div class="flex-shrink-0 mb-4 md:mb-0">
                                            <span
                                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ bookingStatusBadge($booking->status) }}">
                                                {{ bookingStatusLabel($booking->status) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $booking->from_location ?? 'N/A' }} <br>
                                                → {{ $booking->to_location ?? 'N/A' }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{ formatDateTimeFr($booking->pickup_date_time) }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">Revenue :
                                            {{ number_format($booking->driver_earning, 0, ',', ' ') }} FCFA</p>
                                        <p class="text-sm font-medium text-gray-900">Commission :
                                            {{ number_format($booking->commission, 0, ',', ' ') }} FCFA</p>
                                        <p class="text-sm text-gray-500">{{ $booking->passengers }} passager(s)</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">Aucune course trouvée pour ce Agent.</p>
                    @endif
                </div>
            </div>

            <!-- Informations du Agent -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Informations Personnelles</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nom complet</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $driverData->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $driverData->email ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $driverData->phone }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Statut du compte</label>
                            <span
                                class="mt-1 px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $driverData->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $driverData->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations du Contrat -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Informations du Contrat</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Code Agent</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $driverData->driver->agent_code ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">ID Agent</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $driverData->driver->agent_id ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type de Contrat</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $driverData->driver->contract_type ? $driverData->driver->contract_type . ' mois' : 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date de Début</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $driverData->driver->start_date ? formatDateFr($driverData->driver->start_date) : 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nom complet propriétaire</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $driverData->driver->tricycle_owner ?? 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Numéro propriétaire</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $driverData->driver->owner_phone ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations du véhicule -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Informations du Véhicule</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Catégorie du permis</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $driverData->driver->license_number ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Numéro du véhicule</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $driverData->driver->vehicle_number ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type de véhicule</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ vehiculeType($driverData->driver->vehicle_type) ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Disponibilité</label>
                            <span
                                class="mt-1 px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $driverData->driver->is_available ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $driverData->driver->is_available ? 'Disponible' : 'Indisponible' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions et informations supplémentaires -->
        <div class="space-y-6">
            <!-- Actions rapides -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Actions</h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <button onclick="openLeaveModal()"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                        <i class="fas fa-calendar mr-2"></i>
                        Ajouter une pause
                    </button>

                    <button onclick="openPaymentModal('{{ $driverData->id }}')"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                        <i class="fas fa-money-bill mr-2"></i>
                        Ajouter un paiement
                    </button>

                    <a href="{{ route('admin.payments.driver-details', $driverData->driver->id) }}"
                        class="w-full text-center block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                        <i class="fas fa-chart-line mr-2"></i> Voir les paiements
                    </a>

                    <button
                        onclick="openAvailabilityModal('{{ $driverData->id }}', {{ $driverData->driver->is_available ? 'false' : 'true' }}, '{{ $driverData->name }}', '{{ $driverData->driver->is_available ? 'indisponible' : 'disponible' }}')"
                        class="w-full {{ $driverData->driver->is_available ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-4 py-2 rounded-lg transition">
                        <i class="fas {{ $driverData->driver->is_available ? 'fa-pause' : 'fa-play' }} mr-2"></i>
                        {{ $driverData->driver->is_available ? 'Marquer indisponible' : 'Marquer disponible' }}
                    </button>

                    <button
                        onclick="openStatusModal('{{ $driverData->id }}', {{ $driverData->is_active ? 'false' : 'true' }}, '{{ $driverData->name }}', '{{ $driverData->is_active ? 'désactiver' : 'activer' }}')"
                        class="w-full {{ $driverData->is_active ? 'text-red-600 border border-red-600 hover:text-red-700' : 'text-green-600 border border-green-700 hover:text-green-700' }} px-4 py-2 rounded-lg transition">
                        <i class="fas {{ $driverData->is_active ? 'fa-user-times' : 'fa-user-check' }} mr-2"></i>
                        {{ $driverData->is_active ? 'Désactiver le compte' : 'Activer le compte' }}
                    </button>
                </div>
            </div>

            <!-- Informations système -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Informations Système</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-calendar-plus text-purple-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Membre depuis</p>
                                <p class="text-sm text-gray-500">{{ formatDateFr($driverData->created_at) }}</p>
                            </div>
                        </div>
                        @if ($driverData->updated_at != $driverData->created_at)
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-edit text-blue-500"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Dernière modification</p>
                                    <p class="text-sm text-gray-500">{{ formatDateTimeFr($driverData->updated_at) }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation - Disponibilité -->
    <div id="availabilityModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center px-4 z-20">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Confirmer l'action</h3>
            <p class="text-gray-600 mb-6" id="availabilityMessage"></p>
            <input type="hidden" id="availabilityDriverId" value="">
            <input type="hidden" id="availabilityNewStatus" value="">
            <div class="flex space-x-4">
                <button type="button" onclick="closeAvailabilityModal()"
                    class="flex-1 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                    Annuler
                </button>
                <button type="button" onclick="confirmToggleAvailability()"
                    class="flex-1 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    Confirmer
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation - Statut -->
    <div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center px-4 z-20">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Confirmer l'action</h3>
            <p class="text-gray-600 mb-6" id="statusMessage"></p>
            <input type="hidden" id="statusDriverId" value="">
            <input type="hidden" id="statusNewStatus" value="">
            <div class="flex space-x-4">
                <button type="button" onclick="closeStatusModal()"
                    class="flex-1 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                    Annuler
                </button>
                <button type="button" onclick="confirmToggleStatus()"
                    class="flex-1 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    Confirmer
                </button>
            </div>
        </div>
    </div>

    <!-- Modal d'ajout de pause -->
    <div id="leaveModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center px-4 z-20">
        <div class="bg-white rounded-lg p-8 max-w-xl w-full">
            <h2 class="text-xl font-semibold text-indigo-900 mb-4">Ajouter une Pause instantanée</h2>
            <form action="{{ route('admin.leaves.add-instant', $driverData->driver->id) }}" method="POST"
                id="addInstantLeaveForm">
                @csrf
                <div class="space-y-4">
                    <!-- Date Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Sélectionnez les dates (mois courant)
                        </label>
                        <div class="mb-4">
                            <input type="date" id="adminLeaveDate"
                                class="border border-gray-300 rounded-lg px-4 py-2 w-full"
                                min="{{ now()->toDateString() }}" max="{{ now()->endOfMonth()->toDateString() }}"
                                title="Les dates doivent être dans le mois courant">
                            <button type="button" onclick="adminAddDate()"
                                class="mt-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 w-full font-medium">
                                + Ajouter une date
                            </button>
                        </div>

                        <!-- Selected Dates Display -->
                        <div id="adminSelectedDatesContainer" class="space-y-2">
                            <p class="text-xs text-gray-500 font-semibold uppercase">Dates sélectionnées:</p>
                            <div id="adminSelectedDates"
                                class="flex flex-wrap gap-2 min-h-12 p-3 bg-white rounded-lg border-2 border-dashed border-indigo-300">
                                <p class="text-gray-400 text-sm w-full text-center py-2">Aucune date sélectionnée</p>
                            </div>
                        </div>

                        <!-- Hidden inputs for form submission -->
                        <div id="adminDatesInputs"></div>
                        <p id="adminDateError" class="text-sm text-red-600 mt-2 hidden"></p>
                    </div>

                    <!-- Validation Info -->
                    <div class="p-3 bg-indigo-100 border border-indigo-300 rounded text-sm text-indigo-800 flex flex-col">
                        <span>ℹ️ Les jours doivent être consécutifs.</span>
                        <span>ℹ️ L'agent dispose de
                            <strong>{{ $driverData->driver->available_leave_days }}</strong> jour(s) disponible(s).</span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 pt-2">
                        <button type="submit" id="adminSubmitBtn"
                            class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium transition disabled:bg-gray-300"
                            disabled>
                            Ajouter la Pause
                        </button>
                        <button type="button" onclick="adminClearDates()"
                            class="flex-1 bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-700 font-medium transition">
                            Réinitialiser
                        </button>
                        <button type="button" onclick="closeLeaveModal()"
                            class="flex-1 bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-700 font-medium transition">
                            Annuler
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal d'ajout de paiement -->
    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center px-4 z-20">
        <div class="bg-white rounded-lg p-8 max-w-xl w-full">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Confirmer l'action</h3>
            <form action="{{ route('admin.payments.store') }}" method="POST">
                @csrf

                <!-- Agent -->
                <input type="hidden" value="{{ $driverData->driver->id }}" name="driver_id">

                <!-- Montant -->
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Montant (FCFA) <span
                            class="text-red-600">*</span></label>
                    <input type="number" name="amount" step="0.01" min="0" required placeholder="0.00"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('amount') border-red-500 @enderror"
                        value="{{ old('amount') }}">
                    @error('amount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Moyen de Paiement -->
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Moyen de Paiement <span
                            class="text-red-600">*</span></label>
                    <select name="payment_method" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('payment_method') border-red-500 @enderror">
                        <option value="">-- Sélectionner --</option>
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Espèces</option>
                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>
                            Virement
                            Bancaire</option>
                        <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>Chèque</option>
                        <option value="mobile_money" {{ old('payment_method') == 'mobile_money' ? 'selected' : '' }}>
                            Mobile
                            Money</option>
                        <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Autre</option>
                    </select>
                    @error('payment_method')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date de Paiement -->
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Date de Paiement <span
                            class="text-red-600">*</span></label>
                    <input type="date" name="payment_date" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('payment_date') border-red-500 @enderror"
                        value="{{ old('payment_date', now()->format('Y-m-d')) }}">
                    @error('payment_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Numéro de Référence -->
                {{-- <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Numéro de Référence</label>
                    <input type="text" name="reference_number" placeholder="Numéro de reçu ou référence"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('reference_number') border-red-500 @enderror"
                        value="{{ old('reference_number') }}">
                    @error('reference_number')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div> --}}

                <!-- Notes -->
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Notes</label>
                    <textarea name="notes" rows="4" placeholder="Notes supplémentaires..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex gap-4">
                    <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                        <i class="fas fa-save mr-2"></i> Enregistrer
                    </button>
                    <button type="button" onclick="closePaymentModal()"
                        class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg text-center">
                        <i class="fas fa-times mr-2"></i> Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                }
            });

            //Availability actions managements
            function openAvailabilityModal(driverId, newStatus, driverName, action) {
                const message = `Êtes-vous sûr de vouloir marquer ${driverName} comme ${action} ?`;
                document.getElementById('availabilityMessage').textContent = message;
                document.getElementById('availabilityDriverId').value = driverId;
                document.getElementById('availabilityNewStatus').value = newStatus;
                document.getElementById('availabilityModal').classList.remove('hidden');
                document.getElementById('availabilityModal').classList.add('flex');
            }

            function closeAvailabilityModal() {
                document.getElementById('availabilityModal').classList.add('hidden');
                document.getElementById('availabilityModal').classList.remove('flex');
            }

            function confirmToggleAvailability() {
                const driverId = document.getElementById('availabilityDriverId').value;
                const newStatus = document.getElementById('availabilityNewStatus').value === 'true' ? 1 : 0;

                console.log(newStatus);

                $.ajax({
                    url: `/admin/drivers/${driverId}/toggle-availability`,
                    method: 'POST',
                    data: {
                        is_available: newStatus
                    },
                    success: function(data) {
                        if (data && data.success) {
                            closeAvailabilityModal();
                            showAlert('success', 'Disponibilité mise à jour avec succès');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showAlert('error', "Erreur lors de la mise à jour: " + (data.message ||
                                'Erreur inconnue'));
                        }
                    },
                    error: function(xhr) {
                        console.error('Erreur mise à jour disponibilité:', xhr.status, xhr.responseText);
                        showAlert('error', "Erreur lors de la mise à jour de la disponibilité");
                    }
                });
            }

            //Status actions managements
            function openStatusModal(driverId, newStatus, driverName, action) {
                const message = `Êtes-vous sûr de vouloir ${action} le compte de ${driverName} ?`;
                document.getElementById('statusMessage').textContent = message;
                document.getElementById('statusDriverId').value = driverId;
                document.getElementById('statusNewStatus').value = newStatus;
                document.getElementById('statusModal').classList.remove('hidden');
                document.getElementById('statusModal').classList.add('flex');
            }

            function closeStatusModal() {
                document.getElementById('statusModal').classList.add('hidden');
                document.getElementById('statusModal').classList.remove('flex');
            }

            function confirmToggleStatus() {
                const driverId = document.getElementById('statusDriverId').value;
                const newStatus = document.getElementById('statusNewStatus').value === 'true' ? 1 : 0;

                $.ajax({
                    url: `/admin/drivers/${driverId}/toggle-status`,
                    method: 'POST',
                    data: {
                        is_active: newStatus
                    },
                    success: function(data) {
                        if (data && data.success) {
                            closeStatusModal();
                            showAlert('success', 'Statut du compte mis à jour avec succès');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showAlert('error', "Erreur lors de la mise à jour: " + (data.message ||
                                'Erreur inconnue'));
                        }
                    },
                    error: function(xhr) {
                        console.error('Erreur mise à jour statut:', xhr.status, xhr.responseText);
                        showAlert('error', "Erreur lors de la mise à jour du statut du compte");
                    }
                });
            }

            //Open leave form modal
            function openLeaveModal() {
                document.getElementById('leaveModal').classList.remove('hidden');
                document.getElementById('leaveModal').classList.add('flex');
            }

            //Close leave form modal
            function closeLeaveModal() {
                document.getElementById('leaveModal').classList.add('hidden');
                document.getElementById('leaveModal').classList.remove('flex');
            }

            // Admin instant leave form functions
            let adminSelectedDates = [];
            const adminMaxDays = {{ $driverData->driver->available_leave_days }};

            function adminSetError(message) {
                const error = document.getElementById('adminDateError');
                error.textContent = message;
                error.classList.remove('hidden');
            }

            function adminClearError() {
                const error = document.getElementById('adminDateError');
                error.textContent = '';
                error.classList.add('hidden');
            }

            function adminAddDate() {
                adminClearError();
                const dateInput = document.getElementById('adminLeaveDate');
                const date = dateInput.value;

                if (!date) {
                    adminSetError('Veuillez sélectionner une date.');
                    return;
                }

                if (adminSelectedDates.includes(date)) {
                    adminSetError('Cette date est déjà sélectionnée.');
                    return;
                }

                if (adminSelectedDates.length >= adminMaxDays) {
                    adminSetError(`Vous ne pouvez ajouter que ${adminMaxDays} jour(s) maximum.`);
                    return;
                }

                adminSelectedDates.push(date);
                adminSelectedDates.sort();
                adminUpdateDisplay();
                dateInput.value = '';
                dateInput.focus();
            }

            function adminRemoveDate(date) {
                adminSelectedDates = adminSelectedDates.filter(d => d !== date);
                adminUpdateDisplay();
            }

            function adminUpdateDisplay() {
                const container = document.getElementById('adminSelectedDates');
                const inputsContainer = document.getElementById('adminDatesInputs');
                const submitBtn = document.getElementById('adminSubmitBtn');

                if (adminSelectedDates.length === 0) {
                    container.innerHTML =
                        '<p class="text-gray-400 text-sm w-full text-center py-2">Aucune date sélectionnée</p>';
                    inputsContainer.innerHTML = '';
                    submitBtn.disabled = true;
                    return;
                }

                container.innerHTML = adminSelectedDates.map(date => {
                    const dateObj = new Date(date + 'T00:00:00');
                    const dayName = dateObj.toLocaleDateString('fr-FR', {
                        weekday: 'short'
                    });
                    return `
                        <div class="inline-flex items-center bg-indigo-100 text-indigo-800 px-3 py-2 rounded-lg text-sm font-medium">
                            ${dateObj.toLocaleDateString('fr-FR')} (${dayName})
                            <button type="button" onclick="adminRemoveDate('${date}')" class="ml-2 hover:text-indigo-600 font-bold">
                                ✕
                            </button>
                        </div>
                    `;
                }).join('');

                inputsContainer.innerHTML = adminSelectedDates.map(date => `
                    <input type="hidden" name="dates[]" value="${date}">
                `).join('');

                submitBtn.disabled = false;
            }

            function adminClearDates() {
                adminSelectedDates = [];
                document.getElementById('adminLeaveDate').value = '';
                adminClearError();
                adminUpdateDisplay();
            }

            // Allow Enter key to add date
            document.getElementById('adminLeaveDate').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    adminAddDate();
                    e.preventDefault();
                }
            });

            //Open payment form modal
            function openPaymentModal() {
                document.getElementById('paymentModal').classList.remove('hidden');
                document.getElementById('paymentModal').classList.add('flex');
            }

            //Close payment form modal
            function closePaymentModal() {
                document.getElementById('paymentModal').classList.add('hidden');
                document.getElementById('paymentModal').classList.remove('flex');
            }
        </script>
    @endpush
@endsection
