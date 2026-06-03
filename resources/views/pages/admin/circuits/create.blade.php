@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Ajouter un Circuit Touristique</h1>
                <p class="text-gray-600">Créez un nouveau circuit pour vos clients</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.circuits.index') }}"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.circuits.store') }}" method="POST" enctype="multipart/form-data"
        class="bg-white rounded-lg shadow-md">
        @csrf

        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Informations du Circuit</h3>
        </div>

        <div class="px-6 py-6 space-y-6">
            <!-- Nom et Description -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nom du circuit <span
                        class="text-red-600">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('name') border-red-500 @enderror"
                    placeholder="Ex: Tour Historique de Porto-Novo">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description <span
                        class="text-red-600">*</span></label>
                <textarea name="description" id="description" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('description') border-red-500 @enderror"
                    placeholder="Décrivez le circuit en détail...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tarification et Durée -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Prix (FCFA) <span
                            class="text-red-600">*</span></label>
                    <div class="relative">
                        <input type="number" name="price" id="price" value="{{ old('price') }}" step="0.01"
                            min="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('price') border-red-500 @enderror"
                            placeholder="15000">
                    </div>
                    @error('price')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">Durée (heures) <span
                            class="text-red-600">*</span></label>
                    <div class="relative">
                        <input type="number" name="duration" id="duration" value="{{ old('duration') }}" min="1"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('duration') border-red-500 @enderror"
                            placeholder="4">
                        <span class="absolute right-3 top-2 text-gray-500">h</span>
                    </div>
                    @error('duration')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Points d'intérêt -->
            <div class="border-t border-gray-200 pt-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Points d'intérêt</h4>

                <div id="locationsContainer" class="space-y-2 mb-4">
                    @php
                        $locations = old('locations', [null]);
                    @endphp
                    @foreach ($locations as $location)
                        <div class="flex gap-2 location-input">
                            <input type="text" name="locations[]" value="{{ $location }}"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 @error('locations.*') border-red-500 @enderror"
                                placeholder="Nom du lieu d'intérêt">
                            <button type="button" onclick="removeLocation(this)"
                                class="px-4 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
                @error('locations')
                    <p class="text-sm text-red-600 mb-2">{{ $message }}</p>
                @enderror

                <button type="button" onclick="addLocation()" class="text-purple-600 hover:text-purple-700 font-semibold">
                    <i class="fas fa-plus-circle mr-1"></i> Ajouter un lieu
                </button>
            </div>

            <!-- Image -->
            <div class="border-t border-gray-200 pt-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Image du circuit</h4>

                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Télécharger une image</label>
                    <div class="flex items-center justify-center px-6 py-6 border-2 border-dashed border-gray-300 rounded-md cursor-pointer hover:border-purple-500 transition"
                        id="imageDropZone">
                        <div class="text-center">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600 font-medium">Cliquez ou déposez une image</p>
                            <p class="text-sm text-gray-500">JPG, PNG, GIF (Max 2MB)</p>
                        </div>
                        <input type="file" name="image" id="image" accept="image/*"
                            class="hidden @error('image') border-red-500 @enderror">
                    </div>
                    @error('image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image Preview -->
                <div id="imagePreview" class="mt-4 hidden">
                    <p class="text-sm font-medium text-gray-700 mb-2">Aperçu:</p>
                    <img id="previewImg" src="" alt="Aperçu" class="h-40 rounded-md border border-gray-300">
                    <button type="button" onclick="clearImage()"
                        class="mt-2 text-sm text-red-600 hover:text-red-700 font-semibold">
                        <i class="fas fa-trash mr-1"></i>Supprimer l'image
                    </button>
                </div>
            </div>

            <!-- Statut -->
            <div class="border-t border-gray-200 pt-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Paramètres</h4>

                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked
                        class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                    <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">Circuit actif</label>
                </div>
                <p class="text-sm text-gray-500 mt-2">Un circuit inactif n'apparaîtra pas dans les réservations client</p>
            </div>
        </div>

        <!-- Actions -->
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
            <a href="{{ route('admin.circuits.index') }}"
                class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition font-medium">
                <i class="fas fa-times mr-2"></i>Annuler
            </a>
            <button type="submit"
                class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition font-medium">
                <i class="fas fa-check mr-2"></i>Créer le Circuit
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            const imageDropZone = document.getElementById('imageDropZone');
            const imageInput = document.getElementById('image');
            const imagePreview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');

            // Click to upload
            imageDropZone.addEventListener('click', () => imageInput.click());

            // Drag and drop
            imageDropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                imageDropZone.classList.add('border-purple-500', 'bg-purple-50');
            });

            imageDropZone.addEventListener('dragleave', () => {
                imageDropZone.classList.remove('border-purple-500', 'bg-purple-50');
            });

            imageDropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                imageDropZone.classList.remove('border-purple-500', 'bg-purple-50');

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    imageInput.files = files;
                    handleImageSelect();
                }
            });

            imageInput.addEventListener('change', handleImageSelect);

            function handleImageSelect() {
                const file = imageInput.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        previewImg.src = e.target.result;
                        imagePreview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            }

            function clearImage() {
                imageInput.value = '';
                imagePreview.classList.add('hidden');
            }

            function addLocation() {
                const container = document.getElementById('locationsContainer');
                const newInput = document.createElement('div');
                newInput.className = 'flex gap-2 location-input';
                newInput.innerHTML = `
                    <input type="text" name="locations[]"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                        placeholder="Nom du lieu d'intérêt">
                    <button type="button" onclick="removeLocation(this)"
                        class="px-4 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                container.appendChild(newInput);
            }

            function removeLocation(button) {
                const container = document.getElementById('locationsContainer');
                if (container.children.length > 1) {
                    button.closest('.location-input').remove();
                } else {
                    alert('Vous devez avoir au moins un point d\'intérêt');
                }
            }
        </script>
    @endpush
@endsection
