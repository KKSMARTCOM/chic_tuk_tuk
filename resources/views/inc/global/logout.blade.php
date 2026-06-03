<div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full mx-4">
        <h2 class="text-lg font-semibold mb-4">Confirmer la déconnexion</h2>
        <p class="mb-4">Êtes-vous sûr de vouloir vous déconnecter ?</p>
        <div class="flex justify-end space-x-2">
            <button onclick="hideLogoutModal()"
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Annuler</button>
            <form method="GET" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit"
                    class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Déconnexion</button>
            </form>
        </div>
    </div>
</div>
