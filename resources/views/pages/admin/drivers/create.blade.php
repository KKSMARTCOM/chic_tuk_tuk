@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Ajouter un Conducteur</h1>
                <p class="text-gray-600">Créez un nouveau compte conducteur</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.drivers.index') }}"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <!-- Formulaire d'ajout -->
    <form action="{{ route('admin.drivers.store') }}" method="POST" class="bg-white rounded-lg shadow-md">
        @csrf

        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Informations du Conducteur</h3>
        </div>

        <div class="px-6 py-6 space-y-6">
            <!-- Informations personnelles -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nom complet <span
                            class="text-red-600">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('name') border-red-500 @enderror"
                        placeholder="Entrez le nom complet">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('email') border-red-500 @enderror"
                        placeholder="email@example.com">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone <span
                            class="text-red-600">*</span></label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('phone') border-red-500 @enderror"
                        placeholder="+229 XX XX XX XX XX">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe <span
                            class="text-red-600">*</span></label>
                    <input type="password" name="password" id="password"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('password') border-red-500 @enderror"
                        placeholder="Entrez un mot de passe sécurisé">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="adresse" class="block text-sm font-medium text-gray-700">Adresse</label>
                    <input type="text" name="adresse" id="adresse" value="{{ old('adresse') }}"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('adresse') border-red-500 @enderror"
                        placeholder="Adresse complète">
                    @error('adresse')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Informations du Contrat -->
            <div class="border-t border-gray-200 pt-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Informations du Contrat</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="agent_code" class="block text-sm font-medium text-gray-700">Code Agent</label>
                        <input type="text" name="agent_code" id="agent_code" value="{{ old('agent_code') }}"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('agent_code') border-red-500 @enderror"
                            placeholder="Code agent">
                        @error('agent_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="agent_id" class="block text-sm font-medium text-gray-700">ID Agent</label>
                        <input type="text" name="agent_id" id="agent_id" value="{{ old('agent_id') }}"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('agent_id') border-red-500 @enderror"
                            placeholder="ID agent">
                        @error('agent_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="contract_type" class="block text-sm font-medium text-gray-700">Type de Contrat</label>
                        <select name="contract_type" id="contract_type"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('vehicle_type') border-red-500 @enderror">
                            <option value="">-- Sélectionnez un type --</option>
                            <option value="24" {{ old('contract_type') == '24' ? 'selected' : '' }}>24 Mois</option>
                            <option value="30" {{ old('contract_type') == '30' ? 'selected' : '' }}>30 Mois</option>
                            <option value="36" {{ old('contract_type') == '36' ? 'selected' : '' }}>36 Mois</option>
                        </select>
                        @error('contract_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Date de Début</label>
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('start_date') border-red-500 @enderror">
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tricycle_owner" class="block text-sm font-medium text-gray-700">Nom complet du
                            propriétaire</label>
                        <input type="text" name="tricycle_owner" id="tricycle_owner"
                            value="{{ old('tricycle_owner') }}"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('tricycle_owner') border-red-500 @enderror">
                        @error('tricycle_owner')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="owner_phone" class="block text-sm font-medium text-gray-700">Numéro du
                            propriétaire</label>
                        <input type="phone" name="owner_phone" id="owner_phone" value="{{ old('owner_phone') }}"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('owner_phone') border-red-500 @enderror"
                            placeholder="+229 XX XX XX XX XX">
                        @error('owner_phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Informations du véhicule -->
            <div class="border-t border-gray-200 pt-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Informations du Véhicule</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="license_number" class="block text-sm font-medium text-gray-700">Numéro de permis <span
                                class="text-red-600">*</span></label>
                        <input type="text" name="license_number" id="license_number"
                            value="{{ old('license_number') }}"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('license_number') border-red-500 @enderror"
                            placeholder="Numéro du permis">
                        @error('license_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="vehicle_number" class="block text-sm font-medium text-gray-700">Numéro du véhicule
                            <span class="text-red-600">*</span></label>
                        <input type="text" name="vehicle_number" id="vehicle_number"
                            value="{{ old('vehicle_number') }}"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('vehicle_number') border-red-500 @enderror"
                            placeholder="Numéro d'immatriculation">
                        @error('vehicle_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="vehicle_type" class="block text-sm font-medium text-gray-700">Type de véhicule <span
                                class="text-red-600">*</span></label>
                        <select name="vehicle_type" id="vehicle_type"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('vehicle_type') border-red-500 @enderror">
                            <option value="">-- Sélectionnez un type --</option>
                            <option value="moto" {{ old('vehicle_type') == 'moto' ? 'selected' : '' }}>Moto</option>
                            <option value="tricycle" {{ old('vehicle_type') == 'tricycle' ? 'selected' : '' }}>Tricycle
                            </option>
                            <option value="car" {{ old('vehicle_type') == 'car' ? 'selected' : '' }}>Voiture</option>
                        </select>
                        @error('vehicle_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <p class="text-sm text-blue-800">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Note :</strong> Ce conducteur sera créé avec le statut <strong>Actif</strong> et la
                disponibilité <strong>Disponible</strong> par défaut. Vous pourrez modifier ces paramètres après la
                création.
            </p>
        </div>

        <!-- Boutons -->
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('admin.drivers.index') }}"
                class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                <i class="fas fa-times mr-2"></i>Annuler
            </a>
            <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-check mr-2"></i> Créer le Conducteur
            </button>
        </div>
    </form>
@endsection
