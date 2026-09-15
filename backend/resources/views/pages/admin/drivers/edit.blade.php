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

        {{-- ── Véhicule & Propriétaire ── --}}
        <div class="bg-white rounded-lg shadow-md px-6 py-6 space-y-4">
            <div class="space-y-5">
                <h3 class="text-lg font-semibold text-gray-800">Véhicule & Propriétaire</h3>

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

                    {{-- Bouton bascule Propriétaire existant / Reconduire --}}
                    <div class="flex gap-3 mb-5">
                        <button type="button" id="btn-mode-existing" onclick="setOwnerMode('existing')"
                            class="flex-1 py-2.5 rounded-xl border-2 border-[#286b41] bg-[#286b41]/10 text-[#286b41] font-semibold text-sm transition">
                            <i class="fas fa-file-contract mr-2"></i> Propriétaire existant
                        </button>
                        <button type="button" id="btn-mode-renewal" onclick="setOwnerMode('renewal')"
                            class="flex-1 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-gray-500 font-semibold text-sm transition">
                            <i class="fas fa-rotate-right mr-2"></i> Reconduire un contrat
                        </button>
                    </div>
                    <input type="hidden" name="_owner_mode" id="owner_mode" value="{{ old('_owner_mode', 'existing') }}">

                    {{-- Section propriétaire existant --}}
                    <div id="section-owner-existing" class="space-y-4">
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
                                        <select name="vehicle_id" id="vehicle_id_select"
                                            onchange="onVehicleSelected(this.value, 'new')"
                                            class="flex-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('vehicle_id') border-red-500 @enderror">
                                            <option value="">— Sélectionnez d'abord un propriétaire —</option>
                                        </select>
                                    </div>
                                    @error('vehicle_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- ── Contrat Agent ── --}}
                        <h3 class="text-lg font-semibold text-gray-800">Contrat Agent</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Code Agent</label>
                                <input type="text" name="agent_code"
                                    value="{{ old('agent_code', $driver->driver?->agent_code) }}"
                                    class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">ID Agent</label>
                                <input type="text" name="agent_id"
                                    value="{{ old('agent_id', $driver->driver?->agent_id) }}"
                                    class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            </div>

                            @if ($activeContract)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Durée du contrat actif</label>
                                    <input type="text"
                                        value="{{ $activeContract->contract_months }} mois — depuis {{ $activeContract->start_date->format('d/m/Y') }}"
                                        disabled
                                        class="mt-1 w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50 text-gray-500 cursor-not-allowed">
                                    <p class="text-xs text-gray-400 mt-1">Pour modifier, terminez le contrat actif depuis
                                        la
                                        fiche de
                                        l'agent.</p>
                                </div>
                            @else
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Durée du contrat</label>
                                    <select name="existing_contract_months" id="contract_months_new"
                                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                        <option value="24">24 mois</option>
                                        <option value="30">30 mois</option>
                                        <option value="36">36 mois</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Date de début</label>
                                    <input type="date" name="existing_start_date" id="start_date_new"
                                        value="{{ date('Y-m-d') }}"
                                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- ── Mode : Reconduction ── --}}
                    <div id="section-owner-renewal" class="hidden space-y-4">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-sm text-blue-700">
                            <i class="fas fa-info-circle mr-2"></i>
                            La durée du contrat sera pré-remplie avec le temps restant sur le contrat du véhicule.
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Propriétaire <span
                                        class="text-red-500">*</span></label>
                                <select name="renewal_owner_id" id="edit_renewal_owner_id_select"
                                    onchange="onEditRenewalOwnerSelected(this.value)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="">— Sélectionnez un propriétaire —</option>
                                    @foreach ($ownersForRenewal as $owner)
                                        <option value="{{ $owner->id }}">{{ $owner->name }} — {{ $owner->phone }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="edit-renewal-vehicles-wrap" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Véhicule <span
                                        class="text-red-500">*</span></label>
                                <select name="renewal_vehicle_id" id="edit_renewal_vehicle_id_select"
                                    onchange="onEditVehicleSelected(this.value, 'renewal')"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="">— Sélectionnez d'abord un propriétaire —</option>
                                </select>
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-800">Contrat Agent</h3>

                        <div id="edit-renewal-contract-summary"
                            class="hidden bg-white border border-gray-200 rounded-xl p-4">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4 text-sm">
                                <div>
                                    <p class="text-xs text-gray-500">Durée totale contrat</p>
                                    <p id="edit-renewal-total-months" class="font-semibold text-gray-800">—</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Mois déjà effectués</p>
                                    <p id="edit-renewal-months-used" class="font-semibold text-orange-600">—</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Temps restant</p>
                                    <p id="edit-renewal-remaining-months" class="font-bold text-[#286b41]">—</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Date de début suggérée</p>
                                    <p id="edit-renewal-suggested-start" class="font-semibold text-gray-800">—</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Code Agent</label>
                                    <input type="text" name="renewal_agent_code"
                                        value="{{ old('renewal_agent_code') }}"
                                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">ID Agent</label>
                                    <input type="text" name="renewal_agent_id" value="{{ old('renewal_agent_id') }}"
                                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Durée du contrat (mois)
                                        <span class="text-red-500">*</span></label>
                                    <input type="number" name="renewal_contract_months"
                                        id="edit_renewal_contract_months" min="1"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#286b41]">
                                    <p id="edit-renewal-months-hint" class="text-xs text-gray-400 mt-1"></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de début <span
                                            class="text-red-500">*</span></label>
                                    <input type="date" name="renewal_start_date" id="edit_renewal_start_date"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#286b41]">
                                </div>
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
            const ownerVehicles = @json($ownerVehicles);
            const ownerVehiclesRenewal = @json($ownerVehiclesForRenewal ?? []);

            const oldOwnerId = "{{ old('owner_id') }}";
            const oldVehicleId = "{{ old('vehicle_id') }}";

            // ── Bascule Propriétaire existant / Reconduction ──────────
            function setOwnerMode(mode) {
                const isExisting = mode === 'existing';
                document.getElementById('owner_mode').value = mode;

                document.getElementById('section-owner-existing').classList.toggle('hidden', !isExisting);
                document.getElementById('section-owner-renewal').classList.toggle('hidden', isExisting);

                document.getElementById('btn-mode-existing').className =
                    `flex-1 py-2.5 rounded-xl border-2 font-semibold text-sm transition ${
                isExisting ? 'border-[#286b41] bg-[#286b41]/10 text-[#286b41]'
                           : 'border-gray-200 bg-white text-gray-500'}`;
                document.getElementById('btn-mode-renewal').className =
                    `flex-1 py-2.5 rounded-xl border-2 font-semibold text-sm transition ${
                !isExisting ? 'border-blue-600 bg-blue-50 text-blue-700'
                            : 'border-gray-200 bg-white text-gray-500'}`;
            }

            // ── Chargement des véhicules (mode existant) ──────────────
            async function onEditOwnerSelected(ownerId) {
                const wrap = document.getElementById('edit-vehicles-wrap');
                const select = document.getElementById('vehicle_id_select');

                if (!ownerId) {
                    wrap.classList.add('hidden');
                    select.innerHTML = '<option value="">— Sélectionnez d\'abord un propriétaire —</option>';
                    resetContractFields('new');
                    return;
                }

                const vehicles = ownerVehicles[ownerId] ?? [];
                wrap.classList.remove('hidden');
                select.innerHTML = '<option value="">— Sélectionnez un véhicule —</option>';

                if (!vehicles.length) {
                    select.innerHTML += '<option value="" disabled>Aucun véhicule disponible</option>';
                    return;
                }

                vehicles.forEach(v => {
                    const label = `${v.vehicle_number} (${v.vehicle_type}${v.color ? ' · ' + v.color : ''})`;
                    const selected = v.id === oldVehicleId ? 'selected' : '';
                    select.innerHTML += `<option value="${v.id}" ${selected}>${label}</option>`;
                });

                if (oldVehicleId) onVehicleSelected(oldVehicleId, 'new');
            }

            function onVehicleSelected(vehicleId, mode) {
                const ownerId = document.getElementById('edit_owner_id_select').value;
                const vehicles = ownerVehicles[ownerId] ?? [];
                const vehicle = vehicles.find(v => v.id === vehicleId);

                if (vehicle) {
                    const monthsSelect = document.getElementById('contract_months_new');
                    if (vehicle.contract_months && [24, 30, 36].includes(Number(vehicle.contract_months))) {
                        monthsSelect.value = vehicle.contract_months;
                    }
                    if (vehicle.contract_start_date) {
                        document.getElementById('start_date_new').value = vehicle.contract_start_date;
                    }
                }
            }

            function resetContractFields(mode) {
                document.getElementById('contract_months_new').value = '';
                document.getElementById('start_date_new').value = "{{ date('Y-m-d') }}";
            }

            // ── Reconduction : sélection propriétaire ─────────────────
            function onEditRenewalOwnerSelected(ownerId) {
                const wrap = document.getElementById('edit-renewal-vehicles-wrap');
                const select = document.getElementById('edit_renewal_vehicle_id_select');
                const summary = document.getElementById('edit-renewal-contract-summary');

                if (!ownerId) {
                    wrap.classList.add('hidden');
                    summary.classList.add('hidden');
                    return;
                }

                const vehicles = ownerVehiclesRenewal[ownerId] ?? [];
                wrap.classList.remove('hidden');
                summary.classList.add('hidden');

                select.innerHTML = '<option value="">— Sélectionnez un véhicule —</option>';
                vehicles.forEach(v => {
                    const label =
                        `${v.vehicle_number} (${v.vehicle_type}${v.color ? ' · ' + v.color : ''}) — ${v.remaining_months} mois restants`;
                    select.innerHTML += `<option value="${v.id}">${label}</option>`;
                });
            }

            function onEditVehicleSelected(vehicleId, mode) {
                if (mode !== 'renewal') return;

                const ownerId = document.getElementById('edit_renewal_owner_id_select').value;
                const vehicles = ownerVehiclesRenewal[ownerId] ?? [];
                const vehicle = vehicles.find(v => v.id === vehicleId);
                const summary = document.getElementById('edit-renewal-contract-summary');

                if (!vehicle) {
                    summary.classList.add('hidden');
                    return;
                }

                summary.classList.remove('hidden');
                document.getElementById('edit-renewal-total-months').textContent = vehicle.total_months + ' mois';
                document.getElementById('edit-renewal-months-used').textContent = vehicle.months_used + ' mois';
                document.getElementById('edit-renewal-remaining-months').textContent = vehicle.remaining_months + ' mois';
                document.getElementById('edit-renewal-suggested-start').textContent = vehicle.suggested_start_date;

                const monthsInput = document.getElementById('edit_renewal_contract_months');
                monthsInput.value = vehicle.remaining_months;
                monthsInput.min = vehicle.remaining_months;
                document.getElementById('edit-renewal-months-hint').textContent =
                    `Minimum : ${vehicle.remaining_months} mois (durée restante du contrat véhicule)`;

                document.getElementById('edit_renewal_start_date').value = vehicle.suggested_start_date;
            }

            document.addEventListener('DOMContentLoaded', () => {
                const ownerId = document.getElementById('edit_owner_id_select')?.value;
                if (ownerId) onEditOwnerSelected(ownerId);

                const savedMode = "{{ old('_owner_mode', 'existing') }}";
                if (savedMode === 'renewal') setOwnerMode('renewal');
            });
        </script>
    @endpush
@endsection
