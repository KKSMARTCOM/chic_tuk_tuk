@extends('layouts.app')

@section('content')
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-semibold mb-1">Réservations acceptées</p>
                    <p class="text-4xl font-bold">{{ $stats['confirmed_trips'] }}</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full w-16 h-16 flex items-center justify-center">
                    <i class="fas fa-car text-3xl"></i>
                </div>
            </div>
        </div>

        {{-- <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
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
        </div> --}}

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-semibold mb-1">Courses terminées</p>
                    <p class="text-4xl font-bold">{{ $stats['completed_trips'] }}</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full w-16 h-16 flex items-center justify-center">
                    <i class="fas fa-check text-3xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-semibold mb-1">Courses annulées</p>
                    <p class="text-4xl font-bold">{{ $stats['cancelled_trips'] }}</p>
                </div>
                <div class="bg-white bg-opacity-20 w-16 h-16 flex items-center justify-center rounded-full">
                    <i class="fas fa-x text-3xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm font-semibold mb-1">Gains Aujourd'hui</p>
                    <p class="text-4xl font-bold">{{ $stats['earnings_today'] }}</p>
                    <p class="text-green-100 text-xs">FCFA</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full w-16 h-16 flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-3xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-semibold mb-1">Gains Total</p>
                    <p class="text-4xl font-bold">{{ $stats['total_earnings'] }}</p>
                    <p class="text-green-100 text-xs">FCFA</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full w-16 h-16 flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-3xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-semibold mb-1">Temps de courses</p>
                    <p class="text-4xl font-bold">{{ $stats['total_duration_minutes'] }}</p>
                    <p class="text-green-100 text-xs">minutes</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full w-16 h-16 flex items-center justify-center">
                    <i class="fas fa-clock text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings Table -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Dernières réservations disponibles</h2>
            <a href="{{ route('driver.bookings.available') }}"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200">
                Voir Plus
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Départ
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Destination</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentBookings as $booking)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($booking->pickup_datetime)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $booking->fromZone->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $booking->toZone->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <form action="{{ route('driver.bookings.accept', $booking) }}" method="POST"
                                    class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition duration-200">
                                        Accepter
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                Aucune course récente trouvée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
