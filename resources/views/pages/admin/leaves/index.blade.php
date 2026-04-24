@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Gestion des Congés</h1>
                    <p class="text-gray-600">Gérez les congés de vos agents</p>
                </div>
                <a href="{{ route('admin.leave.requests.index') }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Demandes en attente
                    @if (collect($drivers)->sum('pending_requests') > 0)
                        <span class="ml-2 bg-red-500 text-white px-2 py-1 rounded-full text-sm">
                            {{ collect($drivers)->sum('pending_requests') }}
                        </span>
                    @endif
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            @if ($drivers && $drivers->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Conducteur</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Durée contrat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jours/Mois</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Utilisés</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Restants</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    En attente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($drivers as $driver)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $driver['name'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $driver['contract_type'] ?? 'N/A' }} mois
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $driver['leave_days_per_month'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $driver['total_leave_days'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $driver['leave_days_used'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm font-semibold {{ $driver['remaining_leave_days'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $driver['remaining_leave_days'] }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($driver['pending_requests'] > 0)
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                                {{ $driver['pending_requests'] }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.leaves.show', $driver['id']) }}"
                                            class="text-indigo-600 hover:text-indigo-900">Voir détails</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $drivers->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                </div>
            @endif
        </div>
    </div>
@endsection
