<div id="formModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-30">
    <div class="bg-white rounded-2xl shadow-xl max-h-[90vh] w-full max-w-3xl mx-4 my-6 overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10">
            <h3 id="modal-title" class="text-lg font-bold text-gray-800">Ajouter un véhicule</h3>
            <button onclick="closeFormModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="vehicle-form" method="POST" action="{{ route('admin.vehicles.store') }}" class="p-6 space-y-5">
            @csrf
            <div id="method-field"></div>

            {{-- ── Infos véhicule ── --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-1 h-5 bg-gray-400 rounded"></div>
                    <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Informations du véhicule</h4>
                </div>
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
            </div>
        </form>
    </div>
</div>
