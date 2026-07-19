<div id="leaveModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center px-4 z-20">
    {{-- contenu identique à l'original --}}
    <div class="bg-white rounded-lg p-6 max-w-xl w-full">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Ajouter une pause</h2>
        <form action="{{ route('admin.leaves.add-instant', $driverProfile->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Sélectionnez les dates</label>
                <div class="flex gap-2">
                    <input type="date" id="adminLeaveDate"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm"
                        min="{{ now()->toDateString() }}" max="{{ now()->endOfMonth()->toDateString() }}">
                    <button type="button" onclick="adminAddDate()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">
                        + Ajouter
                    </button>
                </div>
            </div>
            <div id="adminSelectedDates"
                class="flex flex-wrap gap-2 min-h-10 p-3 bg-gray-50 rounded-lg border border-dashed border-gray-300 mb-3">
                <p class="text-gray-400 text-sm w-full text-center">Aucune date sélectionnée</p>
            </div>
            <div id="adminDatesInputs"></div>
            <p id="adminDateError" class="text-sm text-red-600 mb-3 hidden"></p>
            <div class="bg-blue-50 border border-blue-200 rounded-lg px-3 py-2 text-xs text-blue-700 mb-4">
                <i class="fas fa-info-circle mr-1"></i>
                Jours disponibles : <strong>{{ $driverProfile?->available_leave_days ?? 0 }}</strong>
            </div>
            <div class="flex gap-3">
                <button type="submit" id="adminSubmitBtn" disabled
                    class="flex-1 py-2 bg-blue-600 text-white rounded-lg font-semibold disabled:opacity-40">
                    Confirmer
                </button>
                <button type="button" onclick="adminClearDates()"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg">Réinitialiser</button>
                <button type="button" onclick="closeLeaveModal()"
                    class="px-4 py-2 bg-red-100 text-red-700 rounded-lg">Annuler</button>
            </div>
        </form>
    </div>
</div>
