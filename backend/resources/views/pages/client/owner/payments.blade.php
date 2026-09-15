@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h1 class="text-lg md:text-2xl font-bold text-gray-800">
                Paiements Véhicule — {{ $vehicle->vehicle_number }}
            </h1>
            <a href="{{ route('owner.dashboard') }}"
                class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                <i class="fas fa-arrow-left mr-2"></i> Retour
            </a>
        </div>
    </div>

    @if ($monthlyRecap->isEmpty())
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-12 text-center text-gray-500">
            Aucun récapitulatif mensuel disponible pour le moment.
        </div>
    @else
        <div class="space-y-4">
            @foreach ($monthlyRecap as $recap)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2 capitalize">
                        {{ $recap['month']->locale('fr')->translatedFormat('F Y') }}
                        @if ($recap['is_current'])
                            <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs font-semibold">
                                En cours
                            </span>
                        @endif
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-3">
                        <div class="bg-emerald-50 p-4 rounded-lg">
                            <p class="text-xs text-emerald-600 font-semibold uppercase">Payé (validé)</p>
                            <p class="text-lg font-bold text-emerald-900">
                                {{ number_format($recap['validated_amount'], 0, ',', ' ') }} FCFA</p>
                        </div>
                        <div class="bg-amber-50 p-4 rounded-lg">
                            <p class="text-xs text-amber-600 font-semibold uppercase">En attente</p>
                            <p class="text-lg font-bold text-amber-900">
                                {{ number_format($recap['pending_amount'], 0, ',', ' ') }} FCFA</p>
                        </div>
                        <div class="bg-red-50 p-4 rounded-lg">
                            <p class="text-xs text-red-600 font-semibold uppercase">Charges prélevées</p>
                            <p class="text-lg font-bold text-red-900">
                                {{ number_format($recap['total_charges'], 0, ',', ' ') }} FCFA</p>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-lg">
                            @if ($recap['is_current'])
                                <p class="text-xs text-blue-600 font-semibold uppercase">Montant fixe à percevoir</p>
                            @else
                                <p class="text-xs text-blue-600 font-semibold uppercase">Montant fixe perçu</p>
                            @endif
                            <p class="text-lg font-bold text-blue-900">
                                {{ number_format($recap['fixed_amount'], 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-xs text-gray-600 font-semibold uppercase">Jours ouvrés travaillés</p>
                            <p class="text-lg font-bold text-gray-900">{{ $recap['worked_days'] }} j</p>
                        </div>
                        <div class="bg-indigo-50 p-4 rounded-lg">
                            <p class="text-xs text-indigo-600 font-semibold uppercase">Jours de pause agent</p>
                            <p class="text-lg font-bold text-indigo-900">{{ $recap['agent_leave_days'] }} j</p>
                        </div>
                        <div class="bg-orange-50 p-4 rounded-lg">
                            <p class="text-xs text-orange-600 font-semibold uppercase">Jours d'immobilisation</p>
                            <p class="text-lg font-bold text-orange-900">{{ $recap['immobilization_days'] }} j</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
