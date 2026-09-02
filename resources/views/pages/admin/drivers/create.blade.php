@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">Ajouter un Agent</h1>
                <p class="text-xs md:text-base text-gray-600">Créez un nouveau compte Agent</p>
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
    <form action="{{ route('admin.drivers.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- ── Informations personnelles ── --}}
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Informations personnelles</h3>
            </div>
            <div class="px-6 py-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nom complet <span
                            class="text-red-600">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('name') border-red-500 @enderror"
                        placeholder="Nom complet">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('email') border-red-500 @enderror"
                        placeholder="email@example.com">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Téléphone <span
                            class="text-red-600">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('phone') border-red-500 @enderror"
                        placeholder="+229 XX XX XX XX XX">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Mot de passe <span
                            class="text-red-600">*</span></label>
                    <div class="flex gap-2 mt-1">
                        <input type="text" name="password" id="password" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('password') border-red-500 @enderror"
                            placeholder="Mot de passe sécurisé">
                        <button type="button" onclick="generatePassword()"
                            class="px-3 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 text-sm font-semibold whitespace-nowrap">
                            <i class="fas fa-refresh mr-1"></i> Générer
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Adresse</label>
                    <input type="text" name="adresse" value="{{ old('adresse') }}"
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                        placeholder="Adresse complète">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Catégorie de permis <span
                            class="text-red-600">*</span></label>
                    <input type="text" name="license_number" value="{{ old('license_number') }}" required
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('license_number') border-red-500 @enderror"
                        placeholder="Catégorie de permis">
                    @error('license_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ── Propriétaire & Véhicule ── --}}
        <div class="bg-white rounded-lg shadow-md px-6">
            {{-- Bouton bascule Nouveau / Reconduire --}}
            <div class="flex gap-3 mb-5 py-6">
                <button type="button" id="btn-new-contract" onclick="setContractMode('new')"
                    class="flex-1 py-2.5 rounded-xl border-2 border-[#286b41] bg-[#286b41]/10 text-[#286b41] font-semibold text-sm transition">
                    <i class="fas fa-file-contract mr-2"></i> Nouveau contrat
                </button>
                <button type="button" id="btn-renewal" onclick="setContractMode('renewal')"
                    class="flex-1 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-gray-500 font-semibold text-sm transition">
                    <i class="fas fa-rotate-right mr-2"></i> Reconduire un contrat
                </button>
            </div>
            <input type="hidden" name="_contract_mode" id="contract_mode" value="new">

            {{-- ── Mode : Nouveau contrat ── --}}
            <div id="section-new-contract" class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800">Propriétaire & Véhicule</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Propriétaire </label>
                        <select name="owner_id" id="owner_id_select" onchange="onOwnerSelected(this.value)"
                            class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('owner_id') border-red-500 @enderror">
                            <option value="">— Sélectionnez un propriétaire —</option>
                            @foreach ($owners as $owner)
                                <option value="{{ $owner->id }}" {{ old('owner_id') == $owner->id ? 'selected' : '' }}>
                                    {{ $owner->name }} — {{ $owner->phone }}
                                </option>
                            @endforeach
                        </select>
                        @error('owner_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Véhicules du propriétaire sélectionné --}}
                    <div id="owner-vehicles-wrap" class="{{ old('owner_id') ? '' : 'hidden' }}">
                        <label class="block text-sm font-medium text-gray-700">Véhicule</label>
                        <div class="flex flex-wrap gap-2 mt-1">
                            <select name="vehicle_id" id="vehicle_id_select" onchange="onVehicleSelected(this.value, 'new')"
                                class="flex-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('vehicle_id') border-red-500 @enderror">
                                <option value="">— Sélectionnez d'abord un propriétaire —</option>
                            </select>
                        </div>
                        @error('vehicle_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- ── Contrat Agent ── --}}
                <h3 class="text-lg font-semibold text-gray-800">Contrat Agent</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Code Agent</label>
                        <input type="text" name="agent_code" value="{{ old('agent_code') }}"
                            class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                            placeholder="Code agent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">ID Agent</label>
                        <input type="text" name="agent_id" value="{{ old('agent_id') }}"
                            class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                            placeholder="ID agent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Durée du contrat <span
                                class="text-red-500">*</span></label>
                        <select name="contract_months" id="contract_months_new"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 @error('contract_months') border-red-500 @enderror">
                            <option value="">— Sélectionnez —</option>
                            <option value="24" {{ old('contract_months') == '24' ? 'selected' : '' }}>24 mois</option>
                            <option value="30" {{ old('contract_months') == '30' ? 'selected' : '' }}>30 mois</option>
                            <option value="36" {{ old('contract_months') == '36' ? 'selected' : '' }}>36 mois</option>
                        </select>
                        @error('contract_months')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de début <span
                                class="text-red-500">*</span></label>
                        <input type="date" name="start_date" id="start_date_new"
                            value="{{ old('start_date', date('Y-m-d')) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 @error('start_date') border-red-500 @enderror">
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pb-4">
                    <p class="text-sm text-blue-800 bg-blue-50 rounded-lg px-4 py-3">
                        <i class="fas fa-info-circle mr-2"></i>
                        L'agent sera créé <strong>Actif</strong> et <strong>Disponible</strong> par défaut.
                    </p>
                </div>
            </div>

            {{-- ── Mode : Reconduction de contrat ── --}}
            <div id="section-renewal" class="hidden space-y-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-sm text-blue-700">
                    <i class="fas fa-info-circle mr-2"></i>
                    La durée du contrat sera pré-remplie avec le temps restant sur le contrat du véhicule.
                </div>

                <h3 class="text-lg font-semibold text-gray-800">Propriétaire & Véhicule</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Propriétaire <span
                                class="text-red-500">*</span></label>
                        <select name="renewal_owner_id" id="renewal_owner_id_select"
                            onchange="onRenewalOwnerSelected(this.value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">— Sélectionnez un propriétaire —</option>
                            @foreach ($ownersForRenewal as $owner)
                                <option value="{{ $owner->id }}">
                                    {{ $owner->name }} — {{ $owner->phone }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="renewal-vehicles-wrap" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Véhicule <span
                                class="text-red-500">*</span></label>
                        <select name="renewal_vehicle_id" id="renewal_vehicle_id_select"
                            onchange="onVehicleSelected(this.value, 'renewal')"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">— Sélectionnez d'abord un propriétaire —</option>
                        </select>
                    </div>
                </div>

                {{-- ── Contrat Agent ── --}}
                <h3 class="text-lg font-semibold text-gray-800">Contrat Agent</h3>

                {{-- Résumé du contrat et durée restante --}}
                <div id="renewal-contract-summary" class="hidden bg-white border border-gray-200 rounded-xl p-4">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4 text-sm">
                        <div>
                            <p class="text-xs text-gray-500">Durée totale contrat</p>
                            <p id="renewal-total-months" class="font-semibold text-gray-800">—</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Mois déjà effectués</p>
                            <p id="renewal-months-used" class="font-semibold text-orange-600">—</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Temps restant</p>
                            <p id="renewal-remaining-months" class="font-bold text-[#286b41]">—</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Date de début suggérée</p>
                            <p id="renewal-suggested-start" class="font-semibold text-gray-800">—</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Code Agent</label>
                            <input type="text" name="renewal_agent_code" value="{{ old('renewal_agent_code') }}"
                                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Code agent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">ID Agent</label>
                            <input type="text" name="renewal_agent_id" value="{{ old('renewal_agent_id') }}"
                                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                                placeholder="ID agent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Durée du contrat (mois) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="renewal_contract_months" id="renewal_contract_months"
                                min="1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#286b41]">
                            <p id="renewal-months-hint" class="text-xs text-gray-400 mt-1"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date de début <span
                                    class="text-red-500">*</span></label>
                            <input type="date" name="renewal_start_date" id="renewal_start_date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#286b41]">
                        </div>
                    </div>
                </div>

                <div class="pb-4">
                    <p class="text-sm text-blue-800 bg-blue-50 rounded-lg px-4 py-3">
                        <i class="fas fa-info-circle mr-2"></i>
                        L'agent sera créé <strong>Actif</strong> et <strong>Disponible</strong> par défaut.
                    </p>
                </div>
            </div>
        </div>

        {{-- Boutons --}}
        <div class="bg-white rounded-lg shadow-md px-6 py-4 flex justify-end space-x-3">
            <a href="{{ route('admin.drivers.index') }}"
                class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                <i class="fas fa-times mr-2"></i> Annuler
            </a>
            <button type="submit"
                class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition font-semibold">
                <i class="fas fa-check mr-2"></i> Créer l'Agent
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            // Génère un mot de passe via AJAX
            async function generatePassword() {
                const res = await fetch('{{ route('admin.users.generate-password') }}');
                const data = await res.json();
                document.getElementById('password').value = data.password;
            }

            document.addEventListener('DOMContentLoaded', () => {
                generatePassword();
            });

            // ── Chargement des véhicules du propriétaire ─────────────
            const ownerVehicles = @json($ownerVehicles);
            const ownerVehiclesRenewal = @json($ownerVehiclesForRenewal);

            const oldOwnerId = "{{ old('owner_id') }}";
            const oldVehicleId = "{{ old('vehicle_id') }}";

            // ── Bascule Nouveau / Reconduction ───────────────────────
            function setContractMode(mode) {
                const isNew = mode === 'new';
                document.getElementById('contract_mode').value = mode;

                document.getElementById('section-new-contract').classList.toggle('hidden', !isNew);
                document.getElementById('section-renewal').classList.toggle('hidden', isNew);

                document.getElementById('btn-new-contract').className =
                    `flex-1 py-2.5 rounded-xl border-2 font-semibold text-sm transition ${
                isNew ? 'border-[#286b41] bg-[#286b41]/10 text-[#286b41]'
                       : 'border-gray-200 bg-white text-gray-500'}`;
                document.getElementById('btn-renewal').className =
                    `flex-1 py-2.5 rounded-xl border-2 font-semibold text-sm transition ${
                !isNew ? 'border-blue-600 bg-blue-50 text-blue-700'
                        : 'border-gray-200 bg-white text-gray-500'}`;
            }

            function onOwnerSelected(ownerId) {
                const wrap = document.getElementById('owner-vehicles-wrap');
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

            // ── Nouveau contrat : sélection véhicule → pré-remplir durée/date ──
            function onVehicleSelected(vehicleId, mode) {
                if (mode === 'new') {
                    const ownerId = document.getElementById('owner_id_select').value;
                    const vehicles = ownerVehicles[ownerId] ?? [];
                    const vehicle = vehicles.find(v => v.id === vehicleId);

                    if (vehicle) {
                        // Pré-remplir durée depuis le contrat proprio-véhicule
                        const monthsSelect = document.getElementById('contract_months_new');
                        if (vehicle.contract_months && [24, 30, 36].includes(Number(vehicle.contract_months))) {
                            monthsSelect.value = vehicle.contract_months;
                        }
                        // Pré-remplir date de début
                        if (vehicle.contract_start_date) {
                            document.getElementById('start_date_new').value = vehicle.contract_start_date;
                        }
                    }
                } else if (mode == 'renewal') {
                    // ── Reconduction : sélection véhicule → pré-remplir résumé ──
                    const ownerId = document.getElementById('renewal_owner_id_select').value;
                    const vehicles = ownerVehiclesRenewal[ownerId] ?? [];
                    const vehicle = vehicles.find(v => v.id === vehicleId);
                    const summary = document.getElementById('renewal-contract-summary');

                    if (!vehicle) {
                        summary.classList.add('hidden');
                        return;
                    }

                    summary.classList.remove('hidden');

                    document.getElementById('renewal-total-months').textContent = vehicle.total_months + ' mois';
                    document.getElementById('renewal-months-used').textContent = vehicle.months_used + ' mois';
                    document.getElementById('renewal-remaining-months').textContent = vehicle.remaining_months + ' mois';
                    document.getElementById('renewal-suggested-start').textContent = vehicle.suggested_start_date;

                    // Pré-remplir durée et date
                    const monthsInput = document.getElementById('renewal_contract_months');
                    monthsInput.value = vehicle.remaining_months;
                    monthsInput.min = vehicle.remaining_months; // ne peut pas dépasser le restant
                    document.getElementById('renewal-months-hint').textContent =
                        `Minimum : ${vehicle.remaining_months} mois (durée restante du contrat véhicule)`;

                    document.getElementById('renewal_start_date').value = vehicle.suggested_start_date;
                }
            }

            function resetContractFields(mode) {
                if (mode === 'new') {
                    document.getElementById('contract_months_new').value = '';
                    document.getElementById('start_date_new').value = "{{ date('Y-m-d') }}";
                }
            }

            // ── Reconduction : sélection propriétaire ────────────────
            function onRenewalOwnerSelected(ownerId) {
                const wrap = document.getElementById('renewal-vehicles-wrap');
                const select = document.getElementById('renewal_vehicle_id_select');
                const summary = document.getElementById('renewal-contract-summary');

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
                        `${v.vehicle_number} (${v.vehicle_type}${v.color ? ' · '+v.color : ''}) — ${v.remaining_months} mois restants`;
                    select.innerHTML += `<option value="${v.id}">${label}</option>`;
                });
            }

            // Pré-remplir après une erreur de validation (old())
            document.addEventListener('DOMContentLoaded', () => {
                if (oldOwnerId) onOwnerSelected(oldOwnerId);
                // Restaurer le mode si erreur de validation
                const savedMode = "{{ old('_contract_mode', 'new') }}";
                if (savedMode === 'renewal') setContractMode('renewal');
            });
        </script>
    @endpush
@endsection
