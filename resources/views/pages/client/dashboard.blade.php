@extends('layouts.app')

@section('content')
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-semibold mb-1">Total Courses</p>
                    <p class="text-4xl font-bold">25</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-car text-3xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm font-semibold mb-1">Note Moyenne</p>
                    <p class="text-4xl font-bold">4.5<span class="text-2xl">/5</span></p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-star text-3xl"></i>
                </div>
            </div>
            <div class="mt-3 flex">
                @for ($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star {{ $i <= 4 ? 'text-white' : 'text-yellow-200' }}"></i>
                @endfor
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-semibold mb-1">Gains Aujourd'hui</p>
                    <p class="text-4xl font-bold">500</p>
                    <p class="text-green-100 text-xs">FCFA</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-money-bill-wave text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Mes Courses Actives -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-800">Mes Courses Actives</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <span
                                    class="px-3 py-1 text-xs font-semibold rounded-full 
                                        {{ 'confirmed' === 'confirmed' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ 'confirmed' === 'confirmed' ? 'Confirmée' : 'En cours' }}
                                </span>
                                <span class="ml-3 text-sm text-gray-600 font-semibold">11FFG
                            </div>

                            <div class="flex items-start mb-3">
                                <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode('Joana') }}"
                                    class="w-12 h-12 rounded-full mr-4">
                                <div>
                                    <h4 class="font-bold text-gray-800">Joana Samson
                                    </h4>
                                    <p class="text-sm text-gray-600"><i class="fas fa-phone mr-1"></i>
                                        015248795</p>
                                </div>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-3 mb-3">
                                <div class="flex items-start mb-2">
                                    <i class="fas fa-circle text-green-500 text-xs mt-1 mr-3"></i>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">Point de
                                            départ</p>
                                        <p class="text-sm text-gray-600">
                                            Abomey-Calavi</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <i class="fas fa-circle text-red-500 text-xs mt-1 mr-3"></i>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">
                                            Destination</p>
                                        <p class="text-sm text-gray-600">
                                            Cotonou</p>
                                    </div>
                                </div>
                                {{-- @if ($booking->dropoff_location)
                                @endif --}}
                            </div>

                            <div class="flex items-center space-x-4 text-sm text-gray-600">
                                <span><i class="far fa-clock mr-1"></i>
                                    18 Décembre 2025</span>
                                <span><i class="fas fa-users mr-1"></i> 3
                                    passager(s)</span>
                                <span class="font-bold text-green-600"><i class="fas fa-money-bill mr-1"></i>
                                    3500 FCFA</span>
                            </div>

                            <div class="mt-3 bg-yellow-50 border-l-4 border-yellow-400 p-3">
                                <p class="text-sm text-yellow-800"><i
                                        class="fas fa-info-circle mr-2"></i><strong>Note:</strong>
                                    Soyez à l'heure</p>
                            </div>
                            {{-- @if ($booking->special_requests)
                            @endif --}}
                        </div>

                        <div class="ml-4 flex flex-col space-y-2">
                            <button onclick="startTrip(1)"
                                class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition text-sm font-semibold">
                                <i class="fas fa-play mr-1"></i> Démarrer
                            </button>
                            {{-- @if ($booking->status === 'confirmed')
                            @endif --}}

                            <form action="" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm font-semibold">
                                    <i class="fas fa-check mr-1"></i> Terminer
                                </button>
                            </form>
                            {{-- @if ($booking->status === 'in_progress')
                            @endif --}}

                            <button onclick="cancelTrip(1)"
                                class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm font-semibold">
                                <i class="fas fa-times mr-1"></i> Annuler
                            </button>
                            {{-- @if ($booking->canBeCancelled())
                            @endif --}}
                        </div>
                    </div>
                </div>
                {{-- @foreach ($myBookings as $booking)
                @endforeach --}}
            </div>
            <div class="text-center py-12">
                <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-600 text-lg">Aucune course active</p>
                <p class="text-gray-500 text-sm">Consultez les réservations disponibles pour accepter
                    une course</p>
            </div>
            {{-- @if ($myBookings->count() > 0)
            @else
            @endif --}}
        </div>
    </div>

    <!-- Réservations Disponibles -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-800">Réservations Disponibles</h3>
            <button onclick="location.reload()" class="text-green-600 hover:text-green-700">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
        </div>
        <div class="p-6">
            <div class="grid md:grid-cols-2 gap-4">
                <div class="border-2 border-gray-200 rounded-lg p-4 hover:border-green-500 transition">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-gray-600">00HT</span>
                        <span class="text-lg font-bold text-green-600">5000
                            FCFA</span>
                    </div>

                    <div class="mb-3">
                        <div class="flex items-center text-sm text-gray-700 mb-1">
                            <i class="fas fa-map-marker-alt text-green-500 mr-2"></i>
                            <span class="font-semibold">Cotonou</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-700">
                            <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                            <span>Abomey-Calavi</span>
                        </div>
                        {{-- @if ($booking->dropoff_location)
                        @endif --}}
                    </div>

                    <div class="flex items-center justify-between text-xs text-gray-600 mb-3">
                        <span><i class="far fa-clock mr-1"></i>
                            21 Décembre 2025</span>
                        <span><i class="fas fa-users mr-1"></i> 2
                            pers.</span>
                    </div>

                    <form action="" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                            <i class="fas fa-check-circle mr-2"></i> Accepter cette course
                        </button>
                    </form>
                </div>
                {{-- @foreach ($availableBookings as $booking)
                @endforeach --}}
            </div>
            <div class="text-center py-12">
                <i class="fas fa-clipboard-list text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-600 text-lg">Aucune réservation disponible</p>
                <p class="text-gray-500 text-sm">De nouvelles courses apparaîtront bientôt</p>
            </div>
            {{-- @if ($availableBookings->count() > 0)
            @else
            @endif --}}
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
                    <button type="submit"
                        class="flex-1 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        Confirmer l'annulation
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
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

            function startTrip(bookingId) {
                if (confirm('Démarrer cette course ?')) {
                    fetch(`/driver/bookings/${bookingId}/start`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    }).then(() => location.reload());
                }
            }

            // Actualisation automatique toutes les 30 secondes
            setInterval(() => {
                location.reload();
            }, 30000);
        </script>
    @endpush
@endsection
