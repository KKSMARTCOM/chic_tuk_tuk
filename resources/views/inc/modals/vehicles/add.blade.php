<div id="modal-form" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-30">
    <div class="bg-white rounded-2xl shadow-xl max-h-[700px] w-full max-w-3xl mx-4 my-6 overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 id="modal-title" class="text-lg font-bold text-gray-800">Ajouter un véhicule</h3>
            <button onclick="closeFormModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="vehicle-form" method="POST" action="{{ route('admin.vehicles.store') }}" class="p-6 space-y-5">
            @csrf
            <div id="method-field"></div>

            {{-- Infos véhicule --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">N° Immatriculation <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="vehicle_number" id="f_vehicle_number" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                        placeholder="BJ-0000-AB">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type <span
                            class="text-red-500">*</span></label>
                    <select name="vehicle_type" id="f_vehicle_type" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="tricycle">Tricycle</option>
                        <option value="moto">Moto</option>
                        <option value="car">Voiture</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" id="f_notes" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                        placeholder="Informations complémentaires..."></textarea>
                </div>
            </div>

            {{-- Propriétaire --}}
            <div class="border-t border-gray-100 pt-4">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-1 h-5 bg-[#286b41] rounded"></div>
                    <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Propriétaire</h4>
                </div>

                {{-- Choix du mode propriétaire --}}
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <label id="owner-mode-existing-label"
                        class="owner-mode-card cursor-pointer rounded-xl border-2 border-[#286b41] bg-[#286b41]/10 p-3 flex items-center gap-2 transition">
                        <input type="radio" name="_owner_mode" value="existing" class="sr-only" checked>
                        <div class="w-8 h-8 rounded-full bg-[#286b41] flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-search text-white text-xs"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800">Propriétaire existant</p>
                        </div>
                    </label>
                    <label id="owner-mode-new-label"
                        class="owner-mode-card cursor-pointer rounded-xl border-2 border-gray-200 bg-white p-3 flex items-center gap-2 transition">
                        <input type="radio" name="_owner_mode" value="new" class="sr-only">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-plus text-gray-400 text-xs"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800">Nouveau propriétaire</p>
                        </div>
                    </label>
                </div>

                {{-- Propriétaire existant --}}
                <div id="section-owner-existing">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Propriétaire <span
                            class="text-red-500">*</span></label>
                    <select name="owner_id" id="f_owner_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">— Sélectionnez un propriétaire —</option>
                        @foreach ($owners as $owner)
                            <option value="{{ $owner->id }}">{{ $owner->name }} ({{ $owner->phone }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Nouveau propriétaire --}}
                <div id="section-owner-new" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="new_owner_name" id="f_new_owner_name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="new_owner_phone" id="f_new_owner_phone"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="+229 XX XX XX XX XX">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="new_owner_email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mot de passe <span
                                class="text-red-600">*</span></label>
                        <div class="flex gap-2 mt-1">
                            <input type="text" name="new_owner_password" id="new_owner_password"
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
            </div>

            {{-- Contrat proprio-véhicule --}}
            <div class="border-t border-gray-100 pt-4" id="contract-section">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-1 h-5 bg-purple-500 rounded"></div>
                    <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Contrat Propriétaire-Véhicule
                    </h4>
                    <span class="text-xs text-gray-400">(optionnel)</span>
                </div>
                <div
                    class="bg-purple-50 border border-purple-100 rounded-lg p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Montant total (FCFA)</label>
                        <input type="number" name="contract_total_amount" id="f_contract_total" min="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="ex: 2500000">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Mensualité (FCFA)</label>
                        <input type="number" name="contract_monthly_payment" id="f_contract_monthly" min="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="ex: 104167">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date de début</label>
                        <input type="date" name="contract_start_date" id="f_contract_start"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date de fin</label>
                        <input type="date" name="contract_end_date" id="f_contract_end"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Notes contrat</label>
                        <textarea name="contract_notes" id="f_contract_notes" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="Conditions particulières..."></textarea>
                    </div>
                </div>
                {{-- En modification : contrat actif déjà existant --}}
                <div id="existing-contract-info"
                    class="hidden mt-3 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
                    <p class="text-xs text-amber-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        Ce véhicule a déjà un contrat actif. Les champs ci-dessus seront ignorés.
                        Pour modifier le contrat, accédez à la <a id="contract-link" href="#"
                            class="underline font-semibold">fiche du contrat</a>.
                    </p>
                </div>
            </div>

            <div class="flex gap-3 pt-2 border-t border-gray-100">
                <button type="button" onclick="closeFormModal()"
                    class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Annuler
                </button>
                <button type="submit"
                    class="flex-1 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                    <i class="fas fa-save mr-1"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
