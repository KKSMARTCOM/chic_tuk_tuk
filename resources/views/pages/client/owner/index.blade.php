@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h1 class="text-lg md:text-2xl font-bold text-gray-800">Mes véhicules</h1>
            <p class="text-sm text-gray-600">Vue d'ensemble de vos véhicules et de leur contrat</p>
        </div>
    </div>

    @if ($vehiclesStats->isEmpty())
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-12 text-center text-gray-500">
            Vous n'avez aucun véhicule enregistré.
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($vehiclesStats as $stat)
                @php $contract = $stat->contract; @endphp
                <div class="bg-white rounded-lg shadow-md p-6">
                    <!-- En-tête véhicule -->
                    <div class="flex justify-between items-start mb-4">
                        <h2 class="text-lg font-bold text-gray-800">
                            {{ 'Véhicule - ' . $stat->vehicle->vehicle_number }}
                        </h2>
                        @if ($stat->active_pause)
                            <span class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-xs font-semibold">
                                <i class="fas fa-pause-circle mr-1"></i> En pause
                            </span>
                        @else
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                                <i class="fas fa-check-circle mr-1"></i> Actif
                            </span>
                        @endif
                    </div>

                    @if ($stat->active_pause)
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-4">
                            <p class="text-sm text-orange-700">
                                En pause depuis le {{ formatDateFr($stat->active_pause->start_date) }}
                                —
                                {{ $stat->active_pause->reason_notes ?? ucfirst(str_replace('_', ' ', $stat->active_pause->reason_type)) }}
                            </p>
                        </div>
                    @endif

                    @if ($contract)
                        <!-- Contrat véhicule -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <p class="text-xs text-blue-600 font-semibold uppercase">Type de contrat</p>
                                <p class="text-lg font-bold text-blue-900">{{ $contract->contract_months }} mois</p>
                            </div>
                            <div class="bg-indigo-50 p-4 rounded-lg">
                                <p class="text-xs text-indigo-600 font-semibold uppercase">Mois écoulés</p>
                                <p class="text-xl font-bold text-indigo-900">
                                    {{ $stat->months_elapsed }} / {{ $contract->contract_months }}
                                </p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-xs text-gray-600 font-semibold uppercase">Mois restants</p>
                                <p class="text-xl font-bold text-gray-900">{{ $stat->months_remaining }}</p>
                            </div>
                            {{-- <div class="bg-indigo-50 p-4 rounded-lg">
                                <p class="text-xs text-indigo-600 font-semibold uppercase">Date de début</p>
                                <p class="text-sm font-bold text-indigo-900">
                                    {{ $stat->start_date ? formatDateFr($stat->start_date) : 'N/A' }}
                                </p>
                            </div> --}}
                            {{-- <div class="bg-orange-50 p-4 rounded-lg">
                                <p class="text-xs text-orange-600 font-semibold uppercase">Fin prévue (ajustée)</p>
                                <p class="text-sm font-bold text-orange-900">
                                    {{ $stat->extended_end_date ? formatDateFr($stat->extended_end_date) : 'N/A' }}
                                </p>
                            </div> --}}
                            <div class="bg-green-50 p-4 rounded-lg">
                                <p class="text-xs text-green-600 font-semibold uppercase">Montant total</p>
                                <p class="text-lg font-bold text-green-900">
                                    {{ number_format($contract->total_amount, 0, ',', ' ') }} FCFA</p>
                            </div>
                            <div class="bg-orange-50 p-4 rounded-lg">
                                <p class="text-xs text-orange-600 font-semibold uppercase">Restant à percevoir</p>
                                <p class="text-lg font-bold text-orange-900">
                                    {{ number_format($stat->remaining, 0, ',', ' ') }} FCFA</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <p class="text-xs text-green-600 font-semibold uppercase">Montant journalier</p>
                                <p class="text-xl font-bold text-green-900">
                                    {{ number_format($stat->daily_net_amount, 0, ',', ' ') }} FCFA
                                </p>
                            </div>
                            {{-- <div class="bg-purple-50 p-4 rounded-lg">
                                <p class="text-xs text-purple-600 font-semibold uppercase">Progression</p>
                                <p class="text-lg font-bold text-purple-900">{{ $stat->progress }}%</p>
                            </div> --}}
                        </div>
                        <div class="mb-4">
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span>Progression du contrat</span>
                                <span>{{ $stat->progress }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-emerald-600 h-2 rounded-full" style="width: {{ $stat->progress }}%"></div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-4 grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Date de début</p>
                                <p class="text-gray-800">{{ formatDateFr($contract->start_date) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Date de fin prévue</p>
                                <p class="text-gray-800">
                                    {{ $stat->extended_end_date ? formatDateFr($stat->extended_end_date) : 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Charges mensuelles</p>
                                <p class="text-gray-800">{{ number_format($contract->total_charges, 0, ',', ' ') }} FCFA
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm mb-6">
                            <div>
                                <p class="text-gray-500">Internet illimité</p>
                                <p class="font-medium">{{ number_format($contract->unlimited_internet ?? 0, 0, ',', ' ') }}
                                    FCFA</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Spotify Premium</p>
                                <p class="font-medium">{{ number_format($contract->spotify_premium ?? 0, 0, ',', ' ') }}
                                    FCFA</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Rémunération manager</p>
                                <p class="font-medium">
                                    {{ number_format($contract->manager_remuneration ?? 0, 0, ',', ' ') }}
                                    FCFA</p>
                            </div>
                        </div>

                        <!-- Pauses (interprétation A — agrégé, sans identité d'agent) -->
                        <div class="border-t border-gray-200 pt-4 mb-6">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Pauses sur ce contrat</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                                <div class="bg-indigo-50 p-4 rounded-lg">
                                    <p class="text-xs text-indigo-600 font-semibold uppercase">Jours de pause pris</p>
                                    <p class="text-xl font-bold text-indigo-900">{{ $stat->total_pause_days_taken }}</p>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p class="text-xs text-gray-600 font-semibold uppercase">Jours total du contrat</p>
                                    <p class="text-xl font-bold text-gray-900">{{ $stat->total_contract_days }}</p>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <p class="text-xs text-green-600 font-semibold uppercase">Jours restants</p>
                                    <p class="text-xl font-bold text-green-900">{{ $stat->remaining_contract_days }}</p>
                                </div>
                            </div>
                            {{-- <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-indigo-600 h-2 rounded-full"
                                    style="width: {{ $stat->pause_usage_percentage }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Jours ouvrés uniquement (hors week-ends).
                            </p> --}}
                        </div>

                        <p class="text-xs text-gray-500 mb-6">
                            <i class="fas fa-info-circle mr-1"></i>
                            Date de fin initialement prévue :
                            {{ $stat->planned_end_date ? formatDateFr($stat->planned_end_date) : 'N/A' }}.
                            Elle est automatiquement décalée de {{ $stat->total_pause_days_taken }} jour(s) ouvré(s), cumul
                            des pauses prises sur ce véhicule.
                        </p>
                    @else
                        <p class="text-sm text-gray-400 mb-6">Aucun contrat actif pour ce véhicule.</p>
                    @endif

                    <!-- Boutons d'accès aux historiques -->
                    <div class="flex flex-col md:flex-row gap-3">
                        <a href="{{ route('owner.leaves.show', $stat->vehicle) }}"
                            class="flex-1 text-center bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium text-sm">
                            <i class="fas fa-calendar-alt mr-1"></i> Historique des pauses
                        </a>
                        <a href="{{ route('owner.payments.show', $stat->vehicle) }}"
                            class="flex-1 text-center bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 font-medium text-sm">
                            <i class="fas fa-money-bill mr-1"></i> Historique des paiements
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
