@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-lg shadow">
        <!-- Settings Header -->
        <div class="bg-gradient-to-r from-green-600 to-green-800 px-8 py-12 text-white">
            <div class="mx-auto">
                <h1 class="text-4xl font-bold mb-2">Paramètres</h1>
                <p class="text-green-100">Gérez vos informations personnelles et vos préférences</p>
            </div>
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div class="max-w-6xl mx-auto px-6 pt-8">
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg flex items-start">
                    <i class="fas fa-check-circle text-green-600 mt-0.5 mr-3"></i>
                    <div>
                        <h3 class="font-semibold text-green-800">Succès</h3>
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Error Message -->
        @if (session('error'))
            <div class="max-w-6xl mx-auto px-6 pt-8">
                <div class="p-4 bg-red-50 border border-red-200 rounded-lg flex items-start">
                    <i class="fas fa-exclamation-circle text-red-600 mt-0.5 mr-3"></i>
                    <div>
                        <h3 class="font-semibold text-red-800">Erreur</h3>
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="px-6 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Navigation Sidebar -->
                <div class="lg:col-span-1">
                    <div class="space-y-2">
                        <a href="#personal-info"
                            class="settings-nav-link active flex items-center px-4 py-3 rounded-lg bg-green-50 text-green-700 font-semibold transition duration-200">
                            <i class="fas fa-user mr-3"></i>Informations personnelles
                        </a>
                        <a href="#photo"
                            class="settings-nav-link flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                            <i class="fas fa-image mr-3"></i>Photo de profil
                        </a>
                        <a href="#notifications"
                            class="settings-nav-link flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                            <i class="fas fa-bell mr-3"></i>Notifications
                        </a>
                        <a href="#password"
                            class="settings-nav-link flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                            <i class="fas fa-lock mr-3"></i>Sécurité
                        </a>
                    </div>
                </div>

                <!-- Settings Content -->
                <div class="lg:col-span-3 space-y-8">

                    <!-- 1. Personal Information -->
                    <section id="personal-info" class="settings-section bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-6">
                            <i class="fas fa-user text-green-600 text-2xl mr-3"></i>
                            <h2 class="text-2xl font-bold text-gray-800">Informations personnelles</h2>
                        </div>

                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Name -->
                                <div>
                                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-pen mr-1"></i>Nom complet
                                    </label>
                                    <input type="text" id="name" name="name"
                                        value="{{ old('name', $user->name) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 {{ $errors->has('name') ? 'border-red-500' : '' }}"
                                        required>
                                    @error('name')
                                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-envelope mr-1"></i>Email
                                    </label>
                                    <input type="email" id="email" name="email"
                                        value="{{ old('email', $user->email) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 {{ $errors->has('email') ? 'border-red-500' : '' }}"
                                        required>
                                    @error('email')
                                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-phone mr-1"></i>Téléphone
                                    </label>
                                    <input type="tel" id="phone" name="phone"
                                        value="{{ old('phone', $user->phone) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 {{ $errors->has('phone') ? 'border-red-500' : '' }}"
                                        required>
                                    @error('phone')
                                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Address -->
                                <div>
                                    <label for="adresse" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-map-marker-alt mr-1"></i>Adresse
                                    </label>
                                    <input type="text" id="adresse" name="adresse"
                                        value="{{ old('adresse', $user->adresse) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 {{ $errors->has('adresse') ? 'border-red-500' : '' }}">
                                    @error('adresse')
                                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button type="submit"
                                    class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200 flex items-center">
                                    <i class="fas fa-save mr-2"></i>Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </section>

                    <!-- 2. Profile Photo -->
                    <section id="photo" class="settings-section bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-6">
                            <i class="fas fa-image text-blue-600 text-2xl mr-3"></i>
                            <h2 class="text-2xl font-bold text-gray-800">Photo de profil</h2>
                        </div>

                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Current Photo -->
                                <div>
                                    <p class="text-sm font-semibold text-gray-700 mb-3">Photo actuelle</p>
                                    <div class="bg-white rounded-lg p-4 border border-gray-300">
                                        <img src="{{ $user->profile_photo ? asset('storage/' . $user->profile_photo) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=200&background=0D8ABC&color=fff' }}"
                                            alt="{{ $user->name }}" class="w-full h-48 object-cover rounded-lg">
                                    </div>
                                </div>

                                <!-- Upload New Photo -->
                                <div>
                                    <p class="text-sm font-semibold text-gray-700 mb-3">Changer la photo</p>
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-green-500 transition duration-200 cursor-pointer bg-white"
                                        onclick="document.getElementById('profile_photo').click()">
                                        <input type="file" id="profile_photo" name="profile_photo" class="hidden"
                                            accept="image/*" onchange="previewImage(this)">
                                        <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-2 block"></i>
                                        <p class="text-gray-600 font-semibold">Cliquez ou glissez une image</p>
                                        <p class="text-gray-500 text-xs mt-1">PNG, JPG, GIF jusqu'à 2MB</p>
                                    </div>
                                    @error('profile_photo')
                                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Hidden full-name and email for form submission -->
                            <input type="hidden" name="name" value="{{ $user->name }}">
                            <input type="hidden" name="email" value="{{ $user->email }}">
                            <input type="hidden" name="phone" value="{{ $user->phone }}">
                            <input type="hidden" name="adresse" value="{{ $user->adresse }}">

                            <div class="mt-6 flex justify-end">
                                <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200 flex items-center">
                                    <i class="fas fa-save mr-2"></i>Mettre à jour la photo
                                </button>
                            </div>
                        </form>
                    </section>

                    <!-- 3. Notifications -->
                    <section id="notifications" class="settings-section bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-6">
                            <i class="fas fa-bell text-yellow-600 text-2xl mr-3"></i>
                            <h2 class="text-2xl font-bold text-gray-800">Préférences de notification</h2>
                        </div>

                        <form action="{{ route('settings.notifications') }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <!-- Email Notifications -->
                                <div
                                    class="bg-white rounded-lg p-4 border border-gray-200 hover:border-gray-300 transition duration-200 flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-envelope text-yellow-600 mr-3 text-lg"></i>
                                        <div>
                                            <p class="font-semibold text-gray-800">Notifications par email</p>
                                            <p class="text-sm text-gray-600">Recevez des emails pour les mises à jour
                                                importantes</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="email_notifications" name="email_notifications"
                                            value="1"
                                            {{ old('email_notifications', isset($user->notification_preferences['email_notifications']) && $user->notification_preferences['email_notifications']) ? 'checked' : '' }}
                                            class="w-5 h-5 text-yellow-600 rounded focus:ring-2 focus:ring-yellow-500 cursor-pointer">
                                    </div>
                                </div>

                                <!-- Push Notifications -->
                                <div
                                    class="bg-white rounded-lg p-4 border border-gray-200 hover:border-gray-300 transition duration-200 flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-bell text-purple-600 mr-3 text-lg"></i>
                                        <div>
                                            <p class="font-semibold text-gray-800">Notifications push web</p>
                                            <p class="text-sm text-gray-600">Recevez des notifications en temps réel dans
                                                votre navigateur</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="push_notifications" name="push_notifications"
                                            value="1"
                                            {{ old('push_notifications', isset($user->notification_preferences['push_notifications']) && $user->notification_preferences['push_notifications']) ? 'checked' : '' }}
                                            class="w-5 h-5 text-purple-600 rounded focus:ring-2 focus:ring-purple-500 cursor-pointer">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button type="submit"
                                    class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200 flex items-center">
                                    <i class="fas fa-save mr-2"></i>Enregistrer les préférences
                                </button>
                            </div>
                        </form>
                    </section>

                    <!-- 4. Security / Password Change -->
                    <section id="password" class="settings-section bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-6">
                            <i class="fas fa-lock text-red-600 text-2xl mr-3"></i>
                            <h2 class="text-2xl font-bold text-gray-800">Sécurité</h2>
                        </div>

                        <form action="{{ route('settings.password') }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <!-- Current Password -->
                                <div>
                                    <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-key mr-1"></i>Mot de passe actuel
                                    </label>
                                    <input type="password" id="current_password" name="current_password"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 {{ $errors->has('current_password') ? 'border-red-500' : '' }}"
                                        required>
                                    @error('current_password')
                                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- New Password -->
                                <div>
                                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-lock mr-1"></i>Nouveau mot de passe
                                    </label>
                                    <input type="password" id="password" name="password"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 {{ $errors->has('password') ? 'border-red-500' : '' }}"
                                        required>
                                    <p class="text-xs text-gray-500 mt-1">Minimum 8 caractères</p>
                                    @error('password')
                                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <label for="password_confirmation"
                                        class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-lock mr-1"></i>Confirmer le mot de passe
                                    </label>
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                        required>
                                    @error('password_confirmation')
                                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button type="submit"
                                    class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200 flex items-center">
                                    <i class="fas fa-save mr-2"></i>Modifier le mot de passe
                                </button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Navigation between sections
            document.querySelectorAll('.settings-nav-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Remove active class from all links
                    document.querySelectorAll('.settings-nav-link').forEach(l => {
                        l.classList.remove('bg-green-50', 'text-green-700');
                        l.classList.add('text-gray-700', 'hover:bg-gray-50');
                    });

                    // Add active class to clicked link
                    this.classList.add('bg-green-50', 'text-green-700');
                    this.classList.remove('text-gray-700', 'hover:bg-gray-50');

                    // Smooth scroll to section
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Preview image before upload
            function previewImage(input) {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.querySelector('.border-dashed img');
                        if (!preview) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'w-full h-48 object-cover rounded-lg';
                            const container = input.parentElement;
                            container.innerHTML = '';
                            container.appendChild(img);
                        }
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }

            // Drag and drop file upload
            const dropZone = document.querySelector('.border-dashed');
            if (dropZone) {
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, preventDefaults, false);
                });

                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, highlight, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, unhighlight, false);
                });

                function highlight(e) {
                    dropZone.classList.add('border-green-500', 'bg-green-50');
                }

                function unhighlight(e) {
                    dropZone.classList.remove('border-green-500', 'bg-green-50');
                }

                dropZone.addEventListener('drop', handleDrop, false);

                function handleDrop(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    document.getElementById('profile_photo').files = files;
                    previewImage(document.getElementById('profile_photo'));
                }
            }
        </script>
    @endpush
@endsection
