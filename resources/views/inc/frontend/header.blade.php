<nav class="bg-white shadow-lg fixed w-full top-0 left-0 right-0 z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <!-- Logo -->
            <div class="flex items-center flex-shrink-0">
                <a href="/" class="h-20 w-28 overflow-hidden">
                    <img src="{{ asset('assets/images/png/chic_tuk_tuk_logo_transparent.png') }}"
                        class="h-full w-full object-cover" alt="Logo">
                </a>
            </div>

            <!-- Menu Desktop -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="#reservation" class="text-gray-700 hover:text-[#286b41] transition font-medium">Réserver</a>
                <a href="#comment-ca-marche" class="text-gray-700 hover:text-[#286b41] transition font-medium">Comment
                    ça marche</a>
                <a href="#avantages" class="text-gray-700 hover:text-[#286b41] transition font-medium">Avantages</a>
                <a href="{{ route('login') }}"
                    class="px-6 py-2 bg-[#286b41] text-white rounded-full hover:opacity-90 shadow-lg shadow-emerald-600/30 transition font-medium">Connexion</a>
            </div>

            <!-- Bouton Hamburger Mobile -->
            <button id="hamburger-btn"
                class="md:hidden flex items-center text-gray-700 hover:text-[#286b41] transition p-2" aria-label="Menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                    </path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Menu Mobile -->
    <div id="mobile-menu" class="md:hidden bg-white border-t border-gray-200 hidden">
        <div class="px-4 pt-2 pb-4 space-y-2">
            <a href="#reservation"
                class="mobile-link block px-4 py-2 text-gray-700 hover:bg-emerald-50 hover:text-[#286b41] transition rounded-lg font-medium">Réserver</a>
            <a href="#comment-ca-marche"
                class="mobile-link block px-4 py-2 text-gray-700 hover:bg-emerald-50 hover:text-[#286b41] transition rounded-lg font-medium">Comment
                ça marche</a>
            <a href="#avantages"
                class="mobile-link block px-4 py-2 text-gray-700 hover:bg-emerald-50 hover:text-[#286b41] transition rounded-lg font-medium">Avantages</a>
            <a href="{{ route('login') }}"
                class="mobile-link block px-4 py-2 mt-4 bg-[#286b41] text-white rounded-lg hover:opacity-90 shadow-lg shadow-emerald-600/30 transition font-medium text-center">Connexion</a>
        </div>
    </div>
</nav>

@push('scripts')
    <script>
        $(document).ready(function() {
            const hamburgerBtn = $('#hamburger-btn');
            const mobileMenu = $('#mobile-menu');

            // Ouvrir/Fermer le menu
            hamburgerBtn.click(function() {
                mobileMenu.toggleClass('hidden');
            });

            // Fermer le menu au clic sur un lien
            $('.mobile-link').click(function() {
                mobileMenu.addClass('hidden');
            });

            // Fermer le menu au redimensionnement si on atteint la breakpoint desktop
            $(window).resize(function() {
                if ($(window).width() >= 768) {
                    mobileMenu.addClass('hidden');
                }
            });
        });
    </script>
@endpush
