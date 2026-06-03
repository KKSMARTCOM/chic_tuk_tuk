@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">Enregistrer un Paiement</h1>
                <p class="text-sm md:text-base text-gray-600">Ajouter un nouveau paiement d'un Agent</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.payments.index') }}"
                    class="bg-gray-600 text-white text-nowrap px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-8 max-w-2xl mx-auto">
        <form action="{{ route('admin.payments.store') }}" method="POST">
            @csrf

            <!-- Agent -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Agent <span
                        class="text-red-600">*</span></label>
                <select name="driver_id" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('driver_id') border-red-500 @enderror">
                    <option value="">-- Sélectionner un agent --</option>
                    @foreach ($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                            {{ $driver->user?->name ?? 'N/A' }} (Agent ID: {{ $driver->agent_id }})
                        </option>
                    @endforeach
                </select>
                @error('driver_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Montant -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Montant (FCFA) <span
                        class="text-red-600">*</span></label>
                <input type="number" name="amount" step="0.01" min="0" required placeholder="0.00"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('amount') border-red-500 @enderror"
                    value="{{ old('amount') }}">
                @error('amount')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Moyen de Paiement -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Moyen de Paiement <span
                        class="text-red-600">*</span></label>
                <select name="payment_method" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('payment_method') border-red-500 @enderror">
                    <option value="">-- Sélectionner --</option>
                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Espèces</option>
                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Virement
                        Bancaire</option>
                    <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>Chèque</option>
                    <option value="mobile_money" {{ old('payment_method') == 'mobile_money' ? 'selected' : '' }}>Mobile
                        Money</option>
                    <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Autre</option>
                </select>
                @error('payment_method')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Date de Paiement -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Date de Paiement <span
                        class="text-red-600">*</span></label>
                <input type="date" name="payment_date" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('payment_date') border-red-500 @enderror"
                    value="{{ old('payment_date', now()->format('Y-m-d')) }}">
                @error('payment_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Numéro de Référence -->
            {{-- <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Numéro de Référence</label>
                <input type="text" name="reference_number" placeholder="Numéro de reçu ou référence"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('reference_number') border-red-500 @enderror"
                    value="{{ old('reference_number') }}">
                @error('reference_number')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div> --}}

            <!-- Notes -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Notes</label>
                <textarea name="notes" rows="4" placeholder="Notes supplémentaires..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Buttons -->
            <div class="flex gap-4">
                <button type="submit"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-save mr-2"></i> Enregistrer
                </button>
                <a href="{{ route('admin.payments.index') }}"
                    class="flex-1 bg-gray-400 hover:bg-gray-500 text-white font-semibold py-2 px-4 rounded-lg text-center">
                    <i class="fas fa-times mr-2"></i> Annuler
                </a>
            </div>
        </form>
    </div>
@endsection
