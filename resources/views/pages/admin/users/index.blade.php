@extends('layouts.app')

@section('content')

    {{-- Header --}}
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">Gestion des Utilisateurs</h1>
                <p class="text-xs md:text-base text-gray-600">Gérez les comptes administrateurs et clients</p>
            </div>
            <button onclick="openCreateModal()"
                class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-plus mr-2"></i> Nouvel Utilisateur
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        @foreach ([['label' => 'Total', 'value' => $stats['total'], 'icon' => 'fa-users', 'color' => 'blue'], ['label' => 'Actifs', 'value' => $stats['active'], 'icon' => 'fa-check-circle', 'color' => 'green'], ['label' => 'Inactifs', 'value' => $stats['inactive'], 'icon' => 'fa-times-circle', 'color' => 'red'], ['label' => 'Admins', 'value' => $stats['admins'], 'icon' => 'fa-user-shield', 'color' => 'purple'], ['label' => 'Clients', 'value' => $stats['clients'], 'icon' => 'fa-user', 'color' => 'orange']] as $stat)
            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 flex items-center justify-center rounded-full bg-{{ $stat['color'] }}-100 text-{{ $stat['color'] }}-600">
                        <i class="fas {{ $stat['icon'] }}"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs text-gray-500">{{ $stat['label'] }}</p>
                        <p class="text-xl font-bold text-gray-900">{{ $stat['value'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="px-6 py-4">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-48">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Nom, email, téléphone..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="w-40">
                    <select name="profil"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Tous les profils</option>
                        <option value="admin" {{ request('profil') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="client" {{ request('profil') === 'client' ? 'selected' : '' }}>Client</option>
                    </select>
                </div>
                <div class="w-40">
                    <select name="is_active"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Tous les statuts</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Actif</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-search mr-1"></i> Rechercher
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                        <i class="fas fa-times mr-1"></i> Effacer
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Liste des Utilisateurs</h3>
        </div>
        @if ($users->count() > 0)
            <div class="overflow-x-auto p-4">
                <table class="min-w-full divide-y divide-gray-200 display" id="datatable1">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Ajouter le </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Utilisateur</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Profil / Rôle</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($users as $user)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ formatDateTimeFr($user->created_at) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}"
                                            class="w-10 h-10 rounded-full mr-3">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                            <p class="text-xs text-gray-400">{{ $user->adresse ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="text-sm text-gray-900">{{ $user->email ?? '—' }}</p>
                                    <p class="text-sm text-gray-500">{{ $user->phone }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full
                                {{ $user->profil === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ ucfirst($user->profil) }}
                                    </span>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach ($user->roles as $role)
                                            <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">
                                                {{ $role->label ?? $role->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full
                                {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center gap-3">

                                        <button
                                            onclick="openEditModal({{ $user->toJson() }}, {{ $user->roles->pluck('name')->toJson() }})"
                                            class="text-green-600 hover:text-green-800" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="openPasswordModal('{{ $user->id }}')"
                                            class="text-blue-600 hover:text-blue-800" title="Modifier le mot de passe">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="{{ $user->is_active ? 'text-orange-500 hover:text-orange-700' : 'text-green-500 hover:text-green-700' }}"
                                                title="{{ $user->is_active ? 'Désactiver' : 'Activer' }}">
                                                <i
                                                    class="fas {{ $user->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                            </button>
                                        </form>
                                        @if ($user->id !== auth()->id())
                                            <button title="Supprimer"
                                                onclick="openDeleteModal('{{ $user->id }}', '{{ $user->name }}')"
                                                class="text-red-600 hover:text-red-800" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">Aucun utilisateur trouvé.</p>
            </div>
        @endif
    </div>

    {{-- ===== MODAL CRÉATION ===== --}}
    <div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-30">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">Nouvel Utilisateur</h3>
                <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="phone" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Profil <span
                                class="text-red-500">*</span></label>
                        <select name="profil" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="client">Client</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rôle</label>
                        <select name="role"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">— Par défaut (profil) —</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->label ?? $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                        <input type="text" name="adresse"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe <span
                                class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <input type="text" name="password" id="create_password" required
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 font-mono">
                            <button type="button" onclick="generatePassword()"
                                class="px-3 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition text-sm font-semibold whitespace-nowrap">
                                <i class="fas fa-refresh mr-1"></i> Générer
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Le mot de passe sera affiché une seule fois — notez-le.</p>
                    </div>
                    <div class="md:col-span-2 flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="create_is_active" value="1" checked
                            class="w-4 h-4 accent-purple-600">
                        <label for="create_is_active" class="text-sm text-gray-700">Compte actif</label>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeCreateModal()"
                        class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Annuler
                    </button>
                    <button type="submit"
                        class="flex-1 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== MODAL MODIFICATION ===== --}}
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-30">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">Modifier l'Utilisateur</h3>
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
                            <option value="client">Client</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rôle Spatie</label>
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

    {{-- ===== MODAL MOT DE PASSE ===== --}}
    <div id="passwordModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-30">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">Modifier le mot de passe</h3>
                <button onclick="closePasswordModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="passwordForm" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe <span
                            class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required minlength="8"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">

                        <button type="button" onclick="togglePassword('password', this)"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700">

                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer <span
                            class="text-red-500">*</span></label>

                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">

                        <button type="button" onclick="togglePassword('password_confirmation', this)"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700">

                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closePasswordModal()"
                        class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Annuler
                    </button>
                    <button type="submit"
                        class="flex-1 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== MODAL SUPPRESSION ===== --}}
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-30">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-800">Supprimer l'utilisateur</h3>
            </div>
            <div class="p-6">
                <p class="text-gray-600 mb-6" id="deleteMessage">Êtes-vous sûr de vouloir supprimer cet utilisateur ?</p>
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

    @push('scripts')
        <script>
            $("#datatable1").DataTable({
                order: [
                    [0, "desc"]
                ],
                columnDefs: [{
                    targets: 0,
                    searchable: false,
                }, ],
                language: {
                    processing: "Traitement en cours...",
                    search: "Rechercher : ",
                    lengthMenu: "Afficher _MENU_ éléments",
                    info: "Affichage de _START_ à _END_ sur _TOTAL_ ",
                    infoEmpty: "Affichage de 0 à 0 sur 0",
                    infoFiltered: "(filtré de _MAX_ éléments au total)",
                    loadingRecords: "Chargement en cours...",
                    zeroRecords: "Aucun élément à afficher",
                    emptyTable: "Aucune donnée disponible dans le tableau",
                },
                // Callback pour appliquer select2 après init
                initComplete: function() {
                    if (typeof $.fn.select2 !== "undefined") {
                        $(".dataTables_length select").select2({
                            minimumResultsForSearch: Infinity,
                        });
                    }
                },
            })

            // ===== CRÉATION =====
            function openCreateModal() {
                generatePassword();
                document.getElementById('createModal').classList.remove('hidden');
                document.getElementById('createModal').classList.add('flex');
            }

            function closeCreateModal() {
                document.getElementById('createModal').classList.add('hidden');
                document.getElementById('createModal').classList.remove('flex');
            }

            // Génère un mot de passe via AJAX
            async function generatePassword() {
                const res = await fetch('{{ route('admin.users.generate-password') }}');
                const data = await res.json();
                document.getElementById('create_password').value = data.password;
            }

            // ===== MODIFICATION =====
            function openEditModal(user, userRoles) {
                document.getElementById('edit_name').value = user.name;
                document.getElementById('edit_email').value = user.email ?? '';
                document.getElementById('edit_phone').value = user.phone;
                document.getElementById('edit_profil').value = user.profil;
                document.getElementById('edit_adresse').value = user.adresse ?? '';
                document.getElementById('edit_is_active').checked = user.is_active;

                // Rôle Spatie — premier rôle de l'utilisateur
                const roleSelect = document.getElementById('edit_role');
                roleSelect.value = userRoles.length > 0 ? userRoles[0] : '';

                document.getElementById('editForm').action = `/admin/users/${user.id}`;

                document.getElementById('editModal').classList.remove('hidden');
                document.getElementById('editModal').classList.add('flex');
            }

            function closeEditModal() {
                document.getElementById('editModal').classList.add('hidden');
                document.getElementById('editModal').classList.remove('flex');
            }

            // ===== MOT DE PASSE =====
            function openPasswordModal(userId) {
                document.getElementById('passwordForm').action = `/admin/users/${userId}/update-password`;
                document.getElementById('passwordModal').classList.remove('hidden');
                document.getElementById('passwordModal').classList.add('flex');
            }

            function closePasswordModal() {
                document.getElementById('passwordModal').classList.add('hidden');
                document.getElementById('passwordModal').classList.remove('flex');
            }

            function togglePassword(inputId, button) {
                const input = document.getElementById(inputId);
                const icon = button.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';

                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';

                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }

            // ===== SUPPRESSION =====
            function openDeleteModal(userId, userName) {
                document.getElementById('deleteMessage').textContent =
                    `Êtes-vous sûr de vouloir supprimer ${userName} ? Cette action est irréversible.`;
                document.getElementById('deleteForm').action = `/admin/users/${userId}`;
                document.getElementById('deleteModal').classList.remove('hidden');
                document.getElementById('deleteModal').classList.add('flex');
            }

            function closeDeleteModal() {
                document.getElementById('deleteModal').classList.add('hidden');
                document.getElementById('deleteModal').classList.remove('flex');
            }
        </script>
    @endpush

@endsection
