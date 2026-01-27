@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Détails du Conducteur</h1>
                <p class="text-gray-600">{{ $driverData->name }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.drivers.edit', $driverData) }}"
                    class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-edit mr-2"></i> Modifier
                </a>
                <a href="{{ route('admin.drivers.index') }}"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
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
                            <div class="text-2xl font-bold text-purple-600">{{ $bookingStats['total_minutes'] }}</div>
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

            <!-- Dernières courses -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Dernières Courses</h3>
                </div>
                <div class="px-6 py-4">
                    @if ($driverData->driver && $driverData->driver->bookings->count() > 0)
                        <div class="space-y-4">
                            @foreach ($driverData->driver->bookings->take(5) as $booking)
                                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <span
                                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ bookingStatusBadge($booking->status) }}">
                                                {{ bookingStatusLabel($booking->status) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $booking->fromZone->name ?? 'N/A' }} →
                                                {{ $booking->toZone->name ?? 'N/A' }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{ formatDateTimeFr($booking->pickup_datetime) }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ number_format($booking->total_price, 0, ',', ' ') }} FCFA</p>
                                        <p class="text-sm text-gray-500">{{ $booking->passengers }} passager(s)</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">Aucune course trouvée pour ce conducteur.</p>
                    @endif
                </div>
            </div>

            <!-- Informations du conducteur -->
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
                            <p class="mt-1 text-sm text-gray-900">{{ $driverData->email }}</p>
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

            <!-- Informations du véhicule -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Informations du Véhicule</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Numéro de permis</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $driverData->driver->license_number ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Numéro du véhicule</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $driverData->driver->vehicle_number ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type de véhicule</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $driverData->driver->vehicle_type ?? 'N/A' }}</p>
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
                    <button
                        onclick="openAvailabilityModal('{{ $driverData->id }}', {{ $driverData->driver->is_available ? 'false' : 'true' }}, '{{ $driverData->name }}', '{{ $driverData->driver->is_available ? 'indisponible' : 'disponible' }}')"
                        class="w-full {{ $driverData->driver->is_available ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-4 py-2 rounded-lg transition">
                        <i class="fas {{ $driverData->driver->is_available ? 'fa-pause' : 'fa-play' }} mr-2"></i>
                        {{ $driverData->driver->is_available ? 'Marquer indisponible' : 'Marquer disponible' }}
                    </button>

                    <button
                        onclick="openStatusModal('{{ $driverData->id }}', {{ $driverData->is_active ? 'false' : 'true' }}, '{{ $driverData->name }}', '{{ $driverData->is_active ? 'désactiver' : 'activer' }}')"
                        class="w-full {{ $driverData->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-4 py-2 rounded-lg transition">
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
    <div id="availabilityModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-20">
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
    <div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-20">
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

    @push('scripts')
        <script>
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                }
            });

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
        </script>
    @endpush
@endsection
