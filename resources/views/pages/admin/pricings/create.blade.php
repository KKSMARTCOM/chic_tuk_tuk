@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Ajouter un Tarif</h1>
                <p class="text-gray-600">Créez un nouveau tarif pour un trajet</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.pricing.index') }}"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.pricing.store') }}" method="POST" class="bg-white rounded-lg shadow-md">
        @csrf

        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Informations du Tarif</h3>
        </div>

        <div class="px-6 py-6 space-y-6">
            <!-- Zones Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="from_zone_id" class="block text-sm font-medium text-gray-700 mb-2">Zone de départ <span
                            class="text-red-600">*</span></label>
                    <select name="from_zone_id" id="from_zone_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('from_zone_id') border-red-500 @enderror">
                        <option value="">-- Sélectionnez une zone --</option>
                        @foreach ($zones as $zone)
                            <option value="{{ $zone->id }}" {{ old('from_zone_id') === $zone->id ? 'selected' : '' }}>
                                {{ $zone->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('from_zone_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="to_zone_id" class="block text-sm font-medium text-gray-700 mb-2">Zone de destination <span
                            class="text-red-600">*</span></label>
                    <select name="to_zone_id" id="to_zone_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('to_zone_id') border-red-500 @enderror">
                        <option value="">-- Sélectionnez une zone --</option>
                        @foreach ($zones as $zone)
                            <option value="{{ $zone->id }}" {{ old('to_zone_id') === $zone->id ? 'selected' : '' }}>
                                {{ $zone->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('to_zone_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Pricing Information -->
            <div class="border-t border-gray-200 pt-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Tarification</h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="base_price" class="block text-sm font-medium text-gray-700 mb-2">Prix de base (FCFA)
                            <span class="text-red-600">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">F</span>
                            <input type="number" name="base_price" id="base_price" step="0.01" min="0"
                                value="{{ old('base_price') }}" placeholder="0.00"
                                class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('base_price') border-red-500 @enderror">
                        </div>
                        @error('base_price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="price_per_km" class="block text-sm font-medium text-gray-700 mb-2">Prix par km (FCFA)
                            {{-- <span class="text-red-600">*</span> --}}</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">F</span>
                            <input type="number" name="price_per_km" id="price_per_km" step="0.01" min="0"
                                value="{{ old('price_per_km') }}" placeholder="0.00"
                                class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('price_per_km') border-red-500 @enderror">
                        </div>
                        @error('price_per_km')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="estimated_duration" class="block text-sm font-medium text-gray-700 mb-2">Durée estimée
                            (minutes) {{-- <span class="text-red-600">*</span> --}}</label>
                        <div class="relative">
                            <input type="number" name="estimated_duration" id="estimated_duration" min="1"
                                value="{{ old('estimated_duration') }}" placeholder="Durée en minutes"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('estimated_duration') border-red-500 @enderror">
                            <span class="absolute right-3 top-2 text-gray-500">min</span>
                        </div>
                        @error('estimated_duration')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    Le prix total du trajet sera calculé comme : <strong>Prix de base + (Prix par km × Nombre de
                        km)</strong>
                </p>
            </div>
        </div>

        <!-- Actions -->
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
            <a href="{{ route('admin.pricing.index') }}"
                class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition font-medium">
                <i class="fas fa-times mr-2"></i>Annuler
            </a>
            <button type="submit"
                class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition font-medium">
                <i class="fas fa-check mr-2"></i>Ajouter le Tarif
            </button>
        </div>
    </form>
@endsection
