@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">Ajouter un Propriétaire</h1>
                <p class="text-xs md:text-base text-gray-600">Créez un nouveau compte Propriétaire</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.owners.index') }}"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <!-- Formulaire d'ajout -->
    <form action="{{ route('admin.owners.store') }}" method="POST" class="bg-white rounded-lg shadow-md p-6 space-y-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet <span
                        class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone <span
                        class="text-red-500">*</span></label>
                <input type="text" name="phone" value="{{ old('phone') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                <input type="text" name="adresse" value="{{ old('adresse') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe <span
                        class="text-red-500">*</span></label>
                <div class="flex gap-2">
                    <input type="text" name="password" id="create_password" required
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 font-mono">
                    <button type="button" onclick="generatePassword()"
                        class="px-3 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition text-sm font-semibold whitespace-nowrap">
                        <i class="fas fa-refresh mr-1"></i> Générer
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-1">Le mot de passe sera affiché une seule fois — notez-le.</p>
            </div>
            <div class="md:col-span-2 flex items-center gap-2">
                <input type="checkbox" name="is_active" id="create_is_active" value="1" checked
                    class="w-4 h-4 accent-purple-600">
                <label for="create_is_active" class="text-sm text-gray-700">Compte actif</label>
            </div>
        </div>

        <div id="create_owner_section" class="border-t border-gray-200 pt-4 space-y-4">

            <div class="flex items-center gap-2 mb-1">
                <div class="w-1 h-5 bg-[#286b41] rounded"></div>
                <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Véhicule</h4>
            </div>

            {{-- Sélection véhicule existant --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Véhicule existant
                    <span class="text-xs text-gray-400 font-normal ml-1">(optionnel — ou créez-en un
                        ci-dessous)</span>
                </label>
                <div class="flex gap-2">
                    <select name="vehicle_id" id="create_vehicle_id"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#286b41]">
                        <option value="">Sélectionnez un véhicule existant</option>
                        @foreach ($availableVehicles ?? [] as $v)
                            <option value="{{ $v->id }}">{{ $v->vehicle_number }}
                                ({{ $v->vehicle_type }})
                            </option>
                        @endforeach
                    </select>
                    <button type="button" onclick="toggleNewVehicleForm()"
                        class="px-3 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition text-sm font-semibold whitespace-nowrap">
                        <i class="fas fa-plus mr-1"></i> Nouveau
                    </button>
                </div>
            </div>

            {{-- Formulaire nouveau véhicule (masqué par défaut) --}}
            <div id="create_new_vehicle_form" class="hidden bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-3">
                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Nouveau véhicule</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">N° Immatriculation</label>
                        <input type="text" name="new_vehicle_number" id="create_new_vehicle_number"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#286b41]"
                            placeholder="ex: BJ-1234-AB">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Type</label>
                        <select name="new_vehicle_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#286b41]">
                            <option value="tricycle">Tricycle</option>
                            <option value="moto">Moto</option>
                            <option value="car">Voiture</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Notes</label>
                        <input type="text" name="new_vehicle_notes"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#286b41]"
                            placeholder="Informations complémentaires (optionnel)">
                    </div>
                </div>
            </div>

            <div id="contract-section" class="hidden mt-4">
                {{-- Contrat propriétaire-véhicule --}}
                <div class="flex items-center gap-2 mb-1 mt-2">
                    <div class="w-1 h-5 bg-purple-500 rounded"></div>
                    <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Contrat Propriétaire-Véhicule
                    </h4>
                    <span class="text-xs text-gray-400 font-normal">(optionnel)</span>
                </div>

                <div class="bg-purple-50 border border-purple-100 rounded-lg p-4 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <p class="text-sm font-medium text-gray-700 mb-2">Option du contrat</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                        Durée du contrat <span class="text-red-500">*</span>
                                    </label>
                                    <select name="contract_months" id="contract_months"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                        <option value="">-- Sélectionnez --</option>
                                        @foreach (\App\Consts\VehicleContract::TOTAL_AMOUNTS as $months => $total)
                                            <option value="{{ $months }}" data-total="{{ $total }}"
                                                {{ old('contract_months') == $months ? 'selected' : '' }}>
                                                {{ $months }} mois
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                        Revenus totaux (FCFA) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="contract_total_amount" id="contract_total_amount"
                                        value="{{ old('contract_total_amount') }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                        Date de début <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="contract_start_date"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                                </div>
                            </div>
                        </div>

                        <div class="col-span-2">
                            <p class="text-sm font-medium text-gray-700 mb-2">Charges mensuelles</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">
                                        <i class="fas fa-wifi mr-1"></i> Internet illimité (FCFA)
                                    </label>
                                    <input type="number" name="unlimited_internet" id="unlimited_internet"
                                        value="{{ old('unlimited_internet', \App\Consts\VehicleContract::DEFAULT_UNLIMITED_INTERNET) }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">
                                        <i class="fab fa-spotify mr-1"></i> Spotify Premium (FCFA)
                                    </label>
                                    <input type="number" name="spotify_premium" id="spotify_premium"
                                        value="{{ old('spotify_premium', \App\Consts\VehicleContract::DEFAULT_SPOTIFY_PREMIUM) }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">
                                        <i class="fas fa-user-tie mr-1"></i> Rémunération manager (FCFA)
                                    </label>
                                    <input type="number" name="manager_remuneration" id="manager_remuneration"
                                        value="{{ old('manager_remuneration', \App\Consts\VehicleContract::DEFAULT_MANAGER_REMUNERATION) }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                </div>
                            </div>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Notes du contrat</label>
                            <textarea name="contract_notes" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"
                                placeholder="Conditions particulières, remarques..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.owners.index') }}"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                Annuler
            </a>
            <button type="submit"
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                Créer
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            // Génère un mot de passe via AJAX
            async function generatePassword() {
                const res = await fetch('{{ route('admin.users.generate-password') }}');
                const data = await res.json();
                document.getElementById('create_password').value = data.password;
            }

            document.addEventListener('DOMContentLoaded', () => {
                generatePassword();
            });

            function toggleContractSection() {
                const hasVehicle = $('#create_vehicle_id').val() !== '';
                const creatingNew = !$('#create_new_vehicle_form').hasClass('hidden');

                if (hasVehicle || creatingNew) {
                    $('#contract-section').removeClass('hidden');
                } else {
                    $('#contract-section').addClass('hidden');
                }
            }

            // ── Afficher/masquer le formulaire nouveau véhicule ─────
            function toggleNewVehicleForm() {
                const form = document.getElementById('create_new_vehicle_form');
                const select = document.getElementById('create_vehicle_id');
                const isShown = !form.classList.contains('hidden');

                form.classList.toggle('hidden', isShown);

                // Si on affiche le formulaire nouveau véhicule,
                // vider la sélection du véhicule existant
                if (!isShown) {
                    select.value = '';
                    select.disabled = true;
                } else {
                    select.disabled = false;
                    // Vider les champs du nouveau véhicule
                    document.getElementById('create_new_vehicle_number').value = '';
                }

                toggleContractSection();
            }

            document.getElementById('contract_months').addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                const total = selected.dataset.total;
                const monthly = selected.dataset.monthly;

                if (total) {
                    document.getElementById('contract_total_amount').value = total;
                } else {
                    document.getElementById('contract_total_amount').value = '';
                }
            });

            $('#create_vehicle_id').on('change', function() {
                if ($(this).val()) {
                    $('#create_new_vehicle_form').addClass('hidden');
                }

                toggleContractSection();
            });

            // Préremplir au chargement si old() a une valeur
            document.addEventListener('DOMContentLoaded', function() {
                const select = document.getElementById('contract_months');
                if (select.value) select.dispatchEvent(new Event('change'));
            });
        </script>
    @endpush
@endsection
