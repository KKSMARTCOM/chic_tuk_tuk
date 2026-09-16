@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Détails du Tarif</h1>
                <p class="text-gray-600">{{ $pricing->fromZone->name }} → {{ $pricing->toZone->name }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.pricing.edit', $pricing) }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-edit mr-2"></i> Modifier
                </a>
                <a href="{{ route('admin.pricing.index') }}"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Pricing Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 flex items-center justify-center rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-dollar-sign text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Prix de base</p>
                    <p class="text-2xl font-bold text-gray-900">€{{ number_format($pricing->base_price, 2, ',', ' ') }}</p>
                </div>
            </div>
        </div>

        <!-- Price per KM Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 flex items-center justify-center rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-road text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Prix par km</p>
                    <p class="text-2xl font-bold text-gray-900">€{{ number_format($pricing->price_per_km, 2, ',', ' ') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Duration Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 flex items-center justify-center rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Durée estimée</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $pricing->estimated_duration }} min</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Route Information -->
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Informations du trajet</h3>
        </div>
        <div class="px-6 py-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Zone de départ</label>
                    <div class="flex items-center">
                        <span
                            class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            {{ $pricing->fromZone->name }}
                        </span>
                        <span class="text-gray-400 text-xs ml-2 font-mono">{{ $pricing->from_zone_id }}</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Zone de destination</label>
                    <div class="flex items-center">
                        <span
                            class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            {{ $pricing->toZone->name }}
                        </span>
                        <span class="text-gray-400 text-xs ml-2 font-mono">{{ $pricing->to_zone_id }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Meta Information -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Informations système</h3>
        </div>
        <div class="px-6 py-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                <div>
                    <label class="block font-medium text-gray-700 mb-1">ID du tarif</label>
                    <p class="font-mono text-gray-600 break-all">{{ $pricing->id }}</p>
                </div>
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Créé le</label>
                    <p class="text-gray-600">{{ $pricing->created_at->format('d/m/Y à H:i:s') }}</p>
                </div>
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Modifié le</label>
                    <p class="text-gray-600">{{ $pricing->updated_at->format('d/m/Y à H:i:s') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
