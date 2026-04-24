@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between space-x-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Importer des Conducteurs</h1>
                    <p class="text-gray-600">Importez plusieurs conducteurs depuis un fichier Excel ou CSV</p>
                </div>

                <div class="flex space-x-3">
                    <a href="{{ route('admin.drivers.index') }}"
                        class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-arrow-left mr-2"></i> Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i> Instructions d'Import
                </h3>
                <ul class="space-y-3 text-gray-700">
                    <li class="flex items-start space-x-3">
                        <span
                            class="inline-block w-6 h-6 bg-blue-100 text-blue-600 rounded-full text-center text-sm font-bold flex-shrink-0">1</span>
                        <span><strong>Téléchargez le template</strong> en cliquant sur le bouton ci-dessous</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <span
                            class="inline-block w-6 h-6 bg-blue-100 text-blue-600 rounded-full text-center text-sm font-bold flex-shrink-0">2</span>
                        <span><strong>Remplissez le fichier</strong> avec les données de vos conducteurs</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <span
                            class="inline-block w-6 h-6 bg-blue-100 text-blue-600 rounded-full text-center text-sm font-bold flex-shrink-0">3</span>
                        <span><strong>Vérifiez les colonnes</strong> requises :
                            <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-sm inline-block mt-2">nom, email,
                                telephone, adresse, numero_permis, numero_vehicule, type_vehicule, disponible, actif</span>
                        </span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <span
                            class="inline-block w-6 h-6 bg-blue-100 text-blue-600 rounded-full text-center text-sm font-bold flex-shrink-0">4</span>
                        <span><strong>Selectionnez le fichier</strong> et cliquez sur "Importer"</span>
                    </li>
                </ul>
            </div>

            <!-- Format Requirements -->
            <div class="bg-blue-50 rounded-lg shadow-md p-6 mb-6 border-l-4 border-blue-600">
                <h3 class="text-lg font-semibold text-blue-900 mb-4">
                    <i class="fas fa-file-alt mr-2"></i> Format des Colonnes
                </h3>
                <div class="space-y-3 text-sm text-blue-900">
                    <div>
                        <span class="font-semibold">Type de véhicule:</span> doit être <span
                            class="bg-white px-2 py-1 rounded">moto</span>, <span
                            class="bg-white px-2 py-1 rounded">tricycle</span> ou <span
                            class="bg-white px-2 py-1 rounded">car</span>
                    </div>
                    <div>
                        <span class="font-semibold">Disponible/Actif:</span> doit être <span
                            class="bg-white px-2 py-1 rounded">Oui</span> ou <span
                            class="bg-white px-2 py-1 rounded">Non</span>
                    </div>
                    <div>
                        <span class="font-semibold">Tous les téléphones:</span> doivent être uniques et au format valide
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-upload mr-2"></i> Sélectionner le Fichier à Importer
                </h3>

                <form action="{{ route('admin.drivers.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-6">
                        <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
                            Fichier Excel ou CSV
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-blue-500 transition"
                            id="dropZone">
                            <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv" class="hidden"
                                onchange="handleFileSelect()">
                            <label for="file" class="cursor-pointer">
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2 inline-block"></i>
                                <p class="text-gray-700 font-medium">Glissez-déposez votre fichier ici</p>
                                <p class="text-gray-500 text-sm">ou cliquez pour sélectionner un fichier</p>
                                <p class="text-gray-400 text-xs mt-2">Formats acceptés: XLSX, XLS, CSV</p>
                            </label>
                            <p id="fileName" class="text-blue-600 font-medium mt-4 hidden"></p>
                        </div>
                        @error('file')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex space-x-4">
                        <a href="{{ route('admin.drivers.template.download') }}"
                            class="flex-1 bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition text-center">
                            <i class="fas fa-download mr-2"></i> Télécharger le Template
                        </a>
                        <button type="submit"
                            class="flex-1 bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition">
                            <i class="fas fa-check mr-2"></i> Importer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <!-- Tips -->
            <div class="bg-yellow-50 rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <h3 class="text-lg font-semibold text-yellow-900 mb-3">
                    <i class="fas fa-lightbulb mr-2"></i> Conseils
                </h3>
                <ul class="space-y-2 text-sm text-yellow-900">
                    <li>• Les numéros de téléphone doivent être uniques</li>
                    <li>• Les permis de conduire doivent être uniques</li>
                    <li>• L'email est optionnel mais doit être valide</li>
                    <li>• Vérifiez l'orthographe des types de véhicule</li>
                    <li>• Un mot de passe temporaire sera généré</li>
                </ul>
            </div>

            <!-- Stats -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-chart-bar mr-2"></i> Statistiques Actuelles
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Total Conducteurs</span>
                        <span class="text-2xl font-bold text-gray-800">{{ $stats['total'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between pb-3 border-t">
                        <span class="text-gray-600">Actifs</span>
                        <span class="text-xl font-semibold text-green-600">{{ $stats['active'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Inactifs</span>
                        <span class="text-xl font-semibold text-red-600">{{ $stats['inactive'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('file');
            const fileName = document.getElementById('fileName');

            // Drag and drop
            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.classList.add('border-blue-500', 'bg-blue-50');
            });

            dropZone.addEventListener('dragleave', (e) => {
                e.preventDefault();
                dropZone.classList.remove('border-blue-500', 'bg-blue-50');
            });

            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropZone.classList.remove('border-blue-500', 'bg-blue-50');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    handleFileSelect();
                }
            });

            function handleFileSelect() {
                if (fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    fileName.textContent = '✓ ' + file.name + ' (' + (file.size / 1024).toFixed(2) + ' KB)';
                    fileName.classList.remove('hidden');
                } else {
                    fileName.classList.add('hidden');
                }
            }

            // Stats
            @if (session('stats'))
                const stats = {!! json_encode(session('stats')) !!};
            @endif
        </script>
    @endpush
@endsection
