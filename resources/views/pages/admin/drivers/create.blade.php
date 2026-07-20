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

        {{-- ── Contrat Agent ── --}}
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Contrat Agent</h3>
            </div>
            <div class="px-6 py-6 grid grid-cols-1 md:grid-cols-2 gap-6">
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
                    <label class="block text-sm font-medium text-gray-700">Durée du contrat</label>
                    <select name="contract_months"
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        <option value="">-- Sélectionnez --</option>
                        <option value="24" {{ old('contract_months') == '24' ? 'selected' : '' }}>24 mois</option>
                        <option value="30" {{ old('contract_months') == '30' ? 'selected' : '' }}>30 mois</option>
                        <option value="36" {{ old('contract_months') == '36' ? 'selected' : '' }}>36 mois</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date de début</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}"
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                </div>
            </div>
        </div>

        {{-- ── Propriétaire & Véhicule ── --}}
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Propriétaire & Véhicule</h3>
            </div>
            <div class="px-6 py-6 space-y-6">

                {{-- Choix du mode --}}
                <div class="grid grid-cols-2 gap-4">
                    <label id="mode-existing-label"
                        class="mode-card cursor-pointer rounded-xl border-2 border-[#286b41] bg-[#286b41]/10 p-4 flex items-center gap-3 transition">
                        <input type="radio" name="_owner_mode" value="existing" class="sr-only" checked>
                        <div class="w-9 h-9 rounded-full bg-[#286b41] flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-search text-white"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800">Propriétaire existant</p>
                            <p class="text-xs text-gray-500">Sélectionner dans la liste</p>
                        </div>
                    </label>

                    <label id="mode-new-label"
                        class="mode-card cursor-pointer rounded-xl border-2 border-gray-200 bg-white p-4 flex items-center gap-3 transition">
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

                {{-- ── Mode : Propriétaire existant ── --}}
                <div id="section-existing">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Propriétaire <span
                                    class="text-red-600">*</span></label>
                            <select name="owner_id" id="owner_id_select" onchange="onOwnerSelected(this.value)"
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

                        {{-- Véhicules du propriétaire sélectionné --}}
                        <div id="owner-vehicles-wrap" class="{{ old('owner_id') ? '' : 'hidden' }}">
                            <label class="block text-sm font-medium text-gray-700">Véhicule <span
                                    class="text-red-600">*</span></label>
                            <div class="flex flex-wrap gap-2 mt-1">
                                <select name="vehicle_id" id="vehicle_id_select"
                                    class="flex-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('vehicle_id') border-red-500 @enderror">
                                    <option value="">— Sélectionnez d'abord un propriétaire —</option>
                                </select>
                                <button type="button" id="btn-add-vehicle" onclick="toggleAddVehicleInline()" disabled
                                    class="px-3 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 text-sm font-semibold whitespace-nowrap disabled:opacity-40 disabled:cursor-not-allowed">
                                    <i class="fas fa-plus mr-1"></i> Nouveau véhicule
                                </button>
                            </div>
                            @error('vehicle_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            {{-- Formulaire rapide nouveau véhicule (inline) --}}
                            <div id="inline-vehicle-form"
                                class="hidden mt-3 bg-green-50 border border-green-200 rounded-lg p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">N° Immatriculation <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" id="inline_vehicle_number"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:outline-none"
                                        placeholder="BJ-0000-AB">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Type</label>
                                    <select id="inline_vehicle_type"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:outline-none">
                                        <option value="tricycle">Tricycle</option>
                                        <option value="moto">Moto</option>
                                        <option value="car">Voiture</option>
                                    </select>
                                </div>

                                <div class="md:col-span-3 flex justify-end gap-2">
                                    <button type="button" onclick="toggleAddVehicleInline()"
                                        class="px-4 py-1.5 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition">
                                        Annuler
                                    </button>
                                    <button type="button" onclick="saveInlineVehicle()"
                                        class="px-4 py-1.5 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 transition font-semibold">
                                        <i class="fas fa-save mr-1"></i> Ajouter ce véhicule
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Mode : Nouveau propriétaire ── --}}
                <div id="section-new" class="hidden space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nom du propriétaire <span
                                    class="text-red-600">*</span></label>
                            <input type="text" name="new_owner_name" value="{{ old('new_owner_name') }}"
                                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Téléphone du propriétaire <span
                                    class="text-red-600">*</span></label>
                            <input type="text" name="new_owner_phone" value="{{ old('new_owner_phone') }}"
                                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                                placeholder="+229 XX XX XX XX XX">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email du propriétaire</label>
                            <input type="email" name="new_owner_email" value="{{ old('new_owner_email') }}"
                                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Mot de passe du propriétaire <span
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

                    {{-- Véhicule du nouveau propriétaire --}}
                    <div class="border-t border-gray-100 pt-4">
                        <p class="text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-truck-pickup text-[#286b41] mr-2"></i>Véhicule du propriétaire
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">N° Immatriculation <span
                                        class="text-red-600">*</span></label>
                                <input type="text" name="new_vehicle_number" value="{{ old('new_vehicle_number') }}"
                                    class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('new_vehicle_number') border-red-500 @enderror"
                                    placeholder="BJ-0000-AB">
                                @error('new_vehicle_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Type de véhicule <span
                                        class="text-red-600">*</span></label>
                                <select name="new_vehicle_type"
                                    class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                    <option value="tricycle"
                                        {{ old('new_vehicle_type', 'tricycle') == 'tricycle' ? 'selected' : '' }}>Tricycle
                                    </option>
                                    <option value="moto" {{ old('new_vehicle_type') == 'moto' ? 'selected' : '' }}>Moto
                                    </option>
                                    <option value="car" {{ old('new_vehicle_type') == 'car' ? 'selected' : '' }}>
                                        Voiture</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Contrat proprio-véhicule (optionnel) --}}
                    <div class="border-t border-gray-100 pt-4">
                        <p class="text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-file-contract text-purple-600 mr-2"></i>Contrat propriétaire-véhicule
                            <span class="text-xs text-gray-400 font-normal">(optionnel)</span>
                        </p>
                        <div
                            class="bg-purple-50 border border-purple-100 rounded-lg p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Montant total (FCFA)</label>
                                <input type="number" name="contract_total_amount"
                                    value="{{ old('contract_total_amount') }}" min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none"
                                    placeholder="ex: 2500000">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Mensualité (FCFA)</label>
                                <input type="number" name="contract_monthly_payment"
                                    value="{{ old('contract_monthly_payment') }}" min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none"
                                    placeholder="ex: 104167">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Date de début contrat</label>
                                <input type="date" name="contract_start_date"
                                    value="{{ old('contract_start_date') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Date de fin contrat</label>
                                <input type="date" name="contract_end_date" value="{{ old('contract_end_date') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-sm text-blue-800 bg-blue-50 rounded-lg px-4 py-3">
                    <i class="fas fa-info-circle mr-2"></i>
                    L'agent sera créé <strong>Actif</strong> et <strong>Disponible</strong> par défaut.
                </p>
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

            async function generateOwnerPassword() {
                const res = await fetch('{{ route('admin.users.generate-password') }}');
                const data = await res.json();
                document.getElementById('new_owner_password').value = data.password;
            }

            document.addEventListener('DOMContentLoaded', () => {
                generatePassword();
                generateOwnerPassword();
            });

            // ── Choix du mode propriétaire ───────────────────────────
            document.querySelectorAll('input[name="_owner_mode"]').forEach(radio => {
                radio.addEventListener('change', () => switchOwnerMode(radio.value));
            });

            document.querySelectorAll('.mode-card').forEach(card => {
                card.addEventListener('click', () => {
                    const radio = card.querySelector('input[type="radio"]');
                    radio.checked = true;
                    switchOwnerMode(radio.value);
                });
            });

            function switchOwnerMode(mode) {
                const isExisting = mode === 'existing';

                // Cards
                document.getElementById('mode-existing-label').className =
                    `mode-card cursor-pointer rounded-xl border-2 p-4 flex items-center gap-3 transition ${isExisting ? 'border-[#286b41] bg-[#286b41]/10' : 'border-gray-200 bg-white'}`;
                document.getElementById('mode-new-label').className =
                    `mode-card cursor-pointer rounded-xl border-2 p-4 flex items-center gap-3 transition ${!isExisting ? 'border-purple-600 bg-purple-50' : 'border-gray-200 bg-white'}`;

                // Icônes
                document.querySelector('#mode-existing-label .w-9').className =
                    `w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 ${isExisting ? 'bg-[#286b41]' : 'bg-gray-100'}`;
                document.querySelector('#mode-existing-label .w-9 i').className =
                    `fas fa-search ${isExisting ? 'text-white' : 'text-gray-400'}`;
                document.querySelector('#mode-new-label .w-9').className =
                    `w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 ${!isExisting ? 'bg-purple-600' : 'bg-gray-100'}`;
                document.querySelector('#mode-new-label .w-9 i').className =
                    `fas fa-plus ${!isExisting ? 'text-white' : 'text-gray-400'}`;

                // Sections
                document.getElementById('section-existing').classList.toggle('hidden', !isExisting);
                document.getElementById('section-new').classList.toggle('hidden', isExisting);
            }

            // ── Chargement des véhicules du propriétaire ─────────────
            async function onOwnerSelected(ownerId) {
                const vehicleSelect = document.getElementById('vehicle_id_select');
                const wrap = document.getElementById('owner-vehicles-wrap');
                const addBtn = document.getElementById('btn-add-vehicle');

                if (!ownerId) {
                    vehicleSelect.innerHTML = '<option value="">— Sélectionnez d\'abord un propriétaire —</option>';
                    wrap.classList.add('hidden');
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

                    if (!data.length) {
                        vehicleSelect.innerHTML +=
                            '<option value="" disabled>Aucun véhicule — utilisez "+ Nouveau véhicule"</option>';
                    } else {
                        data.forEach(v => {
                            vehicleSelect.innerHTML +=
                                `<option value="${v.id}">${v.vehicle_number} (${v.vehicle_type})${v.color ? ' · ' + v.color : ''}</option>`;
                        });
                    }
                } catch {
                    vehicleSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                }
            }

            // ── Formulaire inline nouveau véhicule ───────────────────
            function toggleAddVehicleInline() {
                const form = document.getElementById('inline-vehicle-form');
                const select = document.getElementById('vehicle_id_select');
                const shown = !form.classList.contains('hidden');

                form.classList.toggle('hidden', shown);
                select.disabled = !shown; // désactiver le select quand on crée un véhicule
            }

            async function saveInlineVehicle() {
                const ownerId = document.getElementById('owner_id_select').value;
                const number = document.getElementById('inline_vehicle_number').value.trim();
                const type = document.getElementById('inline_vehicle_type').value;

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
                    }),
                });
                const data = await res.json();

                if (data.success) {
                    // Ajouter dans le select et sélectionner
                    const label =
                        `${data.vehicle.vehicle_number} (${data.vehicle.vehicle_type})${data.vehicle.color ? ' · ' + data.vehicle.color : ''}`;
                    const opt = new Option(label, data.vehicle.id, true, true);
                    const select = document.getElementById('vehicle_id_select');
                    select.disabled = false;
                    select.appendChild(opt);
                    toggleAddVehicleInline();
                    showAlert('success', 'Véhicule ajouté et sélectionné.');
                } else {
                    showAlert('error', data.message ?? 'Erreur lors de la création du véhicule.');
                }
            }

            // Pré-charger si old('owner_id') présent après erreur validation
            document.addEventListener('DOMContentLoaded', () => {
                const ownerId = document.getElementById('owner_id_select')?.value;
                if (ownerId) onOwnerSelected(ownerId);
            });
        </script>
    @endpush
@endsection
