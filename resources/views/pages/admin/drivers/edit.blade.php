@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Modifier le Conducteur</h1>
                <p class="text-gray-600">{{ $driver->name }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.drivers.show', $driver) }}"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <!-- Formulaire de modification -->
    <form action="{{ route('admin.drivers.update', $driver) }}" method="POST" class="bg-white rounded-lg shadow-md">
        @csrf
        @method('PUT')

        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Informations du Conducteur</h3>
        </div>

        <div class="px-6 py-6 space-y-6">
            <!-- Informations personnelles -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nom complet</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $driver->name) }}"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $driver->email) }}"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $driver->phone) }}"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="is_active" class="block text-sm font-medium text-gray-700">Statut du compte</label>
                    <select name="is_active" id="is_active"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        <option value="1" {{ old('is_active', $driver->is_active) ? 'selected' : '' }}>Actif
                        </option>
                        <option value="0" {{ old('is_active', !$driver->is_active) ? 'selected' : '' }}>Inactif
                        </option>
                    </select>
                    @error('is_active')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Informations du véhicule -->
            <div class="border-t border-gray-200 pt-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Informations du Véhicule</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="license_number" class="block text-sm font-medium text-gray-700">Numéro de
                            permis</label>
                        <input type="text" name="license_number" id="license_number"
                            value="{{ old('license_number', $driver->driver->license_number ?? '') }}"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        @error('license_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="vehicle_number" class="block text-sm font-medium text-gray-700">Numéro du
                            véhicule</label>
                        <input type="text" name="vehicle_number" id="vehicle_number"
                            value="{{ old('vehicle_number', $driver->driver->vehicle_number ?? '') }}"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        @error('vehicle_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="vehicle_type" class="block text-sm font-medium text-gray-700">Type de
                            véhicule</label>
                        <select name="vehicle_type" id="vehicle_type"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Sélectionnez un type</option>
                            <option value="standard"
                                {{ old('vehicle_type', $driver->driver->vehicle_type ?? '') == 'moto' ? 'selected' : '' }}>
                                Moto</option>
                            <option value="premium"
                                {{ old('vehicle_type', $driver->driver->vehicle_type ?? '') == 'tricycle' ? 'selected' : '' }}>
                                Tricycle</option>
                            <option value="van"
                                {{ old('vehicle_type', $driver->driver->vehicle_type ?? '') == 'car' ? 'selected' : '' }}>
                                Voiture</option>
                        </select>
                        @error('vehicle_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="is_available" class="block text-sm font-medium text-gray-700">Disponibilité</label>
                        <select name="is_available" id="is_available"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            <option value="1"
                                {{ old('is_available', $driver->driver->is_available ?? true) ? 'selected' : '' }}>
                                Disponible</option>
                            <option value="0"
                                {{ old('is_available', !($driver->driver->is_available ?? true)) ? 'selected' : '' }}>
                                Indisponible</option>
                        </select>
                        @error('is_available')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Boutons -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.drivers.show', $driver) }}"
                    class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                    Annuler
                </a>
                <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-save mr-2"></i> Enregistrer les modifications
                </button>
            </div>
        </div>
    </form>
@endsection
