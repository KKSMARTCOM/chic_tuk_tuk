@extends('layouts.app')

@section('content')
    <!-- Recent Bookings -->
    <div class="px-6 py-4 bg-white rounded-lg shadow-md">
        <div class="py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-800">Liste des réservations disponible</h3>
            {{-- <button class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-download mr-2"></i> Exporter
            </button> --}}
        </div>

        <!-- Formulaire de recherche et filtre -->
        <div class="py-4 border-b border-gray-200 bg-gray-50">
            <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Rechercher</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="N° réservation, téléphone, ville de départ ou d'arrivée..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="md:w-48">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select name="status" id="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Tous les statuts</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmé</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>En cours
                        </option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Terminé</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulé</option>
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit"
                        class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-search mr-2"></i> Rechercher
                    </button>
                    <a href="{{ route('admin.bookings.index') }}"
                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                        <i class="fas fa-times mr-2"></i> Effacer
                    </a>
                </div>
            </form>
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
                            Prix</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($bookings as $booking)
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                {{ number_format($booking->total_price, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">

                                <span
                                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ bookingStatusBadge($booking->status) }}">
                                    {{ bookingStatusLabel($booking->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('admin.bookings.show', $booking->id) }}"
                                    class="text-blue-600 hover:text-blue-800 mr-3">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.bookings.edit', $booking->id) }}"
                                    class="text-green-600 hover:text-green-800 mr-3">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                Aucune réservation trouvée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($bookings->hasPages())
        <div class="mt-8">
            {{ $bookings->links('pagination::tailwind') }}
        </div>
    @endif

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
                            showAlert('error', "Erreur lors de l'assignation: " + data.message);
                        }
                    },
                    error: function(xhr) {
                        console.error('Erreur assignation:', xhr.status, xhr.responseText);
                        showAlert('error', xhr.responseJSON.message);
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
                        showAlert('error', xhr.responseJSON.message);
                    }
                });
            }
        </script>
    @endpush
@endsection
