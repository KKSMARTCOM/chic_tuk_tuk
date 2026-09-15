@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h1 class="text-lg md:text-2xl font-bold text-gray-900">Gestion des Rôles et Permissions</h1>
                <p class="text-sm md:text-base text-gray-600">Gérez les rôles et permissions des utilisateurs</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-8">
            <div class="flex space-x-8">
                <button type="button"
                    class="tab-button active py-4 px-1 border-b-2 border-blue-500 font-medium text-blue-600" data-tab="roles">
                    Rôles
                </button>
                <button type="button"
                    class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-gray-600 hover:text-gray-900 hover:border-gray-300"
                    data-tab="permissions">
                    Permissions
                </button>
            </div>
        </div>

        <!-- Roles Tab -->
        <div id="roles-tab" class="tab-content">
            <div class="mb-6 flex justify-between items-center bg-white px-6 py-4 rounded-lg">
                <h2 class="text-xl font-semibold text-gray-900">Rôles</h2>
                <button type="button" onclick="openRoleModal()"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fa fa-plus mr-2"></i>
                    Nouveau Rôle
                </button>
            </div>

            @if ($roles->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($roles as $role)
                        <div class="bg-white rounded-lg shadow hover:shadow-lg transition border border-gray-200">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $role->label }}</h3>
                                        <p class="text-xs text-gray-900">{{ $role->description }}</p>
                                    </div>
                                    @if (!in_array($role->name, ['admin', 'driver', 'client', 'proprietaire']))
                                        <button type="button"
                                            onclick="openDeleteRoleModal('{{ $role->id }}', '{{ $role->name }}')"
                                            class="text-red-600 hover:text-red-800 transition">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    @endif
                                </div>

                                <div class="space-y-3 mb-4">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Permissions:</span>
                                        <span
                                            class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                                            {{ $role->permissions_count }}
                                        </span>
                                    </div>
                                    {{-- <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Utilisateurs:</span>
                                        <span
                                            class="inline-block bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-medium">
                                            {{ $role->users_count }}
                                        </span>
                                    </div> --}}
                                </div>

                                <div class="flex gap-2 pt-4 border-t border-gray-200">
                                    <button type="button" onclick="openRoleModal('{{ $role->id }}')"
                                        class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition">
                                        <i class="fa fa-edit mr-2"></i>
                                        Modifier
                                    </button>
                                    <a href="{{ route('admin.roles.show', $role) }}"
                                        class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-gray-50 text-gray-600 rounded hover:bg-gray-100 transition">
                                        <i class="fa fa-eye mr-2"></i>
                                        Détails
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg">
                    Aucun rôle trouvé.
                </div>
            @endif
        </div>

        <!-- Permissions Tab -->
        <div id="permissions-tab" class="tab-content hidden">
            <div class="mb-6 flex justify-between items-center bg-white px-6 py-4 rounded-lg">
                <h2 class="text-xl font-semibold text-gray-900">Permissions</h2>
                <button type="button" onclick="openPermissionModal()"
                    class="inline-flex  text-nowrap items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fa fa-plus mr-2"></i>
                    Nouvelle Permission
                </button>
            </div>

            @if ($permissions->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($permissions as $permission)
                        <div class="bg-white rounded-lg shadow hover:shadow-lg transition border border-gray-200">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 break-words">{{ $permission->label }}
                                        </h3>
                                        <p class="text-xs text-gray-900 break-words">{{ $permission->description }}</p>
                                    </div>
                                </div>

                                <div class="flex gap-2 pt-4 border-t border-gray-200">
                                    <button type="button" onclick="openPermissionModal('{{ $permission->id }}')"
                                        class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-green-50 text-green-600 rounded hover:bg-green-100 transition">
                                        <i class="fa fa-edit mr-2"></i>
                                        Modifier
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    Aucune permission trouvée.
                </div>
            @endif
        </div>
    </div>

    <!-- Role Modal -->
    <div id="roleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-lg max-w-xl w-full max-h-screen overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b border-gray-200 sticky top-0 bg-white">
                <h3 class="text-lg font-semibold text-gray-900" id="roleModalTitle">Créer un Rôle</h3>
                <button type="button" onclick="closeRoleModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form id="roleForm" method="POST" action="{{ route('admin.roles.store') }}" class="p-6 space-y-4">
                @csrf
                <input type="hidden" id="roleMethod" name="_method" value="POST">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom du Rôle</label>
                    <input type="text" id="roleName" name="label"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea type="text" id="roleDescription" name="description"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                    <div class="space-y-2 max-h-60 overflow-y-auto border border-gray-200 rounded-lg p-4">
                        @foreach ($permissions as $permission)
                            <label class="flex items-center">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                    class="permission-checkbox w-4 h-4 text-blue-600 rounded">
                                <span class="ml-2 text-sm text-gray-700 capitalize">{{ $permission->label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-3 pt-6 border-t border-gray-200">
                    <button type="button" onclick="closeRoleModal()"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Annuler
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Permission Modal -->
    <div id="permissionModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-lg max-w-xl w-full">
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900" id="permissionModalTitle">Créer une Permission</h3>
                <button type="button" onclick="closePermissionModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form id="permissionForm" method="POST" action="{{ route('admin.permissions.store') }}"
                class="p-6 space-y-4">
                @csrf
                <input type="hidden" id="permissionMethod" name="_method" value="POST">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom de la Permission</label>
                    <input type="text" id="permissionName" name="label"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        placeholder="ex: view-users" readonly required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea type="text" id="permissionDescription" name="description"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                </div>

                <div class="flex gap-3 pt-6 border-t border-gray-200">
                    <button type="button" onclick="closePermissionModal()"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Annuler
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-lg max-w-sm w-full">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-center text-gray-900 mb-2">Confirmer la suppression</h3>
                <p class="text-center text-gray-600 mb-6">
                    Êtes-vous sûr de vouloir supprimer <span id="deleteItemName" class="font-semibold"></span>? Cette
                    action est irréversible.
                </p>

                <form id="deleteForm" method="POST" class="flex gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="closeDeleteModal()"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Annuler
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Tab switching
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                const tabName = this.dataset.tab;

                // Update button states
                document.querySelectorAll('.tab-button').forEach(b => {
                    b.classList.remove('border-blue-500', 'text-blue-600');
                    b.classList.add('border-transparent', 'text-gray-600');
                });
                this.classList.add('border-blue-500', 'text-blue-600');
                this.classList.remove('border-transparent', 'text-gray-600');

                // Update tab content
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                });
                document.getElementById(tabName + '-tab').classList.remove('hidden');
            });
        });

        // Role Modal functions
        function openRoleModal(roleId = null) {
            const modal = document.getElementById('roleModal');
            const form = document.getElementById('roleForm');
            const title = document.getElementById('roleModalTitle');
            const methodField = document.getElementById('roleMethod');

            if (roleId) {
                title.textContent = 'Modifier le Rôle';
                methodField.value = 'PUT';
                form.action = `/admin/roles/${roleId}`;

                // Load role data
                fetch(`/admin/roles/${roleId}/data`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('roleName').value = data.label;
                        document.getElementById('roleDescription').value = data.description;
                        document.querySelectorAll('.permission-checkbox').forEach(cb => {
                            cb.checked = data.permissions.includes(parseInt(cb.value));
                        });
                    })
                    .catch(err => console.error('Error loading role:', err));
            } else {
                title.textContent = 'Créer un Rôle';
                methodField.value = 'POST';
                form.action = '{{ route('admin.roles.store') }}';
                document.getElementById('roleName').value = '';
                document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
            }

            modal.classList.remove('hidden');
        }

        function closeRoleModal() {
            document.getElementById('roleModal').classList.add('hidden');
        }

        // Permission Modal functions
        function openPermissionModal(permissionId = null) {
            const modal = document.getElementById('permissionModal');
            const form = document.getElementById('permissionForm');
            const title = document.getElementById('permissionModalTitle');
            const methodField = document.getElementById('permissionMethod');

            if (permissionId) {
                title.textContent = 'Modifier la Permission';
                methodField.value = 'PUT';
                form.action = `/admin/permissions/${permissionId}`;

                // Load permission data
                fetch(`/admin/permissions/${permissionId}/data`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('permissionName').value = data.label;
                        document.getElementById('permissionDescription').value = data.description;
                    })
                    .catch(err => console.error('Error loading permission:', err));
            } else {
                title.textContent = 'Créer une Permission';
                methodField.value = 'POST';
                form.action = '{{ route('admin.permissions.store') }}';
                document.getElementById('permissionName').value = '';
            }

            modal.classList.remove('hidden');
        }

        function closePermissionModal() {
            document.getElementById('permissionModal').classList.add('hidden');
        }

        // Delete functions
        function openDeleteRoleModal(roleId, roleName) {
            const modal = document.getElementById('deleteModal');
            const form = document.getElementById('deleteForm');
            document.getElementById('deleteItemName').textContent = roleName;
            form.action = `/admin/roles/${roleId}`;
            modal.classList.remove('hidden');
        }

        function openDeletePermissionModal(permissionId, permissionName) {
            const modal = document.getElementById('deleteModal');
            const form = document.getElementById('deleteForm');
            document.getElementById('deleteItemName').textContent = permissionName;
            form.action = `/admin/permissions/${permissionId}`;
            modal.classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>
@endsection
