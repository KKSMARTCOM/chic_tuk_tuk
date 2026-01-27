@extends('layouts.app')

@section('content')
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Total Réservations</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $stats['total_bookings'] }}</p>
                </div>
                <div class="bg-blue-100 rounded-full w-16 h-16 flex justify-center items-center">
                    <i class="fas fa-calendar-check text-blue-600 text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-green-600">
                <i class="fas fa-arrow-up"></i> +12% ce mois
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">En Attente</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $stats['pending_bookings'] }}</p>
                </div>
                <div class="bg-yellow-100 rounded-full w-16 h-16 flex justify-center items-center">
                    <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-gray-600">
                Nécessitent une action
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Conducteurs Actifs</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">
                        {{ $stats['active_drivers'] }}/{{ $stats['total_drivers'] }}</p>
                </div>
                <div class="bg-green-100 rounded-full w-16 h-16 flex justify-center items-center">
                    <i class="fas fa-users text-green-600 text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-green-600">
                <i class="fas fa-arrow-up"></i> +3 cette semaine
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Revenus</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">
                        {{ number_format($stats['total_revenue'], 0, ',', ' ') }}</p>
                </div>
                <div class="bg-purple-100 rounded-full w-16 h-16 flex justify-center items-center">
                    <i class="fas fa-dollar-sign text-purple-600 text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-gray-600">
                FCFA ce mois
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-800">Réservations récentes</h3>

        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            N° Réservation</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Client</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Conducteur</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Trajet</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentBookings as $booking)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $booking->booking_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode($booking->user->name ?? 'Client') }}"
                                        class="w-8 h-8 rounded-full mr-3">
                                    <div>
                                        {{-- <div class="text-sm font-medium text-gray-900">
                                            {{ $booking->user->name ?? 'N/A' }}</div> --}}
                                        <div class="text-sm text-gray-500">{{ $booking->phone }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($booking->driver)
                                    <div class="flex items-center">
                                        <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode($booking->driver->user->name ?? 'Conducteur') }}"
                                            class="w-8 h-8 rounded-full mr-3">
                                        <div class="text-sm text-gray-900">{{ $booking->driver->user->name ?? 'N/A' }}
                                        </div>
                                    </div>
                                    @if ($booking->status !== 'completed')
                                        <button onclick="confirmRemoveDriver('{{ $booking->id }}')"
                                            class="text-red-600 hover:text-red-800 text-sm font-semibold">
                                            <i class="fas fa-user-times"></i> Retirer
                                        </button>
                                    @endif
                                @else
                                    <button onclick="assignDriver('{{ $booking->id }}')"
                                        class="text-purple-600 hover:text-purple-800 text-sm font-semibold">
                                        <i class="fas fa-plus-circle"></i> Assigner
                                    </button>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    {{ $booking->fromZone->name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500"><i class="fas fa-arrow-right"></i>
                                    {{ $booking->toZone->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatDateTimeFr($booking->pickup_datetime) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">

                                <span
                                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ bookingStatusBadge($booking->status) }}">
                                    {{ bookingStatusLabel($booking->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button onclick="viewBooking('{{ $booking->id }}')"
                                    class="text-blue-600 hover:text-blue-800 mr-3">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="editBooking('{{ $booking->id }}')"
                                    class="text-green-600 hover:text-green-800 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                Aucune réservation récente trouvée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid md:grid-cols-3 gap-6 mt-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h4 class="text-lg font-bold text-gray-800 mb-4">Actions rapides</h4>
            <div class="space-y-3">
                <a href="{{ route('admin.bookings.index') }}"
                    class="block w-full bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 transition text-center">
                    <i class="fas fa-plus mr-2"></i> Voir les réservations
                </a>
                <a href="{{ route('admin.drivers.create') }}"
                    class="block w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition text-center">
                    <i class="fas fa-user-plus mr-2"></i> Ajouter conducteur
                </a>
                <a href="{{ route('admin.promo-codes.create') }}"
                    class="block w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition text-center">
                    <i class="fas fa-ticket-alt mr-2"></i> Créer code promo
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h4 class="text-lg font-bold text-gray-800 mb-4">Statistiques du jour</h4>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Courses complétées</span>
                    <span class="font-bold text-green-600">{{ $todayStats['completed_today'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">En cours</span>
                    <span class="font-bold text-blue-600">{{ $todayStats['in_progress_today'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Annulées</span>
                    <span class="font-bold text-red-600">{{ $todayStats['cancelled_today'] }}</span>
                </div>
            </div>
        </div>

        {{-- <div class="bg-white rounded-lg shadow-md p-6">
            <h4 class="text-lg font-bold text-gray-800 mb-4">Notifications</h4>
            <div class="space-y-3">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                    <div>
                        <p class="text-sm text-gray-800 font-semibold">5 nouvelles réservations</p>
                        <p class="text-xs text-gray-500">Il y a 10 minutes</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mt-1 mr-3"></i>
                    <div>
                        <p class="text-sm text-gray-800 font-semibold">2 conducteurs inactifs</p>
                        <p class="text-xs text-gray-500">Il y a 1 heure</p>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>

    <!-- Modal pour assigner un conducteur -->
    <div id="assignDriverModal"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden items-center justify-center z-10">
        <div class="relative mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Assigner un Conducteur</h3>
                <input type="hidden" id="currentBookingId" value="">
                <div class="mb-4">
                    <label for="driverSelect" class="block text-sm font-medium text-gray-700 mb-2">
                        Sélectionnez un conducteur disponible
                    </label>
                    <select id="driverSelect"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Chargement...</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button onclick="closeModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition">
                        Annuler
                    </button>
                    <button onclick="confirmAssign()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                        Assigner
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de retrait -->
    <div id="removeDriverModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-10">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Retirer le conducteur</h3>
            <p class="text-gray-600 mb-4">Êtes-vous sûr de vouloir retirer le conducteur de cette course ? Cette action est
                irréversible.</p>

            <input type="hidden" id="removeBookingId" value="">

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal()"
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                    Annuler
                </button>
                <button type="button" onclick="removeDriver()"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    Retirer
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Setup CSRF pour toutes les requêtes AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                }
            });

            function assignDriver(bookingId) {
                $('#currentBookingId').val(bookingId);

                $('#assignDriverModal').removeClass('hidden').addClass('flex');

                const $select = $('#driverSelect');
                $select.html('<option value="">Chargement...</option>');

                // Charger la liste des conducteurs disponibles
                $.ajax({
                    url: '/admin/drivers',
                    method: 'GET',
                    data: {
                        available: 1
                    },
                    dataType: 'json',
                    success: function(data) {
                        $select.html('<option value="">Sélectionnez un conducteur</option>');

                        if (data && data.data && data.data.length > 0) {
                            data.data.forEach(function(user) {
                                const driverId = user.driver ? user.driver.id : user.id;
                                const driverName = user.name ?? 'Conducteur';

                                $select.append(
                                    $('<option>', {
                                        value: driverId,
                                        text: driverName
                                    })
                                );
                            });
                        } else {
                            $select.html('<option value="">Aucun conducteur disponible</option>');
                        }
                    },
                    error: function(xhr) {
                        console.error('Erreur chargement conducteurs:', xhr.status, xhr.responseText);
                        $select.html('<option value="">Erreur de chargement</option>');
                    }
                });
            }

            function closeModal() {
                if ($('#assignDriverModal')) {
                    $('#assignDriverModal').addClass('hidden').removeClass('flex');
                }

                if ($('#removeDriverModal')) {
                    $('#removeDriverModal').addClass('hidden').removeClass('flex');
                    $('#removeBookingId').val('');
                }
            }

            function confirmAssign() {
                const bookingId = $('#currentBookingId').val();
                const driverId = $('#driverSelect').val();

                if (!driverId) {
                    showAlert('error', 'Veuillez sélectionner un conducteur');
                    return;
                }

                $.ajax({
                    url: `/admin/bookings/${bookingId}/assign-driver`,
                    method: 'POST',
                    contentType: 'application/json',
                    dataType: 'json',
                    data: JSON.stringify({
                        driver_id: driverId
                    }),
                    success: function(data) {
                        if (data && data.success) {
                            closeModal();

                            showAlert('success', data.message);

                            setTimeout(() => location.reload(), 2000);
                        } else {
                            showAlert('error', "Erreur lors de l'assignation: " + (data.message ||
                                'Erreur inconnue'));
                        }
                    },
                    error: function(xhr) {
                        console.error('Erreur assignation:', xhr.status, xhr.responseText);
                        showAlert('error', "Erreur lors de l'assignation");
                    }
                });
            }

            function confirmRemoveDriver(bookingId) {
                $('#removeBookingId').val(bookingId);
                $('#removeDriverModal').removeClass('hidden').addClass('flex');
            }

            function removeDriver() {
                const bookingId = $('#removeBookingId').val();

                $.ajax({
                    url: `/admin/bookings/${bookingId}/remove-driver`,
                    method: 'POST',
                    dataType: 'json',
                    success: function(data) {
                        if (data && data.success) {
                            closeModal();

                            showAlert('success', data.message)

                            setTimeout(() => location.reload(), 2000);
                        } else {
                            showAlert('error', "Erreur lors du retrait: " + (data.message || 'Erreur inconnue'));
                        }
                    },
                    error: function(xhr) {
                        console.error('Erreur retrait:', xhr.status, xhr.responseText);
                        showAlert('error', "Erreur lors du retrait");
                    }
                });
            }

            function viewBooking(bookingId) {
                window.location.href = `/admin/bookings/${bookingId}`;
            }

            function editBooking(bookingId) {
                window.location.href = `/admin/bookings/${bookingId}/edit`;
            }
        </script>
    @endpush
@endsection
