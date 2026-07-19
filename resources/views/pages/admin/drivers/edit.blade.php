@extends('layouts.app')

@php
    $activeContract = $driver->driver?->activeDriverContract?->load(['vehicle.owner', 'vehicleContract']);
    $currentVehicle = $activeContract?->vehicle;
    $currentOwner = $currentVehicle?->owner;
@endphp

@section('content')

    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">Modifier l'Agent</h1>
                <p class="text-sm md:text-base text-gray-600">{{ $driver->name }}</p>
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
    <form action="{{ route('admin.drivers.update', $driver) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- ── Informations personnelles ── --}}
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Informations personnelles</h3>
            </div>
            <div class="px-6 py-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nom complet <span
                            class="text-red-600">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $driver->name) }}" required
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $driver->email) }}"
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Téléphone <span
                            class="text-red-600">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone', $driver->phone) }}" required
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Statut du compte</label>
                    <select name="is_active"
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        <option value="1" {{ old('is_active', $driver->is_active) ? 'selected' : '' }}>Actif</option>
                        <option value="0" {{ !old('is_active', $driver->is_active) ? 'selected' : '' }}>Inactif
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Adresse</label>
                    <input type="text" name="adresse" value="{{ old('adresse', $driver->adresse) }}"
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Catégorie de permis <span
                            class="text-red-600">*</span></label>
                    <input type="text" name="license_number"
                        value="{{ old('license_number', $driver->driver?->license_number) }}" required
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('license_number') border-red-500 @enderror">
                    @error('license_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Disponibilité</label>
                    <select name="is_available"
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        <option value="1" {{ old('is_available', $driver->driver?->is_available) ? 'selected' : '' }}>
                            Disponible</option>
                        <option value="0"
                            {{ !old('is_available', $driver->driver?->is_available) ? 'selected' : '' }}>Indisponible
                        </option>
                    </select>
                </div>
            </div>
        </div>

        {{-- ── Contrat Agent ── --}}
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Contrat Agent</h3>
            </div>
            <div class="px-6 py-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Code Agent</label>
                    <input type="text" name="agent_code" value="{{ old('agent_code', $driver->driver?->agent_code) }}"
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">ID Agent</label>
                    <input type="text" name="agent_id" value="{{ old('agent_id', $driver->driver?->agent_id) }}"
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                </div>
                @if ($activeContract)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Durée du contrat actif</label>
                        <input type="text"
                            value="{{ $activeContract->contract_months }} mois — depuis {{ $activeContract->start_date->format('d/m/Y') }}"
                            disabled
                            class="mt-1 w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50 text-gray-500 cursor-not-allowed">
                        <p class="text-xs text-gray-400 mt-1">Pour modifier, terminez le contrat actif depuis la fiche de
                            l'agent.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Véhicule & Propriétaire ── --}}
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Véhicule & Propriétaire</h3>
            </div>
            <div class="px-6 py-6 space-y-5">

                {{-- Contrat actif affiché en lecture seule --}}
                @if ($activeContract && $currentVehicle)
                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                        <p class="text-xs font-bold text-emerald-700 uppercase tracking-wide mb-3">Contrat actif</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs text-gray-500">Véhicule</p>
                                <p class="font-semibold text-gray-800">{{ $currentVehicle->vehicle_number }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ ucfirst($currentVehicle->vehicle_type) }}{{ $currentVehicle->color ? ' · ' . $currentVehicle->color : '' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Propriétaire</p>
                                <p class="font-semibold text-gray-800">{{ $currentOwner?->name ?? '—' }}</p>
                                <p class="text-xs text-gray-500">{{ $currentOwner?->phone }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Contrat véhicule</p>
                                @if ($activeContract->vehicleContract)
                                    <p class="font-semibold text-gray-800">
                                        {{ number_format($activeContract->vehicleContract->total_amount, 0, ',', ' ') }}
                                        FCFA</p>
                                    <p class="text-xs text-gray-500">
                                        {{ number_format($activeContract->vehicleContract->monthly_payment, 0, ',', ' ') }}
                                        FCFA/mois</p>
                                @else
                                    <p class="text-xs text-gray-400 italic">Aucun contrat véhicule</p>
                                @endif
                            </div>
                        </div>
                        <p class="text-xs text-amber-600 bg-amber-50 rounded-lg px-3 py-2 mt-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            Pour changer de véhicule, terminez d'abord le contrat actif depuis la fiche de l'agent, puis
                            créez un nouveau contrat.
                        </p>
                    </div>
                @else
                    {{-- Pas de contrat actif → permettre d'en créer un --}}
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4">
                        <p class="text-sm text-amber-800 font-semibold">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Cet agent n'a pas de contrat véhicule actif.
                        </p>
                        <p class="text-xs text-amber-600 mt-1">Assignez-lui un véhicule et un propriétaire ci-dessous.</p>
                    </div>

                    {{-- Choix du mode --}}
                    <div class="grid grid-cols-2 gap-4">
                        <label id="edit-mode-existing-label"
                            class="edit-mode-card cursor-pointer rounded-xl border-2 border-[#286b41] bg-[#286b41]/10 p-4 flex items-center gap-3 transition">
                            <input type="radio" name="_owner_mode" value="existing" class="sr-only" checked>
                            <div class="w-9 h-9 rounded-full bg-[#286b41] flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-search text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Propriétaire existant</p>
                                <p class="text-xs text-gray-500">Sélectionner dans la liste</p>
                            </div>
                        </label>

                        <label id="edit-mode-new-label"
                            class="edit-mode-card cursor-pointer rounded-xl border-2 border-gray-200 bg-white p-4 flex items-center gap-3 transition">
                            <input type="radio" name="_owner_mode" value="new" class="sr-only">
                            <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-plus text-gray-400"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Nouveau propriétaire</p>
                                <p class="text-xs text-gray-500">Créer un nouveau compte</p>
                            </div>
                        </label>
                    </div>

                    {{-- Section propriétaire existant --}}
                    <div id="edit-section-existing" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Propriétaire <span
                                        class="text-red-600">*</span></label>
                                <select name="owner_id" id="edit_owner_id_select"
                                    onchange="onEditOwnerSelected(this.value)"
                                    class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('owner_id') border-red-500 @enderror">
                                    <option value="">— Sélectionnez un propriétaire —</option>
                                    @foreach ($owners as $owner)
                                        <option value="{{ $owner->id }}"
                                            {{ old('owner_id') == $owner->id ? 'selected' : '' }}>
                                            {{ $owner->name }} — {{ $owner->phone }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('owner_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div id="edit-vehicles-wrap" class="hidden">
                                <label class="block text-sm font-medium text-gray-700">Véhicule <span
                                        class="text-red-600">*</span></label>
                                <div class="flex gap-2 mt-1">
                                    <select name="vehicle_id" id="edit_vehicle_id_select"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('vehicle_id') border-red-500 @enderror">
                                        <option value="">— Sélectionnez d'abord un propriétaire —</option>
                                    </select>
                                    <button type="button" id="edit-btn-add-vehicle" onclick="toggleEditVehicleInline()"
                                        disabled
                                        class="px-3 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 text-sm font-semibold whitespace-nowrap disabled:opacity-40 disabled:cursor-not-allowed">
                                        <i class="fas fa-plus mr-1"></i> Nouveau
                                    </button>
                                </div>
                                @error('vehicle_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror

                                {{-- Formulaire inline nouveau véhicule --}}
                                <div id="edit-inline-vehicle-form"
                                    class="hidden mt-3 bg-green-50 border border-green-200 rounded-lg p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">N° Immatriculation
                                            <span class="text-red-500">*</span></label>
                                        <input type="text" id="edit_inline_vehicle_number"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:outline-none"
                                            placeholder="BJ-0000-AB">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Type</label>
                                        <select id="edit_inline_vehicle_type"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:outline-none">
                                            <option value="tricycle">Tricycle</option>
                                            <option value="moto">Moto</option>
                                            <option value="car">Voiture</option>
                                        </select>
                                    </div>

                                    <div class="md:col-span-3 flex justify-end gap-2">
                                        <button type="button" onclick="toggleEditVehicleInline()"
                                            class="px-4 py-1.5 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition">Annuler</button>
                                        <button type="button" onclick="saveEditInlineVehicle()"
                                            class="px-4 py-1.5 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 transition font-semibold">
                                            <i class="fas fa-save mr-1"></i> Ajouter
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Durée du nouveau contrat --}}
                            <div id="edit-new-contract-fields" class="md:col-span-2 hidden">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Durée du contrat</label>
                                        <select name="contract_months"
                                            class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                            <option value="24" selected>24 mois</option>
                                            <option value="30">30 mois</option>
                                            <option value="36">36 mois</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Date de début</label>
                                        <input type="date" name="start_date" value="{{ date('Y-m-d') }}"
                                            class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section nouveau propriétaire --}}
                    <div id="edit-section-new" class="hidden space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nom du propriétaire <span
                                        class="text-red-600">*</span></label>
                                <input type="text" name="new_owner_name" value="{{ old('new_owner_name') }}"
                                    class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Téléphone <span
                                        class="text-red-600">*</span></label>
                                <input type="text" name="new_owner_phone" value="{{ old('new_owner_phone') }}"
                                    class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                                    placeholder="+229 XX XX XX XX XX">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="new_owner_email" value="{{ old('new_owner_email') }}"
                                    class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mot de passe <span
                                        class="text-red-600">*</span></label>
                                <div class="flex gap-2 mt-1">
                                    <input type="text" name="new_owner_password" id="new_owner_password" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('password') border-red-500 @enderror"
                                        placeholder="Mot de passe sécurisé">
                                    <button type="button" onclick="generateOwnerPassword()"
                                        class="px-3 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 text-sm font-semibold whitespace-nowrap">
                                        <i class="fas fa-refresh mr-1"></i> Générer
                                    </button>
                                </div>
                                @error('new_owner_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-4">
                            <p class="text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-truck-pickup text-[#286b41] mr-2"></i>Véhicule du propriétaire
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">N° Immatriculation <span
                                            class="text-red-600">*</span></label>
                                    <input type="text" name="new_vehicle_number"
                                        value="{{ old('new_vehicle_number') }}"
                                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('new_vehicle_number') border-red-500 @enderror"
                                        placeholder="BJ-0000-AB">
                                    @error('new_vehicle_number')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Type</label>
                                    <select name="new_vehicle_type"
                                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                        <option value="tricycle">Tricycle</option>
                                        <option value="moto">Moto</option>
                                        <option value="car">Voiture</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Durée du contrat agent</label>
                                <select name="contract_months"
                                    class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                    <option value="24" selected>24 mois</option>
                                    <option value="30">30 mois</option>
                                    <option value="36">36 mois</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date de début</label>
                                <input type="date" name="start_date" value="{{ date('Y-m-d') }}"
                                    class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>

                        {{-- Contrat proprio-véhicule optionnel --}}
                        <div
                            class="bg-purple-50 border border-purple-100 rounded-lg p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <p class="md:col-span-2 text-xs font-bold text-purple-700 uppercase tracking-wide">
                                Contrat propriétaire-véhicule <span class="font-normal text-gray-400">(optionnel)</span>
                            </p>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Montant total (FCFA)</label>
                                <input type="number" name="contract_total_amount" min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none"
                                    placeholder="ex: 2500000">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Mensualité (FCFA)</label>
                                <input type="number" name="contract_monthly_payment" min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none"
                                    placeholder="ex: 104167">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Date début contrat
                                    véhicule</label>
                                <input type="date" name="contract_start_date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Date fin contrat
                                    véhicule</label>
                                <input type="date" name="contract_end_date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>

        {{-- Boutons --}}
        <div class="bg-white rounded-lg shadow-md px-6 py-4 flex justify-end space-x-3">
            <a href="{{ route('admin.drivers.show', $driver) }}"
                class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                <i class="fas fa-times mr-2"></i> Annuler
            </a>
            <button type="submit"
                class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition font-semibold">
                <i class="fas fa-save mr-2"></i> Enregistrer
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            async function generateOwnerPassword() {
                const res = await fetch('{{ route('admin.users.generate-password') }}');
                const data = await res.json();
                document.getElementById('new_owner_password').value = data.password;
            }

            document.addEventListener('DOMContentLoaded', () => {
                generateOwnerPassword();
            });

            // ── Choix du mode ────────────────────────────────────────
            document.querySelectorAll('input[name="_owner_mode"]').forEach(radio => {
                radio.addEventListener('change', () => switchEditMode(radio.value));
            });
            document.querySelectorAll('.edit-mode-card').forEach(card => {
                card.addEventListener('click', () => {
                    const r = card.querySelector('input[type="radio"]');
                    r.checked = true;
                    switchEditMode(r.value);
                });
            });

            function switchEditMode(mode) {
                const isExisting = mode === 'existing';

                document.getElementById('edit-mode-existing-label').className =
                    `edit-mode-card cursor-pointer rounded-xl border-2 p-4 flex items-center gap-3 transition ${isExisting ? 'border-[#286b41] bg-[#286b41]/10' : 'border-gray-200 bg-white'}`;
                document.getElementById('edit-mode-new-label').className =
                    `edit-mode-card cursor-pointer rounded-xl border-2 p-4 flex items-center gap-3 transition ${!isExisting ? 'border-purple-600 bg-purple-50' : 'border-gray-200 bg-white'}`;

                document.getElementById('edit-section-existing').classList.toggle('hidden', !isExisting);
                document.getElementById('edit-section-new').classList.toggle('hidden', isExisting);
            }

            // ── Chargement des véhicules ─────────────────────────────
            async function onEditOwnerSelected(ownerId) {
                const vehicleSelect = document.getElementById('edit_vehicle_id_select');
                const wrap = document.getElementById('edit-vehicles-wrap');
                const addBtn = document.getElementById('edit-btn-add-vehicle');
                const contractFields = document.getElementById('edit-new-contract-fields');

                if (!ownerId) {
                    vehicleSelect.innerHTML = '<option value="">— Sélectionnez d\'abord un propriétaire —</option>';
                    wrap.classList.add('hidden');
                    contractFields.classList.add('hidden');
                    addBtn.disabled = true;
                    return;
                }

                wrap.classList.remove('hidden');
                addBtn.disabled = false;
                vehicleSelect.innerHTML = '<option value="">Chargement...</option>';

                try {
                    const res = await fetch(`/admin/owners/${ownerId}/vehicles`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();

                    vehicleSelect.innerHTML = '<option value="">— Sélectionnez un véhicule —</option>';
                    data.forEach(v => {
                        vehicleSelect.innerHTML +=
                            `<option value="${v.id}">${v.vehicle_number} (${v.vehicle_type})${v.color ? ' · ' + v.color : ''}</option>`;
                    });

                    if (!data.length) {
                        vehicleSelect.innerHTML +=
                            '<option value="" disabled>Aucun véhicule — utilisez "+ Nouveau"</option>';
                    }
                } catch {
                    vehicleSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                }
            }

            // Afficher les champs durée/date quand un véhicule est choisi
            document.getElementById('edit_vehicle_id_select')?.addEventListener('change', function() {
                document.getElementById('edit-new-contract-fields').classList.toggle('hidden', !this.value);
            });

            // ── Formulaire inline véhicule ───────────────────────────
            function toggleEditVehicleInline() {
                const form = document.getElementById('edit-inline-vehicle-form');
                const select = document.getElementById('edit_vehicle_id_select');
                const shown = !form.classList.contains('hidden');
                form.classList.toggle('hidden', shown);
                select.disabled = !shown;
                if (!shown) document.getElementById('edit-new-contract-fields').classList.add('hidden');
            }

            async function saveEditInlineVehicle() {
                const ownerId = document.getElementById('edit_owner_id_select').value;
                const number = document.getElementById('edit_inline_vehicle_number').value.trim();
                const type = document.getElementById('edit_inline_vehicle_type').value;
                const color = document.getElementById('edit_inline_vehicle_color').value.trim();

                if (!number) {
                    showAlert('error', 'Le numéro d\'immatriculation est obligatoire.');
                    return;
                }

                const res = await fetch('/admin/vehicles', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        owner_id: ownerId,
                        vehicle_number: number,
                        vehicle_type: type,
                        color
                    }),
                });
                const data = await res.json();

                if (data.success) {
                    const label =
                        `${data.vehicle.vehicle_number} (${data.vehicle.vehicle_type})${data.vehicle.color ? ' · ' + data.vehicle.color : ''}`;
                    const select = document.getElementById('edit_vehicle_id_select');
                    select.disabled = false;
                    select.appendChild(new Option(label, data.vehicle.id, true, true));
                    document.getElementById('edit-new-contract-fields').classList.remove('hidden');
                    toggleEditVehicleInline();
                    showAlert('success', 'Véhicule ajouté et sélectionné.');
                } else {
                    showAlert('error', data.message ?? 'Erreur lors de la création du véhicule.');
                }
            }

            // Pré-charger si old('owner_id') présent après erreur validation
            document.addEventListener('DOMContentLoaded', () => {
                const ownerId = document.getElementById('edit_owner_id_select')?.value;
                if (ownerId) onEditOwnerSelected(ownerId);
            });
        </script>
    @endpush
@endsection
