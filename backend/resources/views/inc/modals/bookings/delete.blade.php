<div id="deleteModal" class="fixed px-4 inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-10">
    <div class="bg-white rounded-lg p-8 max-w-md w-full">
        <h3 class="text-2xl font-bold text-gray-800 mb-4">Supprimer la course</h3>
        <p class="text-gray-600 mb-4">Êtes-vous sûr de vouloir supprimer cette course ? Cette action est
            irréversible.</p>
        <form id="deleteForm" method="POST" action="">
            @csrf
            @method('DELETE')
            <input type="hidden" name="status" value="">
            <div class="flex space-x-4">
                <button type="button" onclick="closeDeleteModal()"
                    class="flex-1 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                    Retour
                </button>
                <button type="submit" class="flex-1 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    Supprimer
                </button>
            </div>
        </form>
    </div>
</div>
