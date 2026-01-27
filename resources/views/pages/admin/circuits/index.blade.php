@extends('layouts.app')

@section('content')
    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Rechercher</label>
                <input type="text" id="searchInput" placeholder="Nom du circuit..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Statut</label>
                <select id="statusFilter"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                    <option value="">Tous</option>
                    <option value="active">Actifs</option>
                    <option value="inactive">Inactifs</option>
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="applyFilters()"
                    class="w-full bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-gray-900 transition">
                    <i class="fas fa-filter mr-2"></i> Filtrer
                </button>
            </div>
        </div>
    </div>

    <!-- Liste des circuits -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Circuit 1 - Tour Historique -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
            <div class="relative h-48 bg-gradient-to-br from-purple-500 to-pink-500">
                <img src="https://images.unsplash.com/photo-1555881400-74d7acaacd8b?w=500" alt="Circuit"
                    class="w-full h-full object-cover opacity-80">
                <div class="absolute top-4 right-4">
                    <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">Actif</span>
                </div>
            </div>
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-2">Tour Historique de Porto-Novo</h3>
                <p class="text-gray-600 text-sm mb-4 line-clamp-2">Découvrez l'histoire et la culture de Porto-Novo avec nos
                    guides experts. Un voyage à travers le temps.</p>

                <div class="space-y-2 mb-4">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-clock text-purple-600 mr-2"></i>
                        <span>Durée: 4 heures</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-dollar-sign text-purple-600 mr-2"></i>
                        <span class="font-bold text-gray-800">15,000 FCFA</span>
                    </div>
                </div>

                <div class="mb-4">
                    <p class="text-xs text-gray-500 font-semibold mb-2">Points d'intérêt:</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">Musée Honmè</span>
                        <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">Grande Mosquée</span>
                        <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">Palais Royal</span>
                        <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">Marché Ouando</span>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Réservations:</span>
                        <span class="font-bold text-gray-800">24 ce mois</span>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <button onclick="editCircuit(1)"
                        class="flex-1 bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition text-sm font-semibold">
                        <i class="fas fa-edit mr-1"></i> Modifier
                    </button>
                    <button onclick="toggleStatus(1)"
                        class="px-4 bg-yellow-500 text-white py-2 rounded-lg hover:bg-yellow-600 transition">
                        <i class="fas fa-power-off"></i>
                    </button>
                    <button onclick="deleteCircuit(1)"
                        class="px-4 bg-red-500 text-white py-2 rounded-lg hover:bg-red-600 transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Circuit 2 - Plages -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
            <div class="relative h-48 bg-gradient-to-br from-blue-500 to-cyan-500">
                <img src="https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=500" alt="Circuit"
                    class="w-full h-full object-cover opacity-80">
                <div class="absolute top-4 right-4">
                    <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">Actif</span>
                </div>
            </div>
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-2">Visite des Plages de Cotonou</h3>
                <p class="text-gray-600 text-sm mb-4 line-clamp-2">Un tour relaxant le long des plus belles plages de
                    Cotonou. Détente et paysages magnifiques garantis.</p>

                <div class="space-y-2 mb-4">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-clock text-purple-600 mr-2"></i>
                        <span>Durée: 3 heures</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-dollar-sign text-purple-600 mr-2"></i>
                        <span class="font-bold text-gray-800">10,000 FCFA</span>
                    </div>
                </div>

                <div class="mb-4">
                    <p class="text-xs text-gray-500 font-semibold mb-2">Points d'intérêt:</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Plage de Fidjrossè</span>
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Plage de la Marina</span>
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Boulevard</span>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Réservations:</span>
                        <span class="font-bold text-gray-800">18 ce mois</span>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <button onclick="editCircuit(2)"
                        class="flex-1 bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition text-sm font-semibold">
                        <i class="fas fa-edit mr-1"></i> Modifier
                    </button>
                    <button onclick="toggleStatus(2)"
                        class="px-4 bg-yellow-500 text-white py-2 rounded-lg hover:bg-yellow-600 transition">
                        <i class="fas fa-power-off"></i>
                    </button>
                    <button onclick="deleteCircuit(2)"
                        class="px-4 bg-red-500 text-white py-2 rounded-lg hover:bg-red-600 transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Carte d'ajout -->
        <div onclick="openCreateModal()"
            class="bg-gradient-to-br from-purple-100 to-purple-50 rounded-lg shadow-md overflow-hidden hover:shadow-xl transition cursor-pointer border-2 border-dashed border-purple-300">
            <div class="h-full flex flex-col items-center justify-center p-6 text-center">
                <div class="w-20 h-20 bg-purple-200 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-plus text-purple-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-purple-800 mb-2">Créer un nouveau circuit</h3>
                <p class="text-purple-600 text-sm">Cliquez pour ajouter un circuit touristique</p>
            </div>
        </div>
    </div>

    <!-- Modal Créer/Modifier Circuit -->
    <div id="circuitModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 overflow-y-auto">
        <div class="bg-white rounded-lg p-8 max-w-2xl w-full m-4 max-h-screen overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-gray-800" id="modalTitle">Nouveau Circuit Touristique</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <form id="circuitForm" action="{{ route('admin.circuits.store') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Nom du circuit *</label>
                        <input type="text" name="name" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                            placeholder="Ex: Tour Historique de Porto-Novo">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Description *</label>
                        <textarea name="description" rows="4" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                            placeholder="Décrivez le circuit en détail..."></textarea>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Prix (FCFA) *</label>
                            <input type="number" name="price" required min="0"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                                placeholder="15000">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Durée (heures) *</label>
                            <input type="number" name="duration" required min="1"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                                placeholder="4">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Points d'intérêt *</label>
                        <div id="locationsContainer" class="space-y-2">
                            <div class="flex space-x-2 location-input">
                                <input type="text" name="locations[]" required
                                    class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                                    placeholder="Nom du lieu">
                                <button type="button" onclick="removeLocation(this)"
                                    class="px-4 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" onclick="addLocation()"
                            class="mt-2 text-purple-600 hover:text-purple-700 font-semibold">
                            <i class="fas fa-plus-circle mr-1"></i> Ajouter un lieu
                        </button>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Image du circuit</label>
                        <input type="file" name="image" accept="image/*"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                        <p class="text-sm text-gray-500 mt-1">Formats acceptés: JPG, PNG. Taille max: 2MB</p>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" checked
                            class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <label for="is_active" class="ml-2 text-gray-700 font-semibold">Circuit actif</label>
                    </div>
                </div>

                <div class="flex space-x-4 mt-6">
                    <button type="button" onclick="closeModal()"
                        class="flex-1 py-3 bg-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-400 transition">
                        Annuler
                    </button>
                    <button type="submit"
                        class="flex-1 py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition">
                        <i class="fas fa-save mr-2"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function openCreateModal() {
                document.getElementById('circuitModal').classList.remove('hidden');
                document.getElementById('circuitModal').classList.add('flex');
                document.getElementById('modalTitle').textContent = 'Nouveau Circuit Touristique';
                document.getElementById('circuitForm').reset();
            }

            function closeModal() {
                document.getElementById('circuitModal').classList.add('hidden');
                document.getElementById('circuitModal').classList.remove('flex');
            }

            function editCircuit(id) {
                openCreateModal();
                document.getElementById('modalTitle').textContent = 'Modifier le Circuit';
                // Charger les données du circuit via AJAX
            }

            function deleteCircuit(id) {
                if (confirm('Êtes-vous sûr de vouloir supprimer ce circuit ? Cette action est irréversible.')) {
                    // Envoyer la requête de suppression
                }
            }

            function toggleStatus(id) {
                // Activer/désactiver le circuit
            }

            function addLocation() {
                const container = document.getElementById('locationsContainer');
                const newInput = document.createElement('div');
                newInput.className = 'flex space-x-2 location-input';
                newInput.innerHTML = `
                <input type="text" name="locations[]" required class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent" placeholder="Nom du lieu">
                <button type="button" onclick="removeLocation(this)" class="px-4 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                    <i class="fas fa-trash"></i>
                </button>
            `;
                container.appendChild(newInput);
            }

            function removeLocation(button) {
                const container = document.getElementById('locationsContainer');
                if (container.children.length > 1) {
                    button.closest('.location-input').remove();
                } else {
                    alert('Vous devez avoir au moins un point d\'intérêt');
                }
            }

            function applyFilters() {
                const search = document.getElementById('searchInput').value;
                const status = document.getElementById('statusFilter').value;
                // Appliquer les filtres
                console.log('Filtres:', {
                    search,
                    status
                });
            }
        </script>
    @endpush
@endsection
