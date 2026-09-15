@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-lg shadow">
        <!-- Profile Header -->
        <div class="bg-gradient-to-r from-green-600 to-green-800 px-8 py-12 text-white">
            <div class="">
                <h1 class="text-4xl font-bold mb-2">Mon Profil</h1>
                <p class="text-blue-100">Consultez vos informations personnelles</p>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="px-6 py-8">
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-red-600 mt-0.5 mr-3"></i>
                        <div>
                            <h3 class="font-semibold text-red-800 mb-2">Erreurs de validation</h3>
                            <ul class="list-disc list-inside text-sm text-red-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <!-- Left Column - Avatar -->
                <div class="md:col-span-1">
                    <div class="text-center">
                        <div class="mb-4">
                            <img src="{{ $user->profile_photo ? asset('storage/' . $user->profile_photo) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=200&background=0D8ABC&color=fff' }}"
                                class="w-32 h-32 rounded-full mx-auto object-cover shadow-lg border-4 border-blue-100">
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-1">{{ $user->name }}</h2>
                        <p class="text-gray-600 mb-4">
                            @if ($user->isAdmin())
                                Administrateur
                            @elseif ($user->isDriver())
                                Agent
                            @elseif($user->hasRole('proprietaire'))
                                Propriétaire
                            @else
                                Client
                            @endif
                        </p>

                        <!-- Status Badge -->
                        <div
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                        {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <span
                                class="w-2 h-2 rounded-full mr-2 {{ $user->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                            {{ $user->is_active ? 'Actif' : 'Inactif' }}
                        </div>

                        <!-- Edit Profile Button -->
                        <a href="{{ route('settings.settings') }}"
                            class="mt-6 w-full inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200">
                            <i class="fas fa-edit mr-2"></i>Modifier le profil
                        </a>
                    </div>
                </div>

                <!-- Right Column - Information -->
                <div class="md:col-span-3">
                    <div class="space-y-6">
                        <!-- Email -->
                        <div class="border-b pb-4">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-envelope text-blue-600 mr-3"></i>
                                <label class="text-sm font-semibold text-gray-600 uppercase">Email</label>
                            </div>
                            <p class="text-lg text-gray-800 ml-7">{{ $user->email }}</p>
                        </div>

                        <!-- Phone -->
                        <div class="border-b pb-4">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-phone text-blue-600 mr-3"></i>
                                <label class="text-sm font-semibold text-gray-600 uppercase">Téléphone</label>
                            </div>
                            <p class="text-lg text-gray-800 ml-7">{{ $user->phone }}</p>
                        </div>

                        <!-- Address -->
                        <div class="border-b pb-4">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-map-marker-alt text-blue-600 mr-3"></i>
                                <label class="text-sm font-semibold text-gray-600 uppercase">Adresse</label>
                            </div>
                            <p class="text-lg text-gray-800 ml-7">{{ $user->adresse ?? 'Non renseignée' }}</p>
                        </div>

                        <!-- Role -->
                        <div class="border-b pb-4">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-user-circle text-blue-600 mr-3"></i>
                                <label class="text-sm font-semibold text-gray-600 uppercase">Rôle</label>
                            </div>
                            <p class="text-lg text-gray-800 ml-7 capitalize">
                                @if ($user->isAdmin())
                                    <span
                                        class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-semibold">Administrateur</span>
                                @elseif ($user->isDriver())
                                    <span
                                        class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm font-semibold">Chauffeur</span>
                                @else
                                    <span
                                        class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">Client</span>
                                @endif
                            </p>
                        </div>

                        <!-- Member Since -->
                        <div>
                            <div class="flex items-center mb-2">
                                <i class="fas fa-calendar text-blue-600 mr-3"></i>
                                <label class="text-sm font-semibold text-gray-600 uppercase">Membre depuis</label>
                            </div>
                            <p class="text-lg text-gray-800 ml-7">{{ $user->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Actions -->
            @if ($user->isDriver())
                <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-4 pt-8 border-t">
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <h3 class="font-semibold text-gray-800 mb-2 flex items-center">
                            <i class="fas fa-taxi text-blue-600 mr-2"></i>Informations du véhicule
                        </h3>
                        <p class="text-sm text-gray-600">Gérez vos informations de véhicule</p>
                    </div>
                    <div class="p-4 bg-orange-50 rounded-lg">
                        <h3 class="font-semibold text-gray-800 mb-2 flex items-center">
                            <i class="fas fa-file-contract text-orange-600 mr-2"></i>Contrat
                        </h3>
                        <p class="text-sm text-gray-600">Consultez les détails de votre contrat</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            // Animation à l'arrivée
            document.addEventListener('DOMContentLoaded', function() {
                const profileCard = document.querySelector('.bg-gradient-to-r');
                profileCard.style.animation = 'fadeIn 0.5s ease-in-out';
            });
        </script>
    @endpush

@endsection
