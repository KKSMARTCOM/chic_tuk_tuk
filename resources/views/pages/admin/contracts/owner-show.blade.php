@extends('layouts.app')

@section('content')

    @php
        $vehicle = $vehicleContract->vehicle;
        $owner = $vehicleContract->owner;
        $driverContracts = $vehicleContract->driverContracts;
        $activeDriverContract = $driverContracts->first(fn($contract) => $contract->status === 'active');
        $activeDriver = $activeDriverContract?->driver?->user;
        $totalPaid = $stats['total_paid'] ?? 0;
        $remaining = $stats['remaining'] ?? 0;
        $surplus = $stats['surplus'] ?? 0;
    @endphp

    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">Détail du contrat véhicule</h1>
                <p class="text-xs md:text-base text-gray-600 mt-1">
                    Contrat propriétaire-véhicule pour <strong>{{ $owner->name ?? 'N/A' }}</strong> · Véhicule
                    <strong>{{ $vehicle->vehicle_number ?? 'N/A' }}</strong>
                </p>
            </div>
            <a href="{{ route('admin.vehicle-contracts.index') }}"
                class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-sm font-semibold transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-base font-bold text-gray-800">Résumé du contrat</h2>
                    <span
                        class="px-2 py-1 text-xs font-semibold rounded-full {{ $vehicleContract->status === 'active' ? 'bg-green-100 text-green-700' : ($vehicleContract->status === 'completed' ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700') }}">
                        {{ match ($vehicleContract->status) {
                            'active' => 'Actif',
                            'completed' => 'Soldé',
                            'cancelled' => 'Annulé',
                            default => ucfirst($vehicleContract->status),
                        } }}
                    </span>
                </div>
                <div class="px-6 py-6 grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500 uppercase mb-2">Montant total</p>
                        <p class="text-lg font-bold text-gray-900">
                            {{ number_format($vehicleContract->total_amount, 0, ',', ' ') }} FCFA</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500 uppercase mb-2">Total payé</p>
                        <p class="text-lg font-bold text-emerald-700">{{ number_format($totalPaid, 0, ',', ' ') }} FCFA</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500 uppercase mb-2">Restant</p>
                        @if ($surplus > 0)
                            <p class="text-lg font-bold text-orange-600">+{{ number_format($surplus, 0, ',', ' ') }} FCFA
                            </p>
                        @else
                            <p class="text-lg font-bold text-red-600">{{ number_format($remaining, 0, ',', ' ') }} FCFA</p>
                        @endif
                    </div>
                </div>
                <div class="px-6 pb-6">
                    <div class="mb-4 flex items-center justify-between text-sm text-gray-500">
                        <span>Progression</span>
                        <span>{{ $stats['progress_percent'] ?? 0 }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                        <div class="h-full rounded-full {{ ($stats['progress_percent'] ?? 0) >= 100 ? 'bg-orange-500' : 'bg-emerald-500' }}"
                            style="width: {{ min(100, $stats['progress_percent'] ?? 0) }}%"></div>
                    </div>
                </div>
                <div class="px-6 pb-6 grid grid-cols-2 gap-4 text-sm text-gray-600">
                    <div>
                        <p class="font-semibold text-gray-800 mb-1">Durée contrat</p>
                        <p>{{ $vehicleContract->contract_months }} mois</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 mb-1">Début</p>
                        <p>{{ formatDateFr($vehicleContract->start_date) }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 mb-1">Fin</p>
                        <p>{{ $vehicleContract->end_date ? formatDateFr($vehicleContract->end_date) : '—' }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 mb-1">Paiements</p>
                        <p>{{ $vehicleContract->payments->count() }} paiement(s)</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-base font-bold text-gray-800">Détails du contrat</h2>
                </div>
                <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                    <div>
                        <p class="text-xs text-gray-500 uppercase mb-2">Notes</p>
                        <p class="text-sm text-gray-600">{{ $vehicleContract->notes ?? 'Aucune note.' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase mb-2">Charges</p>
                        <div class="space-y-2 text-sm text-gray-600">
                            <p>Internet illimité :
                                {{ number_format($vehicleContract->unlimited_internet ?? 0, 0, ',', ' ') }} FCFA</p>
                            <p>Spotify Premium : {{ number_format($vehicleContract->spotify_premium ?? 0, 0, ',', ' ') }}
                                FCFA</p>
                            <p>Rémunération manager :
                                {{ number_format($vehicleContract->manager_remuneration ?? 0, 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-base font-bold text-gray-800">Paiements par mois</h2>
                    <span class="text-sm text-gray-500">{{ $paymentsByMonth->count() }} mois</span>
                </div>
                <div class="px-6 py-5">
                    @if ($paymentsByMonth->count() > 0)
                        <div class="space-y-3">
                            @foreach ($paymentsByMonth as $paymentMonth)
                                <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">
                                            {{ \Carbon\Carbon::parse($paymentMonth->month)->format('m/Y') }}
                                        </p>
                                        <p class="text-xs text-gray-500">Total payé</p>
                                    </div>
                                    <p class="text-sm font-bold text-gray-900">
                                        {{ number_format($paymentMonth->total, 0, ',', ' ') }} FCFA</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Aucun paiement enregistré pour ce contrat.</p>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-base font-bold text-gray-800">Historique des agents associés</h2>
                </div>
                <div class="px-6 py-5">
                    @if ($driverContracts->count() > 0)
                        <div class="space-y-3">
                            @foreach ($driverContracts->sortByDesc('start_date') as $driverContract)
                                <div class="rounded-xl border border-gray-100 p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">
                                                {{ $driverContract->driver?->user?->name ?? 'Agent inconnu' }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ formatDateFr($driverContract->start_date) }} →
                                                {{ $driverContract->end_date ? formatDateFr($driverContract->end_date) : 'en cours' }}
                                            </p>
                                        </div>
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full {{ $driverContract->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ ucfirst($driverContract->status) }}
                                        </span>
                                    </div>
                                    <div class="mt-3 text-sm text-gray-600 grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div>
                                            <p class="text-xs text-gray-500">Durée</p>
                                            <p>{{ $driverContract->contract_months }} mois</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Paiements</p>
                                            <p>{{ $driverContract->payments->count() }} paiement(s)</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Observations</p>
                                            <p>{{ $driverContract->end_reason ?? '—' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Aucun agent associé à ce contrat.</p>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-base font-bold text-gray-800">Pauses liées au contrat</h2>
                </div>
                <div class="px-6 py-5">
                    @if ($vehicleContract->pauses->count() > 0)
                        <div class="space-y-3">
                            @foreach ($vehicleContract->pauses->sortByDesc('start_date') as $pause)
                                <div class="rounded-xl border border-gray-100 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">
                                                {{ $pause->reason_label ?? ucfirst($pause->reason_type) }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ formatDateFr($pause->start_date) }} →
                                                {{ $pause->end_date ? formatDateFr($pause->end_date) : 'en cours' }}
                                            </p>
                                        </div>
                                        <span
                                            class="text-xs text-gray-500">{{ $pause->is_auto ? 'Auto' : 'Manuel' }}</span>
                                    </div>
                                    @if ($pause->reason_notes)
                                        <p class="mt-3 text-sm text-gray-600">{{ $pause->reason_notes }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Aucune pause enregistrée pour ce contrat.</p>
                    @endif
                </div>
            </div>

        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-base font-bold text-gray-800">Véhicule</h3>
                </div>
                <div class="px-6 py-5 space-y-3 text-sm text-gray-700">
                    <p class="font-semibold text-gray-900">{{ $vehicle->vehicle_number ?? 'N/A' }}</p>
                    <p>Type : {{ vehiculeType($vehicle->vehicle_type) ?? 'N/A' }}</p>
                    <p>Statut : {{ $vehicle->is_active ? 'Actif' : 'Inactif' }}</p>
                    <p>Notes : {{ $vehicle->notes ?? '—' }}</p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-base font-bold text-gray-800">Propriétaire</h3>
                </div>
                <div class="px-6 py-5 text-sm text-gray-700">
                    @if ($owner)
                        <p class="font-semibold text-gray-900">{{ $owner->name }}</p>
                        <p>Téléphone : {{ $owner->phone ?? '—' }}</p>
                        <p>Email : {{ $owner->email ?? '—' }}</p>
                    @else
                        <p class="text-gray-500">Aucun propriétaire associé.</p>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-base font-bold text-gray-800">Agent actuel</h3>
                </div>
                <div class="px-6 py-5 text-sm text-gray-700">
                    @if ($activeDriver)
                        <p class="font-semibold text-gray-900">{{ $activeDriver->name }}</p>
                        <p>Téléphone : {{ $activeDriver->phone ?? '—' }}</p>
                        <p>Email : {{ $activeDriver->email ?? '—' }}</p>
                        <p>Contrat démarré le {{ formatDateFr($activeDriverContract->start_date) }}</p>
                    @else
                        <p class="text-gray-500">Aucun agent actif sur ce contrat.</p>
                    @endif
                </div>
            </div>
        </div>

    </div>

@endsection
