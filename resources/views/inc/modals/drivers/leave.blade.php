<div id="leaveModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center px-4 z-20">
    <div class="bg-white rounded-lg p-6 max-w-xl w-full">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Ajouter une pause</h2>
        <form action="{{ route('admin.leaves.add-ongoing', $driverProfile->id) }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                    <input type="date" name="start_date" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    @error('start_date')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de jours demandés</label>
                    <input type="number" name="requested_days" min="1" value="1" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    @error('requested_days')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg px-3 py-2 text-xs text-blue-700 mb-4">
                <i class="fas fa-info-circle mr-1"></i>
                Jours disponibles (indicatif) : <strong>{{ $driverProfile?->available_leave_days ?? 0 }}</strong>
                — la pause démarre à la date choisie et reste active jusqu'à ce qu'un administrateur y mette fin,
                même si elle dépasse le nombre de jours indiqué.
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="flex-1 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700">
                    Confirmer
                </button>
                <button type="button" onclick="closeLeaveModal()"
                    class="px-4 py-2 bg-red-100 text-red-700 rounded-lg">Annuler</button>
            </div>
        </form>
    </div>
</div>
