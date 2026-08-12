@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">Détail du contrat agent</h1>
                <p class="text-xs md:text-base text-gray-600 mt-1">
                    Contrat agent pour <strong>{{ $driver->name ?? 'N/A' }}</strong>
                    {{ $vehicle ? '· Véhicule ' . $vehicle->vehicle_number : '' }}
                </p>
            </div>
            <a href="{{ route('admin.driver-contracts.index') }}"
                class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-sm font-semibold transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-base font-bold text-gray-800">Résumé du contrat agent</h2>
                    <span
                        class="px-2 py-1 text-xs font-semibold rounded-full {{ $driverContract->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $driverContract->status === 'active' ? 'Actif' : ucfirst($driverContract->status) }}
                    </span>
                </div>
                <div class="px-6 py-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500 uppercase mb-2">Début</p>
                        <p class="text-lg font-bold text-gray-900">{{ formatDateFr($driverContract->start_date) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500 uppercase mb-2">Fin</p>
                        <p class="text-lg font-bold text-emerald-700">
                            {{ $driverContract->end_date ? formatDateFr($driverContract->end_date) : '—' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500 uppercase mb-2">Durée</p>
                        <p class="text-lg font-bold text-gray-900">{{ $driverContract->contract_months }} mois</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500 uppercase mb-2">Paiements</p>
                        <p class="text-lg font-bold text-gray-900">{{ $driverContract->payments->count() }} paiement(s)</p>
                    </div>
                </div>
                <div class="px-6 pb-6">
                    <div class="mb-4 flex items-center justify-between text-sm text-gray-500">
                        <span>Progression</span>
                        <span>{{ $progressPercent }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                        <div class="h-full rounded-full {{ $progressPercent >= 100 ? 'bg-orange-500' : 'bg-emerald-500' }}"
                            style="width: {{ min(100, $progressPercent) }}%"></div>
                    </div>
                </div>
                <div class="px-6 pb-6 grid grid-cols-2 gap-4 text-sm text-gray-600">
                    <div>
                        <p class="font-semibold text-gray-800 mb-1">Jours acquis</p>
                        <p>{{ number_format($accruedDays, 0, ',', ' ') }} jours</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 mb-1">Jours utilisés</p>
                        <p>{{ number_format($usedDays, 0, ',', ' ') }} jours</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 mb-1">Disponibles</p>
                        <p>{{ number_format(max(0, $availableDays), 0, ',', ' ') }} jours</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 mb-1">Surplus</p>
                        <p>{{ number_format($surplusDays, 0, ',', ' ') }} jours</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-base font-bold text-gray-800">Détails du contrat</h2>
                </div>
                <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                    <div>
                        <p class="text-xs text-gray-500 uppercase mb-2">Agent</p>
                        <p class="text-sm text-gray-800">{{ $driver->name ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-500">{{ $driver->phone ?? '—' }}</p>
                        <p class="text-sm text-gray-500">{{ $driver->email ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase mb-2">Véhicule</p>
                        <p class="text-sm text-gray-800">{{ $vehicle->vehicle_number ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-500">{{ vehiculeType($vehicle->vehicle_type) ?? '—' }}</p>
                        <p class="text-sm text-gray-500">{{ $vehicle->color ? ucfirst($vehicle->color) : '—' }}</p>
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <p class="text-xs text-gray-500 uppercase mb-2">Notes</p>
                        <p class="text-sm text-gray-600">
                            {{ $driverContract->end_notes ?? ($driverContract->vehicleContract?->notes ?? 'Aucune note.') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase mb-2">Raison de fin</p>
                        <p class="text-sm text-gray-700">
                            {{ $driverContract->end_reason ? ucfirst(str_replace('_', ' ', $driverContract->end_reason)) : '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase mb-2">Contrat véhicule</p>
                        <p class="text-sm text-gray-700">
                            {{ $driverContract->vehicleContract?->status ? ucfirst($driverContract->vehicleContract->status) : '—' }}
                        </p>
                        <p class="text-sm text-gray-700">
                            {{ $driverContract->vehicleContract?->contract_months ? $driverContract->vehicleContract->contract_months . ' mois' : '—' }}
                        </p>
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
                                            {{ \Carbon\Carbon::parse($paymentMonth->month . '-01')->format('m/Y') }}
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
                    <h2 class="text-base font-bold text-gray-800">Pauses liées au contrat</h2>
                </div>
                <div class="px-6 py-5">
                    @if ($driverContract->vehiclePauses->count() > 0)
                        <div class="space-y-3">
                            @foreach ($driverContract->vehiclePauses->sortByDesc('start_date') as $pause)
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
                    <h3 class="text-base font-bold text-gray-800">Agent</h3>
                </div>
                <div class="px-6 py-5 text-sm text-gray-700 space-y-3">
                    @if ($driver)
                        <p class="font-semibold text-gray-900">{{ $driver->name }}</p>
                        <p>Téléphone : {{ $driver->phone ?? '—' }}</p>
                        <p>Email : {{ $driver->email ?? '—' }}</p>
                        <p>Statut : {{ $driverContract->driver?->user?->is_active ? 'Actif' : 'Inactif' }}</p>
                    @else
                        <p class="text-gray-500">Aucun agent associé.</p>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-base font-bold text-gray-800">Véhicule</h3>
                </div>
                <div class="px-6 py-5 space-y-3 text-sm text-gray-700">
                    @if ($vehicle)
                        <p class="font-semibold text-gray-900">{{ $vehicle->vehicle_number }}</p>
                        <p>Type : {{ vehiculeType($vehicle->vehicle_type) ?? 'N/A' }}</p>
                        <p>Statut : {{ $vehicle->is_active ? 'Actif' : 'Inactif' }}</p>
                        <p>Notes : {{ $vehicle->notes ?? '—' }}</p>
                    @else
                        <p class="text-gray-500">Aucun véhicule associé.</p>
                    @endif
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

        </div>

    </div>

@endsection
