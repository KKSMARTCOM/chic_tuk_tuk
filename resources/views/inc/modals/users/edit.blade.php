<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-30">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-800">Modifier l'Utilisateur</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editForm" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" id="edit_name" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="edit_email"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="phone" id="edit_phone" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Profil <span
                            class="text-red-500">*</span></label>
                    <select name="profil" id="edit_profil" required onchange="onProfilChange(this.value, 'edit')"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="client">Client</option>
                        <option value="admin">Administrateur</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rôle</label>
                    <select name="role" id="edit_role" onchange="onRoleChange(this.value, 'edit')"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">— Par défaut (profil) —</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->label ?? $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                    <input type="text" name="adresse" id="edit_adresse"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="md:col-span-2 flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                        class="w-4 h-4 accent-purple-600">
                    <label for="edit_is_active" class="text-sm text-gray-700">Compte actif</label>
                </div>
            </div>

            {{-- ===== SECTION PROPRIÉTAIRE ===== --}}
            <div id="edit_owner_section" class="hidden border-t border-gray-200 pt-4 space-y-4">

                {{-- Véhicules actuels du propriétaire --}}
                <div id="edit_current_vehicles_wrap">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-1 h-5 bg-[#286b41] rounded"></div>
                        <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Véhicules actuels</h4>
                    </div>
                    <div id="edit_current_vehicles" class="space-y-2">
                        {{-- Rempli dynamiquement par JS --}}
                    </div>
                </div>

                {{-- Ajouter un véhicule --}}
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-1 h-5 bg-[#286b41] rounded"></div>
                        <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Ajouter un véhicule</h4>
                    </div>

                    {{-- Sélection véhicule existant sans proprio --}}
                    <div class="flex gap-2">
                        <select name="vehicle_id" id="edit_vehicle_id"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#286b41]">
                            <option value="">— Aucun (ou créez-en un nouveau) —</option>
                            @foreach ($availableVehicles ?? [] as $v)
                                <option value="{{ $v->id }}">{{ $v->vehicle_number }} ({{ $v->vehicle_type }})
                                </option>
                            @endforeach
                        </select>
                        <button type="button" onclick="toggleEditVehicleForm()" id="edit_toggle_vehicle_btn"
                            class="px-3 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition text-sm font-semibold whitespace-nowrap">
                            <i class="fas fa-plus mr-1"></i> Nouveau
                        </button>
                    </div>

                    {{-- Formulaire nouveau véhicule --}}
                    <div id="edit_new_vehicle_form"
                        class="hidden mt-3 bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-3">
                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Nouveau véhicule</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">N° Immatriculation</label>
                                <input type="text" name="new_vehicle_number" id="edit_new_vehicle_number"
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
                        </div>
                    </div>

                    {{-- Contrat pour le nouveau/existant véhicule --}}
                    <div id="edit_contract_section"
                        class="hidden mt-3 bg-purple-50 border border-purple-100 rounded-lg p-4 space-y-3">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-1 h-4 bg-purple-500 rounded"></div>
                            <p class="text-xs font-bold text-gray-700 uppercase tracking-wide">Contrat pour ce véhicule
                            </p>
                            <span class="text-xs text-gray-400">(optionnel)</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Montant total
                                    (FCFA)</label>
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
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeEditModal()"
                    class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Annuler
                </button>
                <button type="submit"
                    class="flex-1 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
