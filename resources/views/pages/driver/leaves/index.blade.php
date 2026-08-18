@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h1 class="text-lg md:text-xl font-bold text-gray-800">Mes Pauses</h1>

                <a href="{{ route('driver.leaves.create') }}"
                    class="bg-blue-600 text-white text-sm md:text-base px-4 py-2 rounded-lg hover:bg-blue-700 font-medium">
                    + Demander une Pause
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-blue-50 rounded-lg shadow p-6 border-l-4 border-blue-500">
                <p class="text-xs md:text-sm text-blue-600 font-semibold uppercase">Jours par mois</p>
                <p class="text-3xl font-bold text-blue-900 mt-2">{{ $leaveInfo['leave_days_per_month'] }}</p>
            </div>
            <div class="bg-purple-50 rounded-lg shadow p-6 border-l-4 border-purple-500">
                <p class="text-xs md:text-sm text-purple-600 font-semibold uppercase">Total du contrat</p>
                <p class="text-3xl font-bold text-purple-900 mt-2">{{ $leaveInfo['total_leave_days'] }}</p>
            </div>
            <div class="bg-orange-50 rounded-lg shadow p-6 border-l-4 border-orange-500">
                <p class="text-xs md:text-sm text-orange-600 font-semibold uppercase">Jours utilisés</p>
                <p class="text-3xl font-bold text-orange-900 mt-2">{{ $leaveInfo['leave_days_used'] }}</p>
            </div>
            <div class="bg-indigo-50 rounded-lg shadow p-6 border-l-4 border-indigo-500">
                <p class="text-xs md:text-sm text-indigo-600 font-semibold uppercase">Disponibles à date</p>
                <p class="text-3xl font-bold text-indigo-900 mt-2">{{ $leaveInfo['available_leave_days'] }}</p>
            </div>
            <div class="bg-green-50 rounded-lg shadow p-6 border-l-4 border-green-500">
                <p class="text-xs md:text-sm text-green-600 font-semibold uppercase">Jours restants</p>
                <p class="text-3xl font-bold text-green-900 mt-2">{{ $leaveInfo['remaining_leave_days'] }}</p>
            </div>
        </div>

        <!-- Pause en cours -->
        @if ($ongoingLeave)
            <div class="bg-orange-50 rounded-lg shadow-md p-6 mb-8 border-2 border-orange-200">
                <h2 class="text-lg font-semibold text-orange-900 mb-2">
                    <i class="fas fa-pause-circle mr-2"></i> Pause en cours
                </h2>
                <p class="text-gray-700">
                    Depuis le <strong>{{ formatDateFr($ongoingLeave->start_date) }}</strong>
                    — {{ $ongoingLeave->requested_days }} jour(s) demandé(s)
                </p>
                <p class="text-sm text-gray-600 mt-1">
                    Fin prévue le <strong>{{ formatDateFr($ongoingLeave->expected_end_date) }}</strong>
                    ({{ $ongoingLeave->expected_end_date->locale('fr')->translatedFormat('l') }})
                    @if ($ongoingLeave->is_overdue)
                        <span class="text-red-600 font-semibold ml-2">— dépasse la durée prévue</span>
                    @endif
                </p>
                <p class="text-xs text-orange-700 mt-2">
                    Votre pause restera active jusqu'à ce qu'un administrateur y mette fin.
                </p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Pending Requests -->
            @if ($pendingRequests->count() > 0)
                <div class="lg:col-span-1">
                    <div class="bg-yellow-50 rounded-lg shadow-md p-6 border-2 border-yellow-200">
                        <h2 class="text-lg font-semibold text-yellow-900 mb-4">
                            En attente
                            <span class="ml-2 bg-yellow-200 text-yellow-800 px-2 py-1 rounded-full text-sm">
                                {{ $pendingRequests->count() }}
                            </span>
                        </h2>
                        <div class="space-y-3">
                            @foreach ($pendingRequests as $request)
                                <div class="bg-white p-4 rounded border border-yellow-200">
                                    <p class="text-sm text-gray-600">Demande du {{ formatDateFr($request->created_at) }}
                                    </p>
                                    <p class="font-semibold text-gray-800">
                                        Du {{ formatDateFr($request->start_date) }} — {{ $request->requested_days }}
                                        jour(s)
                                        demandé(s)
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Fin prévue le {{ formatDateFr($request->expected_end_date) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- History -->
            @if ($history->count() > 0)
                <div
                    class="{{ $pendingRequests->count() > 0 && $rejectedRequests->count() > 0 ? 'lg:col-span-1' : 'lg:col-span-2' }}">
                    <div class="bg-green-50 rounded-lg shadow-md p-6 border-2 border-green-200">
                        <h2 class="text-lg font-semibold text-green-900 mb-4">
                            Historique
                            <span class="ml-2 bg-green-200 text-green-800 px-2 py-1 rounded-full text-sm">
                                {{ $history->count() }}
                            </span>
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-96 overflow-y-auto custom-scrollbar">
                            @foreach ($history as $request)
                                <div class="bg-white p-4 rounded border border-green-200">
                                    <p class="font-semibold text-green-800">
                                        {{ formatDateFr($request->start_date) }} → {{ formatDateFr($request->end_date) }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        {{ $request->effective_days }} jour(s) effectif(s)
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Rejected Requests -->
            @if ($rejectedRequests->count() > 0)
                <div
                    class="{{ $pendingRequests->count() > 0 || $history->count() > 0 ? 'lg:col-span-1' : 'lg:col-span-3' }}">
                    <div class="bg-red-50 rounded-lg shadow-md p-6 border-2 border-red-200">
                        <h2 class="text-lg font-semibold text-red-900 mb-4">
                            Rejetés
                            <span class="ml-2 bg-red-200 text-red-800 px-2 py-1 rounded-full text-sm">
                                {{ $rejectedRequests->count() }}
                            </span>
                        </h2>
                        <div class="space-y-3 max-h-96 overflow-y-auto custom-scrollbar">
                            @foreach ($rejectedRequests as $request)
                                <div class="bg-white p-4 rounded border border-red-200">
                                    <p class="text-sm text-gray-600">Demande du {{ formatDateFr($request->created_at) }}
                                    </p>
                                    <p class="font-semibold text-gray-800">
                                        Du {{ formatDateFr($request->start_date) }} — {{ $request->requested_days }}
                                        jour(s) demandé(s)
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Fin prévue le {{ formatDateFr($request->expected_end_date) }}
                                    </p>
                                    @if ($request->rejection_reason)
                                        <p class="text-xs text-red-700 bg-red-100 p-2 rounded mt-2">
                                            <strong>Motif:</strong> {{ $request->rejection_reason }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Empty State -->
        @if (!$ongoingLeave && $pendingRequests->isEmpty() && $history->isEmpty() && $rejectedRequests->isEmpty())
            <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-12 text-center">
                <p class="text-blue-800 text-lg font-semibold mb-2">Aucune demande de Pause</p>
                <p class="text-blue-600 text-sm mb-4">
                    Vous n'avez aucune demande de Pause en cours. Vous pouvez en créer une à tout moment.
                </p>
                <a href="{{ route('driver.leaves.create') }}"
                    class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-medium">
                    Faire une demande
                </a>
            </div>
        @endif
    </div>
@endsection
