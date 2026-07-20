<div id="removeDriverModal" class="fixed px-4 inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-10">
    <div class="bg-white rounded-lg p-8 max-w-md w-full">
        <h3 class="text-2xl font-bold text-gray-800 mb-4">Retirer le Agent</h3>
        <p class="text-gray-600 mb-4">Êtes-vous sûr de vouloir retirer le Agent de cette course ? Cette action est
            irréversible.</p>
        <input type="hidden" id="removeBookingId" value="">
        <div class="flex justify-end space-x-3">
            <button type="button" onclick="closeModal()"
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                Annuler
            </button>
            <button type="button" onclick="removeDriver()"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                Retirer
            </button>
        </div>
    </div>
</div>
