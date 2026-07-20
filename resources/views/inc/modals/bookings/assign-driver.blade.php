<div id="assignDriverModal"
    class="fixed px-4 inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden items-center justify-center z-10">
    <div class="relative mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Assigner un Agent</h3>
            <input type="hidden" id="currentBookingId" value="">
            <div class="mb-4">
                <label for="driverSelect" class="block text-sm font-medium text-gray-700 mb-2">
                    Sélectionnez un Agent disponible
                </label>
                <select id="driverSelect"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Chargement...</option>
                </select>
            </div>
            <div class="flex justify-end space-x-3">
                <button onclick="closeModal()"
                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition">
                    Annuler
                </button>
                <button onclick="confirmAssign()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                    Assigner
                </button>
            </div>
        </div>
    </div>
</div>
