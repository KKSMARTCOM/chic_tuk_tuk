@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Gestion des Circuits Touristiques</h1>
                <p class="text-gray-600">Gérez vos circuits et les points d'intérêt</p>
            </div>
            <a href="{{ route('admin.circuits.create') }}"
                class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-plus mr-2"></i> Nouveau Circuit
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="w-16 h-16 flex items-center justify-center rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-map-pin text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Circuits</p>
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
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Recherche et Filtres</h3>
        </div>
        <div class="px-6 py-4">
            <form method="GET" action="{{ route('admin.circuits.index') }}" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Rechercher</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Nom ou description du circuit..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="md:w-48">
                    <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select name="is_active" id="is_active"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Tous les statuts</option>
                        <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Actifs</option>
                        <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactifs</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition">
                        <i class="fas fa-search mr-2"></i>Rechercher
                    </button>
                    <a href="{{ route('admin.circuits.index') }}"
                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition">
                        <i class="fas fa-redo mr-2"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Alert Messages -->
    @if ($message = Session::get('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 flex items-start">
            <i class="fas fa-check-circle text-green-600 mt-1 mr-3"></i>
            <div>
                <p class="font-semibold text-green-800">Succès</p>
                <p class="text-green-700">{{ $message }}</p>
            </div>
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 flex items-start">
            <i class="fas fa-exclamation-circle text-red-600 mt-1 mr-3"></i>
            <div>
                <p class="font-semibold text-red-800">Erreur</p>
                <p class="text-red-700">{{ $message }}</p>
            </div>
        </div>
    @endif

    <!-- Circuits Grid -->
    @if ($circuits->count() > 0)
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach ($circuits as $circuit)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                    <!-- Image -->
                    <div class="relative h-48 bg-gradient-to-br from-purple-500 to-pink-500 overflow-hidden">
                        @if ($circuit->image)
                            <img src="{{ asset('storage/' . $circuit->image) }}" alt="{{ $circuit->name }}"
                                class="w-full h-full object-cover opacity-90">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gray-300">
                                <i class="fas fa-image text-gray-400 text-4xl"></i>
                            </div>
                        @endif
                        <div class="absolute top-4 right-4">
                            <span
                                class="px-3 py-1 rounded-full text-xs font-semibold {{ $circuit->is_active ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                                {{ $circuit->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-2 line-clamp-2">{{ $circuit->name }}</h3>
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $circuit->description }}</p>

                        <!-- Info -->
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-clock text-purple-600 mr-2"></i>
                                <span>{{ $circuit->duration }} heure{{ $circuit->duration > 1 ? 's' : '' }}</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-dollar-sign text-purple-600 mr-2"></i>
                                <span class="font-bold text-gray-800">{{ number_format($circuit->price, 0, ',', ' ') }}
                                    FCFA</span>
                            </div>
                        </div>

                        <!-- Locations -->
                        @if ($circuit->locations && count($circuit->locations) > 0)
                            <div class="mb-4">
                                <p class="text-xs text-gray-500 font-semibold mb-2">Points d'intérêt:</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach (array_slice($circuit->locations, 0, 3) as $location)
                                        <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">
                                            {{ $location }}
                                        </span>
                                    @endforeach
                                    @if (count($circuit->locations) > 3)
                                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-semibold">
                                            +{{ count($circuit->locations) - 3 }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Bookings Stats -->
                        <div class="bg-gray-50 rounded-lg p-3 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Réservations:</span>
                                <span class="font-bold text-gray-800">{{ $circuit->bookings()->count() }}</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <a href="{{ route('admin.circuits.edit', $circuit) }}"
                                class="flex-1 bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition text-sm font-semibold text-center">
                                <i class="fas fa-edit mr-1"></i> Modifier
                            </a>
                            <button type="button"
                                onclick="toggleStatus({{ $circuit->id }}, {{ $circuit->is_active ? 'false' : 'true' }})"
                                class="px-4 {{ $circuit->is_active ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600' }} text-white py-2 rounded-lg transition"
                                title="{{ $circuit->is_active ? 'Désactiver' : 'Activer' }}">
                                <i class="fas fa-power-off"></i>
                            </button>
                            <form action="{{ route('admin.circuits.destroy', $circuit) }}" method="POST" class="inline"
                                onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce circuit ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="px-4 bg-red-500 text-white py-2 rounded-lg hover:bg-red-600 transition"
                                    title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mb-8">
            {{ $circuits->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-600 text-lg mb-4">Aucun circuit touristique trouvé</p>
            {{-- <a href="{{ route('admin.circuits.create') }}"
                class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition inline-block">
                <i class="fas fa-plus mr-2"></i> Créer un circuit
            </a> --}}
        </div>
    @endif

    @push('scripts')
        <script>
            function toggleStatus(circuitId, newStatus) {
                fetch(`/admin/circuits/${circuitId}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            is_active: newStatus
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Une erreur s\'est produite');
                    });
            }
        </script>
    @endpush
@endsection
