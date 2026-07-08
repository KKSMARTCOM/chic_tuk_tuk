<!-- Conteneur pour la notification d'installation PWA -->
<div id="pwa-install-container" class="fixed top-4 right-4 z-50 hidden max-w-sm animate-in slide-in-from-top">
    <div class="bg-white rounded-lg shadow-lg border border-blue-200 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-[#286b41] to-green-700 text-white px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fas fa-mobile-alt text-lg"></i>
                <span class="font-semibold">Installer Chic Tuk Tuk</span>
            </div>
            <button onclick="PWA.dismiss()" class="text-white hover:text-blue-100 text-xl leading-none">×</button>
        </div>

        <!-- Content -->
        <div class="px-4 py-3">
            <p class="text-sm text-gray-700 mb-4">
                Installez Chic Tuk Tuk sur votre appareil pour un accès rapide et une meilleure expérience hors ligne.
            </p>
            <div class="space-y-2">
                <button id="pwa-install-btn"
                    class="w-full bg-[#286b41] hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                    <i class="fas fa-download mr-2"></i>
                    Installer
                </button>
                <button id="pwa-dismiss-btn" onclick="PWA.dismiss()"
                    class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2 px-4 rounded-lg transition duration-200">
                    Plus tard
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Conteneur pour l'état de connexion -->
<div id="connection-status" class="fixed top-4 left-4 z-50 hidden max-w-md animate-in slide-in-from-top">
    <!-- Le contenu sera rempli par le script PWA -->
</div>

@push('scripts')
    <script>
        // Assurer que le script PWA est chargé
        document.addEventListener('DOMContentLoaded', function() {
            // Vérifier l'état de connexion au chargement
            if (!navigator.onLine) {
                const statusContainer = document.getElementById('connection-status');
                statusContainer.innerHTML = `
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 flex items-center space-x-2 shadow-lg">
                    <i class="fas fa-wifi-slash text-yellow-600"></i>
                    <span class="text-sm text-yellow-800 font-semibold">Mode hors ligne activé</span>
                </div>
            `;
                statusContainer.classList.remove('hidden');
            }
        });
    </script>
@endpush
