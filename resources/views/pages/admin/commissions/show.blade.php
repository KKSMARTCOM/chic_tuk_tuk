@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">Détails de la Commission</h1>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.commissions.index') }}"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Informations principales -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Commission Info -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Informations de la Commission</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Revenue agent</p>
                            <p class="text-2xl font-bold text-green-600">
                                {{ number_format($commission->booking->driver_earning, 0, ',', ' ') }} FCFA</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Montant</p>
                            <p class="text-2xl font-bold text-green-600">
                                {{ number_format($commission->amount, 0, ',', ' ') }} FCFA</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Date</p>
                            <p class="text-lg text-gray-900">
                                {{ formatDateFr($commission->date) ?? formatDateFr($commission->created_at) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions Sidebar -->
        <div class="space-y-6">
            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Actions</h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <a href="{{ route('admin.drivers.show', $commission->driver->user) }}"
                        class="block w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-center">
                        <i class="fas fa-user mr-2"></i> Voir Agent
                    </a>
                    <a href="{{ route('admin.bookings.show', $commission->booking) }}"
                        class="block w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition text-center">
                        <i class="fas fa-route mr-2"></i> Voir Course
                    </a>
                </div>
            </div>

            <!-- System Info -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Informations Système</h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Créée le</span>
                        <span class="text-gray-900 font-medium">{{ $commission->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Modifiée le</span>
                        <span class="text-gray-900 font-medium">{{ $commission->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
