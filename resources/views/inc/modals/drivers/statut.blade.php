<div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center px-4 z-20">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h3 class="text-lg font-bold text-gray-800 mb-3">Confirmer</h3>
        <p class="text-gray-600 mb-5" id="statusMessage"></p>
        <input type="hidden" id="statusDriverId"><input type="hidden" id="statusNewStatus">
        <div class="flex gap-3">
            <button onclick="closeStatusModal()"
                class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg">Annuler</button>
            <button onclick="confirmToggleStatus()"
                class="flex-1 py-2 bg-purple-600 text-white rounded-lg font-semibold">Confirmer</button>
        </div>
    </div>
</div>
