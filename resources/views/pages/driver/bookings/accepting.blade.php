@extends('layouts.app')

@section('content')
    <!-- Mes Courses Actives -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-800">Mes Courses Actives</h3>
            <button onclick="location.reload()" class="text-green-600 hover:text-green-700">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
        </div>
        <div class="p-6">
            @if ($bookings->count() > 0)
                <div class="space-y-4">
                    @foreach ($bookings as $booking)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-4">
                                        <span
                                            class="px-3 py-1 text-xs font-semibold rounded-full 
                                        {{ bookingStatusBadge($booking->status) }}">
                                            {{ bookingStatusLabel($booking->status) }}
                                        </span>
                                        <span
                                            class="ml-3 text-sm text-gray-600 font-semibold">{{ $booking->booking_number }}
                                    </div>

                                    <div class="flex items-center gap-4 mb-2">
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $booking->phone) }}?text={{ urlencode('Bonjour, je suis votre chauffeur pour la course ' . $booking->booking_number) }}"
                                            target="_blank"
                                            class="px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800 transition text-sm font-semibold flex items-center justify-center">
                                            <i class="fab fa-whatsapp mr-2"></i> WhatsApp
                                        </a>

                                        <div>
                                            {{-- <h4 class="font-bold text-gray-800">Joana Samson
                                            </h4> --}}
                                            <p class="text-sm text-gray-600"><i class="fas fa-phone mr-1"></i>
                                                {{ $booking->phone }}</p>
                                        </div>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-3 mb-3">
                                        <div class="flex items-start mb-2">
                                            <i class="fas fa-circle text-green-500 text-xs mt-1 mr-3"></i>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800">Point de
                                                    départ</p>
                                                <p class="text-sm text-gray-600">
                                                    {{ $booking->from_location }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start">
                                            <i class="fas fa-circle text-red-500 text-xs mt-1 mr-3"></i>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800">
                                                    Destination</p>
                                                <p class="text-sm text-gray-600">
                                                    {{ $booking->to_location }}</p>
                                            </div>
                                        </div>
                                        {{-- @if ($booking->dropoff_location)
                                @endif --}}
                                    </div>

                                    @if ($booking->status === 'in_progress')
                                        <div id="timer-{{ $booking->id }}"
                                            data-start="{{ $booking->started_at_timestamp }}"
                                            class="mb-6 text-lg font-bold text-blue-600">
                                            ⏱️ 00:00:00
                                        </div>
                                    @endif

                                    <div class="flex items-center space-x-4 text-sm text-gray-600">
                                        <span><i class="far fa-clock mr-1"></i>
                                            {{ formatDateTimeFr($booking->pickup_date_time) }}</span>
                                        {{-- <span><i class="fas fa-users mr-1"></i> {{ $booking->passengers }}
                                            passager(s)</span> --}}
                                        <span class="font-bold text-green-600"><i class="fas fa-money-bill mr-1"></i>
                                            {{ $booking->base_price }} FCFA</span>
                                    </div>

                                    @if ($booking->special_requests)
                                        <div class="mt-3 bg-yellow-50 border-l-4 border-yellow-400 p-3">
                                            <p class="text-sm text-yellow-800"><i
                                                    class="fas fa-info-circle mr-2"></i><strong>Note:</strong>
                                                {{ $booking->special_requests }}</p>
                                        </div>
                                    @endif
                                </div>

                                <div class="ml-4 flex flex-col space-y-2">
                                    @if ($booking->status === 'confirmed')
                                        <button onclick="startTrip('{{ $booking->id }}')"
                                            class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition text-sm font-semibold">
                                            <i class="fas fa-play mr-1"></i> Démarrer
                                        </button>
                                    @endif

                                    @if ($booking->status === 'in_progress')
                                        <button onclick="completeTrip('{{ $booking->id }}')"
                                            class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm font-semibold">
                                            <i class="fas fa-check mr-1"></i> Terminer
                                        </button>
                                    @endif

                                    @if ($booking->canBeCancelled())
                                        <button onclick="cancelTrip('{{ $booking->id }}')"
                                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm font-semibold">
                                            <i class="fas fa-times mr-1"></i> Annuler
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600 text-lg">Aucune course active</p>
                    <p class="text-gray-500 text-sm">Consultez les réservations disponibles pour accepter
                        une course</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal de confirmation de départ -->
    <div id="startModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Démarrer la course</h3>
            <p class="text-gray-600 mb-4">Êtes-vous sûr de vouloir démarrer cette course ?</p>
            <form id="startForm" method="POST">
                @csrf

                <div class="flex space-x-4">
                    <button type="button" onclick="closeStartModal()"
                        class="flex-1 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                        Retour
                    </button>
                    <button type="submit"
                        class="flex-1 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        Confirmer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de confirmation d'arrivée -->
    <div id="arrivalModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">
                Confirmer l'arrivée
            </h3>

            <p class="text-gray-600 mb-6">
                Êtes-vous sûr d’être arrivé à destination et de vouloir terminer cette course ?
            </p>

            <form id="arrivalForm" method="POST">
                @csrf

                <div class="flex space-x-4">
                    <button type="button" onclick="closeArrivalModal()"
                        class="flex-1 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                        Annuler
                    </button>

                    <button type="submit"
                        class="flex-1 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Confirmer l’arrivée
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal d'annulation -->
    <div id="cancelModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Annuler la course</h3>
            <p class="text-gray-600 mb-4">Êtes-vous sûr de vouloir annuler cette course ? Cette action est
                irréversible.</p>
            <form id="cancelForm" method="POST">
                @csrf
                <textarea name="cancellation_reason" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg mb-4"
                    placeholder="Raison de l'annulation (optionnel)"></textarea>
                <div class="flex space-x-4">
                    <button type="button" onclick="closeCancelModal()"
                        class="flex-1 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                        Retour
                    </button>
                    <button type="submit" class="flex-1 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        Confirmer l'annulation
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            // Démarrer une course
            function startTrip(bookingId) {
                const modal = document.getElementById('startModal');
                const form = document.getElementById('startForm');
                form.action = `/driver/bookings/${bookingId}/start`;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeStartModal() {
                document.getElementById('startModal').classList.add('hidden');
                document.getElementById('startModal').classList.remove('flex');
            }

            // Terminer une course
            function completeTrip(bookingId) {
                const modal = document.getElementById('arrivalModal');
                const form = document.getElementById('arrivalForm');
                form.action = `/driver/bookings/${bookingId}/complete`;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeArrivalModal() {
                document.getElementById('arrivalModal').classList.add('hidden');
                document.getElementById('arrivalModal').classList.remove('flex');
            }

            //Annuler une course
            function cancelTrip(bookingId) {
                const modal = document.getElementById('cancelModal');
                const form = document.getElementById('cancelForm');
                form.action = `/driver/bookings/${bookingId}/cancel`;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeCancelModal() {
                document.getElementById('cancelModal').classList.add('hidden');
                document.getElementById('cancelModal').classList.remove('flex');
            }

            // Timers pour la durée de la course
            let timers = {};

            function startTimer(bookingId, startTimestamp) {
                const timerEl = document.getElementById(`timer-${bookingId}`);
                if (!timerEl || isNaN(startTimestamp)) return;

                const start = startTimestamp * 1000;

                timers[bookingId] = setInterval(() => {
                    const diff = Date.now() - start;
                    if (diff < 0) return;

                    const hours = Math.floor(diff / 3600000);
                    const minutes = Math.floor((diff % 3600000) / 60000);
                    const seconds = Math.floor((diff % 60000) / 1000);

                    timerEl.textContent =
                        `⏱️ ${String(hours).padStart(2, '0')}:` +
                        `${String(minutes).padStart(2, '0')}:` +
                        `${String(seconds).padStart(2, '0')}`;
                }, 1000);
            }

            function stopTimer(bookingId) {
                if (timers[bookingId]) {
                    clearInterval(timers[bookingId]);
                    delete timers[bookingId];
                }
            }

            // Démarrer automatiquement les compteurs existants
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[id^="timer-"]').forEach(el => {
                    const bookingId = el.id.replace('timer-', '');
                    const startTimestamp = el.dataset.start;

                    if (startTimestamp) {
                        startTimer(bookingId, parseInt(startTimestamp));
                    }
                });
            });

            // Actualisation automatique toutes les 30 secondes
            /* setInterval(() => {
                location.reload();
            }, 30000); */
        </script>
    @endpush
@endsection
