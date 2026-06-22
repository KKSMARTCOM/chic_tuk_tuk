@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">Détails de la Réservation</h1>
                <p class="text-sm md:text-base text-gray-600">N° {{ $booking->booking_number }}</p>
            </div>
            <div class="block md:flex space-x-3">
                @if ($booking->status === 'pending')
                    <a href="{{ route('admin.bookings.edit', $booking) }}"
                        class="bg-purple-600 text-white block px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-edit mr-2"></i> Modifier
                    </a>
                @endif

                <a href="{{ route('admin.bookings.index') }}"
                    class="bg-gray-600 text-white block mt-2 md:mt-0 px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Informations principales -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Détails de la réservation -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Informations de la Réservation</h3>

                    {{-- Badge type de course --}}
                    <div class="flex flex-wrap items-center gap-2">

                        @if ($booking->is_subscription_parent)
                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                <i class="fas fa-rotate"></i>
                                Abonn parent · {{ $booking->days }}j
                            </span>
                        @elseif ($booking->is_subscription_child)
                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-teal-50 text-teal-700 border border-teal-200">
                                <i class="fas fa-link"></i>
                                {{ $booking->subscription_label }}
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-600 border border-blue-200">
                                <i class="fas fa-car-side"></i>
                                Course unique
                            </span>
                        @endif

                        @if ($booking->round_trip)
                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-600 border border-purple-200">
                                <i class="fas fa-arrows-left-right"></i>
                                Aller-Retour
                            </span>
                        @endif

                        @if ($booking->trip_type === 'return')
                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-50 text-orange-600 border border-orange-200">
                                <i class="fas fa-arrow-left"></i>
                                Retour
                            </span>
                        @endif

                        @if ($booking->is_revoked)
                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600 border border-red-200">
                                <i class="fas fa-ban"></i>
                                Révoquée
                            </span>
                        @endif

                    </div>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Client --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Client</label>
                            <div class="mt-1 flex items-center">
                                <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode($booking->client_name ?? 'Client') }}"
                                    class="w-10 h-10 rounded-full mr-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $booking->client_name ?? 'N/A' }}</p>
                                    <p class="text-sm text-gray-500">{{ $booking->user->email ?? '' }}</p>
                                    <p class="text-sm text-gray-500">{{ $booking->phone }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Agent --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Agent</label>
                            @if ($booking->driver)
                                <div class="mt-1 flex items-center">
                                    <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode($booking->driver->user->name ?? 'Agent') }}"
                                        class="w-10 h-10 rounded-full mr-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $booking->driver->user->name ?? 'N/A' }}</p>
                                        <p class="text-sm text-gray-500">{{ $booking->driver->user->email ?? '' }}</p>
                                    </div>
                                </div>
                            @else
                                <p class="mt-1 text-sm text-gray-500">Aucun Agent assigné</p>
                            @endif
                        </div>

                        {{-- Agent abonnement lié --}}
                        @if ($booking->is_recurring && $booking->subscriptionDriver)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Agent lié à l'abonnement</label>
                                <div class="mt-1 flex items-center gap-3">
                                    <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode($booking->subscriptionDriver->user->name ?? 'Agent') }}"
                                        class="w-8 h-8 rounded-full">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $booking->subscriptionDriver->user->name }}</p>
                                        <p class="text-xs text-gray-500">Lié à toutes les courses de cet abonnement</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Trajet --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Trajet</label>
                            <div class="mt-1">
                                <p class="text-sm text-gray-900">{{ $booking->from_location ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-500"><i class="fas fa-arrow-right"></i>
                                    {{ $booking->to_location ?? 'N/A' }}</p>
                            </div>
                        </div>

                        {{-- Distance --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Distance estimée</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $booking->distance ?? 'N/A' }} km</p>
                        </div>

                        {{-- Date départ --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date et heure de départ</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ formatDateTimeFr($booking->pickup_date_time) }}</p>
                            </p>
                        </div>

                        {{-- Heure de retour si aller-retour --}}
                        @if ($booking->round_trip && $booking->return_time)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Heure de retour</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $booking->return_time }}</p>
                            </div>
                        @endif

                        {{-- Prix du trajet --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Prix du trajet </label>
                            <p class="mt-1 text-lg font-semibold text-purple-600">
                                {{ number_format($booking->base_price, 0, ',', ' ') }} FCFA</p>
                        </div>

                        {{-- Prix total si abonnement --}}
                        @if ($booking->days > 1)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Prix de l'abonnement </label>
                                <p class="mt-1 text-lg font-semibold text-purple-600">
                                    {{ number_format($booking->total_price, 0, ',', ' ') }} FCFA</p>
                            </div>
                        @endif

                        {{-- Commission --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 flex items-center gap-1">
                                Commission
                                @if (!$booking->commission)
                                    <span
                                        class="text-xs font-normal text-amber-500 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded-full ml-1">
                                        Aperçu
                                    </span>
                                @endif
                            </label>
                            <p class="mt-1 text-lg font-semibold text-purple-600">
                                {{ number_format($booking->commission_preview, 0, ',', ' ') }} FCFA
                            </p>
                        </div>

                        {{-- Revenu agent --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 flex items-center gap-1">
                                Revenu agent
                                @if (!$booking->driver_earning)
                                    <span
                                        class="text-xs font-normal text-amber-500 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded-full ml-1">
                                        Aperçu
                                    </span>
                                @endif
                            </label>
                            <p class="mt-1 text-lg font-semibold text-purple-600">
                                {{ number_format($booking->driver_earning_preview, 0, ',', ' ') }} FCFA
                            </p>
                        </div>

                        {{-- Statut --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Statut</label>
                            <span
                                class="mt-1 px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ bookingStatusBadge($booking->status) }}">
                                {{ bookingStatusLabel($booking->status) }}
                            </span>
                        </div>

                        {{-- Révocation --}}
                        @if ($booking->is_revoked)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Révocation</label>
                                <p class="mt-1 text-sm text-red-500">
                                    <i class="fas fa-ban mr-1"></i>
                                    Course révoquée le {{ formatDateTimeFr($booking->revoked_at) }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Détails supplémentaires -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Détails Supplémentaires</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre de passagers</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $booking->passengers }}</p>
                        </div>
                        {{-- <div>
                            <label class="block text-sm font-medium text-gray-700">Circuit touristique</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $booking->touristCircuit->name ?? 'N/A' }}</p>
                        </div> --}}
                        {{-- <div>
                            <label class="block text-sm font-medium text-gray-700">Code promo</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $booking->promoCode->code ?? 'Aucun' }}</p>
                        </div> --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre de jours</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $booking->days ?? 1 }}</p>
                        </div>
                        @if ($booking->days > 1)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jours de circulation</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ match ($booking->week_days) {
                                        'lun_ven' => 'Lundi → Vendredi',
                                        'lun_sam' => 'Lundi → Samedi',
                                        'lun_dim' => 'Lundi → Dimanche',
                                        default => 'N/A',
                                    } }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jours restants</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ $booking->remaining_days > 1 ? $booking->remaining_days . ' jour(s)' : 'Dernier jour' }}
                                </p>
                            </div>

                            @if ($booking->is_recurring)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Date de fin de
                                        l'abonnement</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ formatDateFr($booking->subscription_end_date) }}</p>
                                </div>
                            @endif

                            @if ($booking->is_subscription_child)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Abonnement parent</label>
                                    <a href="{{ route('admin.bookings.show', $booking->parent_booking_id) }}"
                                        class="mt-1 inline-flex items-center gap-1 text-sm text-[#286b41] hover:underline">
                                        <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                                        {{ $booking->parentBooking?->booking_number }}
                                    </a>
                                </div>
                            @endif

                            @if ($booking->is_subscription_parent)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Courses enfants</label>
                                    <div class="mt-1 space-y-1">
                                        @forelse ($booking->childBookings as $child)
                                            <a href="{{ route('admin.bookings.show', $child) }}"
                                                class="flex items-center justify-between text-sm text-gray-700 hover:text-[#286b41] hover:underline">
                                                <span>
                                                    <i class="fas fa-link text-xs text-gray-400 mr-1"></i>
                                                    {{ $child->subscription_label }}
                                                </span>
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-xs {{ bookingStatusBadge($child->status) }}">
                                                    {{ bookingStatusLabel($child->status) }}
                                                </span>
                                            </a>
                                        @empty
                                            <p class="text-sm text-gray-400">Aucune course enfant encore créée</p>
                                        @endforelse
                                    </div>
                                </div>
                            @endif
                        @endif

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Instructions spéciales</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $booking->special_requests ?? 'Aucune' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions et historique -->
        <div class="space-y-6">
            <!-- Actions rapides -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Actions</h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    @if (!$booking->driver && in_array($booking->status, ['pending']))
                        <button onclick="assignDriver('{{ $booking->id }}')"
                            class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                            <i class="fas fa-user-plus mr-2"></i> Assigner un Agent
                        </button>
                    @elseif(!in_array($booking->status, ['completed', 'cancelled', 'expired']))
                        <button onclick="confirmRemoveDriver('{{ $booking->id }}')"
                            class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                            <i class="fas fa-user-times mr-2"></i> Retirer le Agent
                        </button>
                    @endif

                    @if (in_array($booking->status, ['cancelled', 'expired']))
                        <button onclick="openDeleteModal('{{ $booking->id }}')"
                            class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                            <i class="fas fa-user-times mr-2"></i> Supprimer la réservation
                        </button>
                    @endif

                    @if (in_array($booking->status, ['pending', 'confirmed']))
                        <button onclick="openCancelModal('{{ $booking->id }}')"
                            class="w-full text-red-600 border border-red-600 px-4 py-2 rounded-lg hover:text-red-700 hover:border-red-700 transition">
                            <i class="fas fa-times mr-2"></i> Annuler la course
                        </button>
                    @endif
                </div>
            </div>

            <!-- Historique -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Historique</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-calendar-plus text-purple-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Réservation créée</p>
                                <p class="text-sm text-gray-500">{{ formatDateTimeFr($booking->created_at) }}</p>
                            </div>
                        </div>
                        @if ($booking->updated_at != $booking->created_at)
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-edit text-blue-500"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Dernière modification</p>
                                    <p class="text-sm text-gray-500">{{ formatDateTimeFr($booking->updated_at) }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour assigner un Agent -->
    <div id="assignDriverModal"
        class="fixed px-4 inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden items-center justify-center z-10">
        <div class="relative mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Assigner un Agent</h3>
                <input type="hidden" id="currentBookingId" value="">
                <div class="mb-4">
                    <label for="driverSelect" class="block text-sm font-medium text-gray-700 mb-2">
                        Sélectionnez un Agent disponible
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
    <div id="removeDriverModal" class="fixed px-4 inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-10">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Retirer le Agent</h3>
            <p class="text-gray-600 mb-4">Êtes-vous sûr de vouloir retirer le Agent de cette course ? Cette action est
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

    <!-- Modal d'annulation -->
    <div id="cancelModal" class="fixed px-4 inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-10">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Annuler la course</h3>
            <p class="text-gray-600 mb-4">Êtes-vous sûr de vouloir annuler cette course ? Cette action est
                irréversible.</p>
            <form id="cancelForm" method="POST" action="">
                @csrf
                <input type="hidden" name="status" value="cancelled">
                <textarea name="cancellation_reason" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg mb-4"
                    placeholder="Raison de l'annulation (optionnel)"></textarea>
                <div class="flex space-x-4">
                    <button type="button" onclick="closeCancelModal()"
                        class="flex-1 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                        Retour
                    </button>
                    <button type="submit"
                        class="flex-1 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        Confirmer l'annulation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de suppression -->
    <div id="deleteModal" class="fixed px-4 inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-10">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Supprimer la course</h3>
            <p class="text-gray-600 mb-4">Êtes-vous sûr de vouloir supprimer cette course ? Cette action est
                irréversible.</p>
            <form id="deleteForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <input type="hidden" name="status" value="">
                <div class="flex space-x-4">
                    <button type="button" onclick="closeDeleteModal()"
                        class="flex-1 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                        Retour
                    </button>
                    <button type="submit"
                        class="flex-1 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        Supprimer
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

            function assignDriver(bookingId) {
                $('#currentBookingId').val(bookingId);
                $('#assignDriverModal').removeClass('hidden').addClass('flex');

                const $select = $('#driverSelect');
                $select.html('<option value="">Chargement...</option>');

                $.ajax({
                    url: '/admin/drivers',
                    method: 'GET',
                    data: {
                        available: 1
                    },
                    dataType: 'json',
                    success: function(data) {
                        $select.html('<option value="">Sélectionnez un Agent</option>');
                        if (data && data.length > 0) {
                            data.forEach(function(user) {
                                const driverId = user.driver ? user.driver.id : user.id;
                                const driverName = user.name ?? 'Agent';
                                $select.append($('<option>', {
                                    value: driverId,
                                    text: driverName
                                }));
                            });
                        } else {
                            $select.html('<option value="">Aucun Agent disponible</option>');
                        }
                    },
                    error: function(xhr) {
                        console.error('Erreur chargement Agents:', xhr.status, xhr.responseText);
                        $select.html('<option value="">Erreur de chargement</option>');
                    }
                });
            }

            function closeModal() {
                $('#assignDriverModal').addClass('hidden').removeClass('flex');
                $('#removeDriverModal').addClass('hidden').removeClass('flex');
                $('#removeBookingId').val('');
            }

            function confirmAssign() {
                const bookingId = $('#currentBookingId').val();
                const driverId = $('#driverSelect').val();

                if (!driverId) {
                    showAlert('error', 'Veuillez sélectionner un Agent');
                    return;
                }

                $.ajax({
                    url: `/admin/bookings/${bookingId}/assign-driver`,
                    method: 'POST',
                    contentType: 'application/json',
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
                    success: function(data) {
                        if (data && data.success) {
                            closeModal();
                            showAlert('success', data.message);
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

            function updateStatus(bookingId, status) {
                if (!confirm('Êtes-vous sûr de vouloir changer le statut ?')) return;

                $.ajax({
                    url: `/admin/bookings/${bookingId}/update-status`,
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        status: status
                    }),
                    success: function(data) {
                        if (data && data.success) {
                            showAlert('success', 'Statut mis à jour avec succès');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showAlert('error', "Erreur lors de la mise à jour: " + (data.message ||
                                'Erreur inconnue'));
                        }
                    },
                    error: function(xhr) {
                        console.error('Erreur mise à jour statut:', xhr.status, xhr.responseText);
                        showAlert('error', "Erreur lors de la mise à jour du statut");
                    }
                });
            }

            function openCancelModal(bookingId) {
                $('#cancelForm').attr('action', `/admin/bookings/${bookingId}/update-status`);
                $('#cancelModal').removeClass('hidden').addClass('flex');
            }

            function closeCancelModal() {
                $('#cancelModal').addClass('hidden').removeClass('flex');
                $('#cancelForm')[0].reset();
            }

            // Gestionnaire pour la soumission du formulaire d'annulation
            $('#cancelForm').on('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        if (data && data.success) {
                            closeCancelModal();
                            showAlert('success', 'Course annulée avec succès');
                            setTimeout(() => location.reload(), 2000);
                        } else {
                            showAlert('error', "Erreur lors de l'annulation: " + (data.message ||
                                'Erreur inconnue'));
                        }
                    },
                    error: function(xhr) {
                        console.error('Erreur annulation:', xhr.status, xhr.responseText);
                        showAlert('error', xhr.responseJSON
                            .message);
                    }
                });
            });

            function openDeleteModal(bookingId) {
                $('#deleteForm').attr('action', `/admin/bookings/${bookingId}`);
                $('#deleteModal').removeClass('hidden').addClass('flex');
            }

            function closeDeleteModal() {
                $('#deleteModal').addClass('hidden').removeClass('flex');
                $('#deleteForm')[0].reset();
            }
        </script>
    @endpush
@endsection
