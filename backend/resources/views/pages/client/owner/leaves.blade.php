@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h1 class="text-lg md:text-2xl font-bold text-gray-800">
                Pauses Véhicule — {{ $vehicle->vehicle_number }}
            </h1>
            <a href="{{ route('owner.dashboard') }}"
                class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                <i class="fas fa-arrow-left mr-2"></i> Retour
            </a>
        </div>
    </div>

    @if ($contract)
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Cumul sur le contrat véhicule</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="bg-indigo-50 p-4 rounded-lg">
                    <p class="text-xs text-indigo-600 font-semibold uppercase">Jours de pause pris</p>
                    <p class="text-2xl font-bold text-indigo-900">{{ $contract->total_pause_days_taken }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-xs text-gray-600 font-semibold uppercase">Jours total du contrat</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $contract->total_contract_days }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <p class="text-xs text-green-600 font-semibold uppercase">Jours restants</p>
                    <p class="text-2xl font-bold text-green-900">{{ $contract->remaining_contract_days }}</p>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-indigo-600 h-3 rounded-full" style="width: {{ $contract->pause_usage_percentage }}%"></div>
            </div>
            {{-- <p class="text-xs text-gray-500 mt-2">
                <i class="fas fa-info-circle mr-1"></i>
                Les jours de pause sont comptés en jours ouvrés uniquement (hors week-ends).
            </p> --}}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Historique des pauses</h2>

        @if ($vehicle->pauses->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Statut</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Début</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Fin</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Motif</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Durée</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($vehicle->pauses as $pause)
                            <tr>
                                <td class="px-4 py-2 text-sm">
                                    @if (is_null($pause->end_date))
                                        <span
                                            class="bg-orange-100 text-orange-800 px-2 py-1 rounded-full text-xs font-semibold">En
                                            cours</span>
                                    @else
                                        <span
                                            class="bg-gray-100 text-gray-700 px-2 py-1 rounded-full text-xs font-semibold">Terminée</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ formatDateFr($pause->start_date) }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">
                                    {{ $pause->end_date ? formatDateFr($pause->end_date) : '—' }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700">
                                    {{ $pause->reason_notes ?? ucfirst(str_replace('_', ' ', $pause->reason_type)) }}
                                    @if ($pause->is_auto)
                                        <span
                                            class="text-xs bg-gray-200 text-gray-600 px-1.5 py-0.5 rounded ml-1">Auto</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700">
                                    {{ $pause->end_date ? $pause->start_date->diffInDays($pause->end_date) + 1 . ' j' : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-sm">Aucune pause enregistrée pour ce véhicule.</p>
        @endif
    </div>
@endsection
