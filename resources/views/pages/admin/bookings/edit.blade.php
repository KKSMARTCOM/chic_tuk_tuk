@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Modifier la Réservation</h1>
                <p class="text-gray-600">N° {{ $booking->booking_number }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.bookings.show', $booking) }}"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <!-- Formulaire de modification -->
    <form action="{{ route('admin.bookings.update', $booking) }}" method="POST" class="bg-white rounded-lg shadow-md">
        @csrf
        @method('PUT')

        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Informations de la Réservation</h3>
        </div>

        <div class="px-6 py-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Client -->
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700">Client</label>
                    <select name="user_id" id="user_id"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        <option value="{{ $booking->user_id }}" selected>{{ $booking->user->name ?? 'Client' }}</option>
                        <!-- Ici, vous pouvez ajouter d'autres options si nécessaire -->
                    </select>
                </div>

                <!-- Téléphone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone</label>
                    <input type="text" name="phone" id="phone" value="{{ $booking->phone }}"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                </div>

                <!-- Zone de départ -->
                <div>
                    <label for="from_zone_id" class="block text-sm font-medium text-gray-700">Zone de départ</label>
                    <select name="from_zone_id" id="from_zone_id"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        @foreach ($zones as $zone)
                            <option value="{{ $zone->id }}" {{ $booking->from_zone_id == $zone->id ? 'selected' : '' }}>
                                {{ $zone->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Zone d'arrivée -->
                <div>
                    <label for="to_zone_id" class="block text-sm font-medium text-gray-700">Zone d'arrivée</label>
                    <select name="to_zone_id" id="to_zone_id"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        @foreach ($zones as $zone)
                            <option value="{{ $zone->id }}" {{ $booking->to_zone_id == $zone->id ? 'selected' : '' }}>
                                {{ $zone->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date et heure de départ -->
                <div>
                    <label for="pickup_datetime" class="block text-sm font-medium text-gray-700">Date et heure de
                        départ</label>
                    <input type="datetime-local" name="pickup_datetime" id="pickup_datetime"
                        value="{{ \Carbon\Carbon::parse($booking->pickup_datetime)->format('Y-m-d\TH:i') }}"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                </div>

                <!-- Nombre de passagers -->
                <div>
                    <label for="days" class="block text-sm font-medium text-gray-700">Nombre de jours</label>
                    <input type="number" name="days" id="days" value="{{ $booking->days }}" min="1"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                </div>

                <!-- Prix total -->
                <div>
                    <label for="total_price" class="block text-sm font-medium text-gray-700">Prix total (FCFA)</label>
                    <input type="number" name="total_price" id="total_price" value="{{ $booking->total_price }}"
                        step="0.01"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                </div>

                <!-- Statut -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Statut</label>
                    <select name="status" id="status"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Selectionner le statut</option>
                        <option value="pending" {{ $booking->status == 'pending' ? 'selected' : '' }}>En attente
                        </option>
                        <option value="confirmed" {{ $booking->status == 'confirmed' ? 'selected' : '' }}>Confirmé
                        </option>
                        {{-- <option value="" {{ $booking->status == 'in_progress' ? 'selected' : '' }}>En cours
                    </option>
                    <option value="" {{ $booking->status == 'completed' ? 'selected' : '' }}>Terminé
                    </option> --}}
                        <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>Annulé
                        </option>
                    </select>
                </div>

                <!-- Circuit touristique -->
                <div>
                    <label for="tourist_circuit_id" class="block text-sm font-medium text-gray-700">Circuit
                        touristique</label>
                    <select name="tourist_circuit_id" id="tourist_circuit_id"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Aucun</option>
                        @foreach ($touristCircuits as $circuit)
                            <option value="{{ $circuit->id }}"
                                {{ $booking->tourist_circuit_id == $circuit->id ? 'selected' : '' }}>
                                {{ $circuit->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Instructions spéciales -->
            <div>
                <label for="special_requests" class="block text-sm font-medium text-gray-700">Instructions
                    spéciales</label>
                <textarea name="special_requests" id="special_requests" rows="4"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">{{ $booking->special_requests }}</textarea>
            </div>

            <!-- Boutons -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.bookings.show', $booking) }}"
                    class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                    Annuler
                </a>
                <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-save mr-2"></i> Enregistrer les modifications
                </button>
            </div>
        </div>
    </form>
@endsection
