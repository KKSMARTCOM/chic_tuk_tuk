@if ($activeContract)
    <div id="endContractModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-30 px-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">Terminer le contrat</h3>
                <button onclick="closeEndContractModal()" class="text-gray-400 hover:text-gray-600"><i
                        class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('admin.driver-contracts.end', $activeContract) }}" method="POST"
                class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin <span
                            class="text-red-500">*</span></label>
                    <input type="date" name="end_date" value="{{ date('Y-m-d') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Raison <span
                            class="text-red-500">*</span></label>
                    <select name="end_reason" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="demission">Démission</option>
                        <option value="abandon">Abandon</option>
                        <option value="fin_contrat">Fin de contrat</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="end_notes" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                        placeholder="Précisions..."></textarea>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-2.5 text-xs text-amber-700">
                    <i class="fas fa-info-circle mr-1"></i>
                    Une pause véhicule sera créée automatiquement jusqu'à l'assignation d'un nouvel agent.
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeEndContractModal()"
                        class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Annuler</button>
                    <button type="submit"
                        class="flex-1 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold">
                        <i class="fas fa-stop mr-1"></i> Terminer le contrat
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
