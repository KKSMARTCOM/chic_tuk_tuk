@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h1 class="text-3xl font-bold text-gray-800">Mes Congés</h1>

                <a href="{{ route('driver.leaves.create') }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium">
                    + Demander un congé
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-blue-50 rounded-lg shadow p-6 border-l-4 border-blue-500">
                <p class="text-sm text-blue-600 font-semibold uppercase">Jours par mois</p>
                <p class="text-3xl font-bold text-blue-900 mt-2">{{ $leaveInfo['leave_days_per_month'] }}</p>
            </div>
            <div class="bg-purple-50 rounded-lg shadow p-6 border-l-4 border-purple-500">
                <p class="text-sm text-purple-600 font-semibold uppercase">Total du contrat</p>
                <p class="text-3xl font-bold text-purple-900 mt-2">{{ $leaveInfo['total_leave_days'] }}</p>
            </div>
            <div class="bg-orange-50 rounded-lg shadow p-6 border-l-4 border-orange-500">
                <p class="text-sm text-orange-600 font-semibold uppercase">Jours utilisés</p>
                <p class="text-3xl font-bold text-orange-900 mt-2">{{ $leaveInfo['leave_days_used'] }}</p>
            </div>
            <div class="bg-green-50 rounded-lg shadow p-6 border-l-4 border-green-500">
                <p class="text-sm text-green-600 font-semibold uppercase">Jours restants</p>
                <p class="text-3xl font-bold text-green-900 mt-2">{{ $leaveInfo['remaining_leave_days'] }}</p>
            </div>
        </div>


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
                                    <p class="text-xs text-gray-600 font-semibold mb-2">
                                        {{ formatDateTimeFr($request->created_at) }}
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($request->dates as $date)
                                            <span
                                                class="inline-block text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">
                                                {{ formatDateFr($date) }}
                                            </span>
                                        @endforeach
                                    </div>
                                    <p class="text-xs text-yellow-700 mt-2 font-semibold">
                                        ⏳ {{ count($request->dates) }} jour(s) en attente de validation
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Approved Requests -->
            @if ($approvedRequests->count() > 0)
                <div class="{{ $pendingRequests->count() > 0 ? 'lg:col-span-1' : 'lg:col-span-2' }}">
                    <div class="bg-green-50 rounded-lg shadow-md p-6 border-2 border-green-200">
                        <h2 class="text-lg font-semibold text-green-900 mb-4">
                            Approuvés ce mois
                            <span class="ml-2 bg-green-200 text-green-800 px-2 py-1 rounded-full text-sm">
                                {{ $approvedRequests->sum(fn($r) => count($r->dates)) }}
                            </span>
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach ($approvedRequests as $request)
                                @foreach ($request->dates as $date)
                                    <div class="bg-white p-4 rounded border border-green-200">
                                        <p class="font-semibold text-green-800">
                                            {{ formatDateFr($date) }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            {{ \Carbon\Carbon::parse($date)->locale('fr')->translatedFormat('l') }}
                                        </p>
                                        <p class="text-xs text-green-700 mt-2">
                                            ✓ Approuvé le {{ formatDateFr($request->updated_at) }}
                                        </p>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Rejected Requests -->
            @if ($rejectedRequests->count() > 0)
                <div
                    class="{{ $pendingRequests->count() > 0 || $approvedRequests->count() > 0 ? 'lg:col-span-1' : 'lg:col-span-3' }}">
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
                                    <p class="text-xs text-gray-600 font-semibold mb-2">
                                        {{ formatDateTimeFr($request->created_at) }}
                                    </p>
                                    <div class="flex flex-wrap gap-1 mb-2">
                                        @foreach ($request->dates as $date)
                                            <span class="inline-block text-xs bg-red-100 text-red-700 px-2 py-1 rounded">
                                                {{ formatDateFr($date) }}
                                            </span>
                                        @endforeach
                                    </div>
                                    @if ($request->rejection_reason)
                                        <p class="text-xs text-red-700 bg-red-100 p-2 rounded">
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
        @if ($pendingRequests->isEmpty() && $approvedRequests->isEmpty() && $rejectedRequests->isEmpty())
            <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-12 text-center">
                <p class="text-blue-800 text-lg font-semibold mb-2">Aucune demande de congé</p>
                <p class="text-blue-600 text-sm mb-4">
                    Vous n'avez aucune demande de congé en cours. Vous pouvez en créer une si vous avez des jours restants.
                </p>
                @if ($leaveInfo['remaining_leave_days'] > 0)
                    <a href="{{ route('driver.leaves.create') }}"
                        class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-medium">
                        Faire une demande
                    </a>
                @else
                    <p class="text-red-600 font-semibold text-sm">
                        Vous n'avez plus de jours de congé disponibles.
                    </p>
                @endif
            </div>
        @endif
    </div>
@endsection
