<nav class="bg-white shadow-lg fixed w-full top-0 left-0 right-0 z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex items-center">
                <a href="/" class="h-20 w-28 overflow-hidden">
                    <img src="{{ asset('assets/images/png/chic_tuk_tuk_logo_transparent.png') }}"
                        class="h-full w-full object-cover" alt="Logo">
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <a href="#reservation" class="text-gray-700 hover:text-[#286b41] transition">Réserver</a>
                <a href="#comment-ca-marche" class="text-gray-700 hover:text-[#286b41] transition">Comment ça
                    marche</a>
                <a href="#avantages" class="text-gray-700 hover:text-[#286b41] transition">Avantages</a>
                <a href="{{ route('login') }}"
                    class="px-6 py-2 bg-emerald-600 text-white rounded-full hover:bg-[#286b41] shadow-lg shadow-emerald-600/30 transition">Connexion</a>
            </div>
        </div>
    </div>
</nav>
