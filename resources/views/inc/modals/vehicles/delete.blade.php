<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-30">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-800">Supprimer le véhicule ?</h3>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-6" id="deleteMessage">Êtes-vous sûr de vouloir supprimer ce véhicule ?</p>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex gap-3">
                    <button type="button" onclick="closeDeleteModal()"
                        class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Annuler
                    </button>
                    <button type="submit"
                        class="flex-1 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold">
                        Supprimer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
