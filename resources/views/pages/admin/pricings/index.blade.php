@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Gestion des Tarifs</h1>
                <p class="text-gray-600">Gérez les tarifs de vos trajets</p>
            </div>
            <a href="{{ route('admin.pricing.create') }}"
                class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-plus mr-2"></i> Nouveau Tarif
            </a>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Recherche et Filtres</h3>
        </div>
        <div class="px-6 py-4">
            <form method="GET" action="{{ route('admin.pricing.index') }}" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Rechercher par zone</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Nom de la zone de départ ou destination..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition">
                        <i class="fas fa-search mr-2"></i>Rechercher
                    </button>
                    <a href="{{ route('admin.pricing.index') }}"
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

    <!-- Table of Pricings -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Liste des tarifs ({{ $pricings->total() }})</h3>
        </div>

        @if ($pricings->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Zone de départ
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Zone de destination
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Prix de base
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Prix par km
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Durée estimée
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($pricings as $pricing)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        {{ $pricing->fromZone->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        {{ $pricing->toZone->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    <i
                                        class="fas fa-coins text-green-600 mr-1"></i>{{ number_format($pricing->base_price, 2, ',', ' ') }}
                                    FCFA
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    <i
                                        class="fas fa-coins text-green-600 mr-1"></i>{{ number_format($pricing->price_per_km, 2, ',', ' ') }}
                                    FCFA
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <i class="fas fa-clock text-blue-600 mr-1"></i>{{ $pricing->estimated_duration }} min
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex gap-2">
                                    <a href="{{ route('admin.pricing.edit', $pricing) }}"
                                        class="text-blue-600 hover:text-blue-900 transition" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.pricing.destroy', $pricing) }}" method="POST"
                                        class="inline-block"
                                        onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce tarif ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 transition"
                                            title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $pricings->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-600 text-lg mb-4">Aucun tarif trouvé</p>
                <a href="{{ route('admin.pricing.create') }}"
                    class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition inline-block">
                    <i class="fas fa-plus mr-2"></i> Ajouter un tarif
                </a>
            </div>
        @endif
    </div>
@endsection
