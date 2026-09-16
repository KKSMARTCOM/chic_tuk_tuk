<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-30">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-800">Modifier l'Administrateur</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editForm" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" id="edit_name" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="edit_email"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="phone" id="edit_phone" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Profil <span
                            class="text-red-500">*</span></label>
                    <select name="profil" id="edit_profil" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option selected value="admin">Administrateur</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rôle</label>
                    <select name="role" id="edit_role"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">— Par défaut (profil) —</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->label ?? $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                    <input type="text" name="adresse" id="edit_adresse"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="md:col-span-2 flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                        class="w-4 h-4 accent-purple-600">
                    <label for="edit_is_active" class="text-sm text-gray-700">Compte actif</label>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeEditModal()"
                    class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Annuler
                </button>
                <button type="submit"
                    class="flex-1 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
