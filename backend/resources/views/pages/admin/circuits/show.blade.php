@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $circuit->name }}</h1>
                <div class="flex items-center gap-3 mt-2">
                    <span
                        class="px-3 py-1 rounded-full text-xs font-semibold {{ $circuit->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $circuit->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                    <span class="text-gray-600">Créé le {{ $circuit->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.circuits.edit', $circuit) }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-edit mr-2"></i> Modifier
                </a>
                <a href="{{ route('admin.circuits.index') }}"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Image and Info -->
        <div class="lg:col-span-2">
            <!-- Image -->
            @if ($circuit->image)
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <img src="{{ asset('storage/' . $circuit->image) }}" alt="{{ $circuit->name }}"
                        class="w-full h-96 object-cover">
                </div>
            @endif

            <!-- Description -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Description</h3>
                <p class="text-gray-600 leading-relaxed">{{ $circuit->description }}</p>
            </div>

            <!-- Points d'intérêt -->
            @if ($circuit->locations && count($circuit->locations) > 0)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Points d'intérêt</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach ($circuit->locations as $location)
                            <div class="flex items-center p-3 bg-purple-50 rounded-lg border border-purple-200">
                                <i class="fas fa-map-pin text-purple-600 mr-3"></i>
                                <span class="text-gray-700">{{ $location }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column - Details -->
        <div>
            <!-- Tarification -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Tarification</h3>

                <div class="space-y-4">
                    <div class="border-b border-gray-200 pb-4">
                        <p class="text-sm text-gray-600">Prix</p>
                        <p class="text-3xl font-bold text-purple-600">{{ number_format($circuit->price, 0, ',', ' ') }}</p>
                        <p class="text-xs text-gray-500">FCFA</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600">Durée</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $circuit->duration }}
                            heure{{ $circuit->duration > 1 ? 's' : '' }}</p>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Statistiques</h3>

                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                        <span class="text-gray-700">Réservations</span>
                        <span class="text-2xl font-bold text-blue-600">{{ $circuit->bookings()->count() }}</span>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                        <span class="text-gray-700">Complétées</span>
                        <span
                            class="text-2xl font-bold text-green-600">{{ $circuit->bookings()->where('status', 'completed')->count() }}</span>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                        <span class="text-gray-700">En cours</span>
                        <span
                            class="text-2xl font-bold text-orange-600">{{ $circuit->bookings()->where('status', 'in_progress')->count() }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Actions</h3>

                <div class="space-y-2">
                    <a href="{{ route('admin.circuits.edit', $circuit) }}"
                        class="w-full block bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition text-center font-semibold">
                        <i class="fas fa-edit mr-2"></i>Modifier
                    </a>

                    <button type="button" onclick="toggleStatus()"
                        class="w-full {{ $circuit->is_active ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700' }} text-white py-2 rounded-lg transition font-semibold">
                        <i class="fas fa-power-off mr-2"></i>{{ $circuit->is_active ? 'Désactiver' : 'Activer' }}
                    </button>

                    <form action="{{ route('admin.circuits.destroy', $circuit) }}" method="POST"
                        onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce circuit ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition font-semibold">
                            <i class="fas fa-trash mr-2"></i>Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function toggleStatus() {
                fetch(`/admin/circuits/{{ $circuit->id }}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            is_active: {{ $circuit->is_active ? 'false' : 'true' }}
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
