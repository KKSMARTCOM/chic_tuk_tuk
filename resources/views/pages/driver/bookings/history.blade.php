@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-lg shadow-md">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-800">Historique des courses</h3>
        </div>

        <!-- Content -->
        <div class="p-6">
            <form method="GET" action="{{ route('bookings.histories') }}" class="mb-6 flex gap-2">
                <input type="text" name="search" value="{{ request()->get('search') }}"
                    placeholder="Rechercher numéro, téléphone, zone..." class="w-full px-3 py-2 border rounded-lg" />

                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">Rechercher</button>
            </form>
            @if ($bookings->count())
                <div class="space-y-4">
                    @foreach ($bookings as $booking)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow transition">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <!-- Status + Booking number -->
                                    <div class="flex items-center mb-3">
                                        <span
                                            class="px-3 py-1 text-xs font-semibold rounded-full
                                            {{ bookingStatusBadge($booking->status) }}">
                                            {{ bookingStatusLabel($booking->status) }}
                                        </span>

                                        <span class="ml-3 text-sm font-semibold text-gray-600">
                                            {{ $booking->booking_number }}
                                        </span>
                                    </div>

                                    @if (auth()->user() && auth()->user()->role === 'admin')
                                        <div class="text-sm text-gray-600 mt-1">
                                            <strong>Conducteur :</strong>
                                            @if ($booking->driver && $booking->driver->user)
                                                {{ $booking->driver->user->name }}
                                            @else
                                                Non assigné
                                            @endif

                                            @if (isset($booking->remaining_days) && $booking->remaining_days > 0)
                                                <span class="ml-3">• <strong>Jours restants :</strong>
                                                    {{ $booking->remaining_days }}</span>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Zones -->
                                    <div class="bg-gray-50 rounded-lg p-3 mb-3">
                                        <div class="flex items-start mb-2">
                                            <i class="fas fa-circle text-green-500 text-xs mt-1 mr-3"></i>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800">Départ</p>
                                                <p class="text-sm text-gray-600">
                                                    {{ $booking->fromZone->name }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-start">
                                            <i class="fas fa-circle text-red-500 text-xs mt-1 mr-3"></i>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800">Destination</p>
                                                <p class="text-sm text-gray-600">
                                                    {{ $booking->toZone->name }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Infos -->
                                    <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                                        <span>
                                            <i class="far fa-calendar mr-1"></i>
                                            {{ formatDateTimeFr($booking->pickup_datetime) }}
                                        </span>

                                        {{-- <span>
                                            <i class="fas fa-users mr-1"></i>
                                            {{ $booking->passengers }} passager(s)
                                        </span> --}}

                                        <span class="font-bold text-green-600">
                                            <i class="fas fa-money-bill mr-1"></i>
                                            {{ $booking->total_price ?? $booking->base_price }} FCFA
                                        </span>

                                        @if ($booking->started_at && $booking->completed_at)
                                            <span class="font-semibold text-blue-600">
                                                <i class="far fa-clock mr-1"></i>
                                                {{ $booking->duration }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Annulation -->
                                    @if ($booking->status === 'cancelled' && $booking->cancellation_reason)
                                        <div class="mt-3 bg-red-50 border-l-4 border-red-400 p-3">
                                            <p class="text-sm text-red-700">
                                                <strong>Raison :</strong> {{ $booking->cancellation_reason }}
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Actions -->
                                {{-- <div class="ml-4 flex flex-col space-y-2">
                                    <a href="#"
                                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-semibold text-center">
                                        <i class="fas fa-eye mr-1"></i> Détails
                                    </a>
                                </div> --}}
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $bookings->links('pagination::tailwind') }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-history text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600 text-lg">Aucune course dans l’historique</p>
                </div>
            @endif
        </div>
    </div>
@endsection
