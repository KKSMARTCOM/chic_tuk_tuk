@extends('layouts.app')

@section('content')
    <!-- Réservations Disponibles -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-800">Réservations Disponibles</h3>
            <button onclick="location.reload()" class="text-green-600 hover:text-green-700">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
        </div>
        <div class="p-6">
            @if ($bookings->count() > 0)
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($bookings as $booking)
                        <div class="border-2 border-gray-200 rounded-lg p-4 hover:border-green-500 transition">
                            <div class="flex items-center justify-between mb-3">
                                <span
                                    class="text-xs font-semibold text-gray-600">{{ formatTimeFr($booking->pickup_datetime) }}</span>
                                {{-- <span class="text-lg font-bold text-green-600">{{ $booking->base_price }}
                                    FCFA</span> --}}
                            </div>

                            <div class="mb-3">
                                <div class="flex items-center text-sm text-gray-700 mb-1">
                                    <i class="fas fa-map-marker-alt text-green-500 mr-2"></i>
                                    <span class="font-semibold">{{ $booking->fromZone->name }}</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-700">
                                    <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                                    <span>{{ $booking->toZone->name }}</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-xs text-gray-600 mb-3">
                                <span><i class="far fa-clock mr-1"></i>
                                    {{ formatDateFr($booking->pickup_datetime) }}</span>
                                {{-- <span><i class="fas fa-users mr-1"></i> {{ $booking->passengers }}
                                    pers.</span> --}}
                            </div>

                            <form action="{{ route('driver.bookings.accept', $booking->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                                    <i class="fas fa-check-circle mr-2"></i> Accepter cette course
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-clipboard-list text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600 text-lg">Aucune réservation disponible</p>
                    <p class="text-gray-500 text-sm">De nouvelles courses apparaîtront bientôt</p>
                </div>
            @endif
        </div>
    </div>
@endsection
