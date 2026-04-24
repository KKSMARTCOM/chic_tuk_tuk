@extends('layouts.app')

@section('content')
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold mb-1">Réservations acceptées</p>
                    <p class="text-4xl font-bold">{{ $stats['confirmed_trips'] }}</p>
                    <p class="text-gray-500 text-xs">réservation(s)</p>
                </div>
                <div class="bg-blue-200 rounded-full w-16 h-16 flex items-center justify-center">
                    <i class="fas fa-car text-3xl text-blue-500"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-green-600">
                <i class="fas fa-arrow-up"></i> +12% ce mois
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

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold mb-1">Courses terminées</p>
                    <p class="text-4xl font-bold">{{ $stats['completed_trips'] }}</p>
                    <p class="text-gray-500 text-xs">course(s)</p>
                </div>
                <div class="bg-green-200 rounded-full w-16 h-16 flex items-center justify-center">
                    <i class="fas fa-check text-3xl text-green-500"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold mb-1">Courses annulées</p>
                    <p class="text-4xl font-bold">{{ $stats['cancelled_trips'] }}</p>
                    <p class="text-gray-500 text-xs">course(s)</p>
                </div>
                <div class="bg-red-200 w-16 h-16 flex items-center justify-center rounded-full">
                    <i class="fas fa-x text-3xl text-red-500"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold mb-1">Gains</p>
                    <p class="text-4xl font-bold mb-1">{{ number_format($stats['total_earnings'], 0, '', '') }}</p>
                    <p class="text-gray-500 text-xs">FCFA</p>
                </div>
                <div class="bg-yellow-200 rounded-full w-16 h-16 flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-3xl text-yellow-500"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-green-600">
                <i class="fas fa-arrow-up"></i> {{ '+' . $stats['earnings_today'] . ' FCFA aujourd\'hui' }}
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold mb-1">Commissions</p>
                    <p class="text-4xl font-bold mb-1">{{ number_format($stats['total_commission'], 0, '', '') }}</p>
                    <p class="text-gray-500 text-xs">FCFA</p>
                </div>
                <div class="bg-blue-200 rounded-full w-16 h-16 flex items-center justify-center">
                    <i class="fas fa-percent text-3xl text-blue-500"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-green-600">
                <i class="fas fa-arrow-up"></i> {{ '+' . $stats['commission_today'] . ' FCFA aujourd\'hui' }}
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold mb-1">Temps de courses</p>
                    <p class="text-4xl font-bold">{{ $stats['total_duration_minutes'] }}</p>
                    <p class="text-gray-500 text-xs">minutes</p>
                </div>
                <div class="bg-orange-200 rounded-full w-16 h-16 flex items-center justify-center">
                    <i class="fas fa-clock text-3xl text-orange-500"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings Table -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-gray-800">Dernières réservations acceptées</h2>
                <a href="{{ route('driver.bookings.accepting') }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200">
                    Voir plus
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Départ
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Destination</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($stats['recent_bookings_accepting'] as $booking)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatDateTimeFr($booking->pickup_date_time) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ Str::limit($booking->from_location, 8, '...') ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ Str::limit($booking->to_location, 8, '...') ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <a href="{{ route('driver.bookings.accepting') }}" type="submit"
                                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition duration-200">
                                        Voir
                                    </a>
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

        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-gray-800">Dernières réservations disponibles</h2>
                <a href="{{ route('driver.bookings.available') }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200">
                    Voir plus
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Départ
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Destination</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($stats['recent_bookings'] as $booking)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatDateTimeFr($booking->pickup_date_time) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ Str::limit($booking->from_location, 8, '...') ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ Str::limit($booking->to_location, 8, '...') ?? 'N/A' }}
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
                                    Aucune réservation récente trouvée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
