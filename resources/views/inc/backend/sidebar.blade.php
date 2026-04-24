<!-- Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-20 hidden md:hidden" onclick="closeSidebar()">
</div>

<!-- Sidebar -->
<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-20 w-64 bg-gradient-to-b from-green-800 to-green-900 text-white transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="flex justify-center p-6">
        <a href="/" class="h-20 w-28 overflow-hidden block rounded-full">
            <img src="{{ asset('assets/images/png/chic_tuk_tuk_logo_green_transparent.png') }}"
                class="h-full w-full object-cover" alt="Logo">
        </a>
    </div>
    <nav class="mt-6">
        @if (auth()->user()->role === 'admin')
            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition 
            {{ request()->routeIs('admin.dashboard') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
            </a>
            <a href="{{ route('admin.bookings.index') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition
            {{ request()->routeIs('admin.bookings*') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-calendar-check mr-3"></i> Réservations
            </a>
            <a href="{{ route('admin.drivers.index') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition
            {{ request()->routeIs('admin.drivers*') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-users mr-3"></i> Conducteurs
            </a>
            <a href="{{ route('admin.commissions.index') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition
                {{ request()->routeIs('admin.commissions*') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-percent mr-3"></i> Commissions
            </a>
            <a href="{{ route('admin.leaves.index') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition
            {{ request()->routeIs('admin.leaves*') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-calendar-alt mr-3"></i> Congés
            </a>
            <a href="{{ route('admin.pricing.index') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition
            {{ request()->routeIs('admin.pricing*') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-dollar-sign mr-3"></i> Tarification
            </a>
            {{-- <a href="{{ route('admin.circuits.index') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition
            {{ request()->routeIs('admin.circuits*') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-map-marked-alt mr-3"></i> Circuits
            </a>
            <a href="{{ route('admin.promo-codes.index') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition 
            {{ request()->routeIs('admin.promo-codes.index') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-tags mr-3"></i> Codes Promo
            </a> --}}
        @endif

        @if (auth()->user()->role === 'driver')
            {{-- if conductor --}}
            <a href="{{ route('driver.dashboard') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition 
                {{ request()->routeIs('driver.dashboard') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-tachometer-alt mr-3"></i> Tableau de bord
            </a>
            <a href="{{ route('driver.bookings.available') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition 
                {{ request()->routeIs('driver.bookings.available') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-list mr-3"></i> Courses Disponibles
            </a>
            <a href="{{ route('driver.bookings.accepting') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition 
                {{ request()->routeIs('driver.bookings.accepting') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-calendar-check mr-3"></i> Mes Courses
            </a>
            <a href="{{ route('driver.leaves.index') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition
            {{ request()->routeIs('driver.leaves*') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-calendar-alt mr-3"></i> Congés
            </a>
            {{-- <a href="{{ route('notifications.index') }}"
            class="flex items-center px-6 py-3 hover:bg-green-600 transition
                {{ request()->routeIs('notifications.index') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-bell mr-3"></i> Notifications
            </a> --}}
            {{-- <a href="" class="flex items-center px-6 py-3 hover:bg-green-600 transition">
                <i class="fas fa-user mr-3"></i> Mon Profil
            </a> --}}
        @endif

        <a href="{{ route('bookings.histories') }}"
            class="flex items-center px-6 py-3 hover:bg-green-600 transition
                {{ request()->routeIs('bookings.histories') ? 'bg-green-600 border-l-4 border-white' : '' }}">
            <i class="fas fa-history mr-3"></i> Historique
        </a>

        <a href="#" class="flex items-center px-6 py-3 hover:bg-green-600 transition mt-auto">
            <i class="fas fa-cog mr-3"></i> Paramètres
        </a>

        <button onclick="showLogoutModal()"
            class="flex items-center px-6 py-3 hover:bg-green-600 transition mt-auto w-full text-left">
            <i class="fas fa-sign-out-alt mr-3"></i> Déconnexion
        </button>
    </nav>
</aside>

<!-- Logout Confirmation Modal -->
@include('inc.global.logout')

@push('scripts')
    <script>
        function showLogoutModal() {
            document.getElementById('logoutModal').classList.remove('hidden');
        }

        function hideLogoutModal() {
            document.getElementById('logoutModal').classList.add('hidden');
        }
    </script>
@endpush
