<div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-30">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-800">Nouvel Utilisateur</h3>
            <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('admin.users.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="phone" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Profil <span
                            class="text-red-500">*</span></label>
                    <select name="profil" id="create_profil" required onchange="onProfilChange(this.value, 'create')"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="client">Client</option>
                        <option value="admin">Administrateur</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rôle</label>
                    <select name="role" id="create_role" onchange="onRoleChange(this.value, 'create')"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">— Par défaut (profil) —</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->label ?? $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                    <input type="text" name="adresse"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="md:col-span-2">
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

            {{-- ===== SECTION PROPRIÉTAIRE (affichée si rôle = proprietaire) ===== --}}
            <div id="create_owner_section" class="hidden border-t border-gray-200 pt-4 space-y-4">

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
                            <option value="">— Aucun véhicule existant —</option>
                            @foreach ($availableVehicles ?? [] as $v)
                                <option value="{{ $v->id }}">{{ $v->vehicle_number }} ({{ $v->vehicle_type }})
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
                <div id="create_new_vehicle_form"
                    class="hidden bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-3">
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
                        {{-- <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Couleur</label>
                            <input type="text" name="new_vehicle_color"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#286b41]"
                                placeholder="ex: Jaune">
                        </div> --}}
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Notes</label>
                            <input type="text" name="new_vehicle_notes"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#286b41]"
                                placeholder="Informations complémentaires (optionnel)">
                        </div>
                    </div>
                </div>

                {{-- Contrat propriétaire-véhicule --}}
                <div class="flex items-center gap-2 mb-1 mt-2">
                    <div class="w-1 h-5 bg-purple-500 rounded"></div>
                    <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Contrat Propriétaire-Véhicule
                    </h4>
                    <span class="text-xs text-gray-400 font-normal">(optionnel)</span>
                </div>

                <div class="bg-purple-50 border border-purple-100 rounded-lg p-4 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Montant total (FCFA)</label>
                            <input type="number" name="contract_total_amount" min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"
                                placeholder="ex: 2500000">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Mensualité (FCFA)</label>
                            <input type="number" name="contract_monthly_payment" min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"
                                placeholder="ex: 104167">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Date de début</label>
                            <input type="date" name="contract_start_date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Date de fin</label>
                            <input type="date" name="contract_end_date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
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

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeCreateModal()"
                    class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Annuler
                </button>
                <button type="submit"
                    class="flex-1 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                    Créer
                </button>
            </div>
        </form>
    </div>
</div>
