@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Gestion des Conducteurs</h1>
                <p class="text-gray-600">Gérez vos conducteurs et leurs véhicules</p>
            </div>
            <div class="flex items-center space-x-3">
                <div class="relative group">
                    {{-- <button
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center">
                        <i class="fas fa-download mr-2"></i> Importer/Exporter
                    </button> --}}
                    <div
                        class="absolute right-0 mt-0 w-64 bg-white rounded-lg shadow-lg border hidden group-hover:block z-10">
                        <a href="{{ route('admin.drivers.export') }}"
                            class="block px-4 py-2 text-gray-700 hover:bg-blue-50 border-b transition">
                            <i class="fas fa-file-excel mr-2 text-green-600"></i> Exporter en Excel
                        </a>
                        <a href="{{ route('admin.drivers.import.form') }}"
                            class="block px-4 py-2 text-gray-700 hover:bg-blue-50 border-b transition">
                            <i class="fas fa-file-import mr-2 text-blue-600"></i> Importer des Conducteurs
                        </a>
                        <a href="{{ route('admin.drivers.template.download') }}"
                            class="block px-4 py-2 text-gray-700 hover:bg-blue-50 transition">
                            <i class="fas fa-file-csv mr-2 text-orange-600"></i> Télécharger le Template
                        </a>
                    </div>
                </div>
                <a href="{{ route('admin.drivers.create') }}"
                    class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-plus mr-2"></i> Nouveau Conducteur
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="w-16 h-16 flex items-center justify-center rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Conducteurs</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="w-16 h-16 flex items-center justify-center rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Actifs</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['active'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="w-16 h-16 flex items-center justify-center rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-times-circle text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Inactifs</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['inactive'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="w-16 h-16 flex items-center justify-center rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-car text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Disponibles</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['available'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Filtres et Recherche</h3>
        </div>
        <div class="px-6 py-4">
            <form method="GET" action="{{ route('admin.drivers.index') }}" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Rechercher</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Nom, email, téléphone, numéro de permis ou véhicule..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="md:w-48">
                    <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">Statut du compte</label>
                    <select name="is_active" id="is_active"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Tous les statuts</option>
                        <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Actif</option>
                        <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
                <div class="md:w-48">
                    <label for="is_available" class="block text-sm font-medium text-gray-700 mb-1">Disponibilité</label>
                    <select name="is_available" id="is_available"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Toutes les disponibilités</option>
                        <option value="1" {{ request('is_available') == '1' ? 'selected' : '' }}>Disponible
                        </option>
                        <option value="0" {{ request('is_available') == '0' ? 'selected' : '' }}>Indisponible
                        </option>
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit"
                        class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-search mr-2"></i> Rechercher
                    </button>
                    <a href="{{ route('admin.drivers.index') }}"
                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                        <i class="fas fa-times mr-2"></i> Effacer
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Drivers List -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Liste des Conducteurs</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Conducteur</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Véhicule</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Courses</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($drivers as $driver)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode($driver->name) }}"
                                        class="w-10 h-10 rounded-full mr-3">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $driver->name }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ $driver->driver->license_number ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $driver->email ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $driver->phone ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $driver->driver->vehicle_number ?? 'N/A' }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ vehiculeType($driver->driver->vehicle_type) ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col space-y-1">
                                    <span
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $driver->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $driver->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                    <span
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $driver->driver->is_available ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $driver->driver->is_available ? 'Disponible' : 'Indisponible' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="text-center">
                                    <div class="text-lg font-semibold">{{ $driver->driver->total_trips ?? 0 }}</div>
                                    <div class="text-xs text-gray-500">courses</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('admin.drivers.show', $driver) }}"
                                    class="text-blue-600 hover:text-blue-800 mr-3">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.drivers.edit', $driver) }}"
                                    class="text-green-600 hover:text-green-800 mr-3">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="confirmDelete('{{ $driver->id }}', '{{ $driver->name }}')"
                                    class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                Aucun conducteur trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if ($drivers->hasPages())
        <div class="mt-8">
            {{ $drivers->links('pagination::tailwind') }}
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-20">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Supprimer le conducteur</h3>
            <p class="text-gray-600 mb-4" id="deleteMessage">
                Êtes-vous sûr de vouloir supprimer ce conducteur ? Cette action est irréversible.
            </p>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex space-x-4">
                    <button type="button" onclick="closeDeleteModal()"
                        class="flex-1 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                        Annuler
                    </button>
                    <button type="submit"
                        class="flex-1 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        Confirmer la suppression
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function confirmDelete(driverId, driverName) {
                document.getElementById('deleteMessage').textContent =
                    `Êtes-vous sûr de vouloir supprimer ${driverName} ? Cette action est irréversible.`;
                document.getElementById('deleteForm').action = `/admin/drivers/${driverId}`;
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
