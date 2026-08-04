@extends('layouts.auth')

@php
    $hidePwaComponent = true;
@endphp

@section('title', 'Installer ChicTukTuk')

@section('form')
    <div class="text-center space-y-5">

        <p class="text-gray-500 text-sm leading-relaxed">
            Installez ChicTukTuk sur votre appareil pour un accès rapide,
            des notifications en temps réel et une meilleure expérience.
        </p>

        {{-- Avantages --}}
        {{-- <div class="text-left bg-gray-50 rounded-xl p-4 space-y-2">
            <div class="flex items-center gap-3 text-sm text-gray-700">
                <i class="fas fa-bolt text-[#286b41] w-5 text-center"></i>
                <span>Accès rapide depuis l'écran d'accueil</span>
            </div>
            <div class="flex items-center gap-3 text-sm text-gray-700">
                <i class="fas fa-bell text-[#286b41] w-5 text-center"></i>
                <span>Notifications de nouvelles courses</span>
            </div>
            <div class="flex items-center gap-3 text-sm text-gray-700">
                <i class="fas fa-wifi-slash text-[#286b41] w-5 text-center"></i>
                <span>Fonctionne même hors ligne</span>
            </div>
            <div class="flex items-center gap-3 text-sm text-gray-700">
                <i class="fas fa-mobile-alt text-[#286b41] w-5 text-center"></i>
                <span>Expérience native sur mobile</span>
            </div>
        </div> --}}

        {{-- Bouton Android/Chrome --}}
        <div id="android-section" class="hidden">
            <button id="install-btn" onclick="triggerInstall()"
                class="w-full py-3 bg-[#286b41] hover:opacity-90 text-white rounded-lg font-semibold
            flex items-center justify-center gap-2 transition">
                <i class="fas fa-download"></i> Installer maintenant
            </button>
            <p class="text-xs text-gray-400 mt-2">
                <i class="fab fa-chrome mr-1"></i> Compatible Chrome, Edge, Samsung Internet
            </p>
        </div>

        {{-- Instructions iOS --}}
        <div id="ios-section" class="hidden">
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-700 text-left">
                <p class="font-semibold mb-3 flex items-center gap-2">
                    <i class="fab fa-apple"></i> Installer sur iPhone / iPad
                </p>
                <ol class="space-y-2">
                    <li class="flex items-start gap-2">
                        <span
                            class="bg-blue-200 text-blue-800 rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">1</span>
                        <span>Appuyez sur <i class="fas fa-arrow-up-from-bracket mx-1"></i> en bas de
                            <strong>Safari</strong></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span
                            class="bg-blue-200 text-blue-800 rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">2</span>
                        <span>Faites défiler et appuyez sur <strong>« Sur l'écran d'accueil »</strong></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span
                            class="bg-blue-200 text-blue-800 rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">3</span>
                        <span>Appuyez sur <strong>Ajouter</strong> en haut à droite</span>
                    </li>
                </ol>
            </div>
        </div>

        {{-- Navigateur non supporté --}}
        <div id="unsupported-section" class="hidden">
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-700 text-left">
                <p class="font-semibold mb-1 flex items-center gap-2">
                    <i class="fas fa-triangle-exclamation"></i> Navigateur non supporté
                </p>
                <p>Ouvrez cette page dans <strong>Chrome</strong> ou <strong>Safari</strong> pour installer l'application.
                </p>
            </div>
        </div>

        {{-- Déjà installé --}}
        <div id="already-installed" class="hidden">
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-700">
                <p class="font-semibold flex items-center justify-center gap-2">
                    <i class="fas fa-check-circle text-green-500"></i>
                    Application déjà installée !
                </p>
                <p class="mt-1 text-green-600">ChicTukTuk est sur votre écran d'accueil.</p>
            </div>
        </div>

        {{-- Loading state --}}
        <div id="loading-section">
            <div class="flex items-center justify-center gap-2 text-gray-400 text-sm py-2">
                <i class="fas fa-spinner fa-spin"></i> Vérification...
            </div>
        </div>

        <div class="border-t border-gray-100 pt-4 space-y-2">
            {{-- <a href="{{ route('login') }}" class="block text-sm text-[#286b41] hover:underline font-medium">
                <i class="fas fa-sign-in-alt mr-1"></i> Se connecter
            </a> --}}
            <a href="{{ route('home') }}" class="block text-sm text-gray-400 hover:text-gray-600 hover:underline">
                Continuer dans le navigateur
            </a>
        </div>
    </div>

    @push('scripts')
        <script>
            (function() {
                const androidSection = document.getElementById('android-section');
                const iosSection = document.getElementById('ios-section');
                const unsupportedSection = document.getElementById('unsupported-section');
                const alreadyInstalled = document.getElementById('already-installed');
                const loadingSection = document.getElementById('loading-section');

                function hideLoading() {
                    loadingSection.classList.add('hidden');
                }

                // Déjà en mode standalone = déjà installé
                if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
                    hideLoading();
                    alreadyInstalled.classList.remove('hidden');
                    return;
                }

                const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
                const isAndroid = /android/i.test(navigator.userAgent);

                if (isIos) {
                    hideLoading();
                    iosSection.classList.remove('hidden');
                    return;
                }

                // Android/Chrome : attendre beforeinstallprompt
                if (window.__pwaDeferred) {
                    // pwa.js a déjà capturé l'événement
                    hideLoading();
                    androidSection.classList.remove('hidden');
                } else {
                    // Attendre que pwa.js capture l'événement
                    window.addEventListener('pwa:installable', () => {
                        hideLoading();
                        androidSection.classList.remove('hidden');
                    });

                    // Timeout : si pas d'événement après 3s → non supporté
                    setTimeout(() => {
                        if (loadingSection.classList.contains('hidden')) return;
                        hideLoading();
                        unsupportedSection.classList.remove('hidden');
                    }, 3000);
                }

                // Quand l'app est installée
                window.addEventListener('appinstalled', () => {
                    androidSection.classList.add('hidden');
                    alreadyInstalled.classList.remove('hidden');
                });
            })();

            function triggerInstall() {
                if (typeof installApp === 'function') {
                    installApp();
                }
            }
        </script>
    @endpush
@endsection
