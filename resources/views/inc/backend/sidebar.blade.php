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
        @if (auth()->user()->profil === 'admin')
            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition 
            {{ request()->routeIs('admin.dashboard') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-tachometer-alt mr-3"></i> Vue d'ensemble
            </a>
            <a href="{{ route('admin.bookings.index') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition
            {{ request()->routeIs('admin.bookings*') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-calendar-check mr-3"></i> Réservations
            </a>
            <a href="{{ route('admin.drivers.index') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition
            {{ request()->routeIs('admin.drivers*') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-users mr-3"></i> Agents
            </a>
            <a href="{{ route('admin.vehicles.index') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition
            {{ request()->routeIs('admin.vehicles*') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-car mr-3"></i> Véhicules
            </a>
            {{-- <div id="contracts-menu">
                <button onclick="toggleMenu('contracts-sub')"
                    class="w-full flex items-center justify-between px-6 py-3 hover:bg-green-600 transition
        {{ request()->routeIs('admin.driver-contracts*', 'admin.vehicle-contracts*') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                    <span class="flex items-center">
                        <i class="fas fa-file-contract mr-3"></i> Contrats
                    </span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200" id="contracts-icon"></i>
                </button>

                <div id="contracts-sub"
                    class="bg-green-900
        {{ request()->routeIs('admin.driver-contracts*', 'admin.vehicle-contracts*') ? '' : 'hidden' }}">
                    <a href="{{ route('admin.driver-contracts.index') }}"
                        class="flex items-center pl-12 pr-6 py-2.5 text-sm hover:bg-green-600 transition
            {{ request()->routeIs('admin.driver-contracts*') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                        <i class="fas fa-user-tie mr-3 text-xs"></i> Cnt. Agents
                    </a>
                    <a href="{{ route('admin.vehicle-contracts.index') }}"
                        class="flex items-center pl-12 pr-6 py-2.5 text-sm hover:bg-green-600 transition
            {{ request()->routeIs('admin.vehicle-contracts*') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                        <i class="fas fa-car mr-3 text-xs"></i> Cnt. Propriétaires
                    </a>
                </div>
            </div> --}}
            <a href="{{ route('admin.commissions.index') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition
                {{ request()->routeIs('admin.commissions*') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-percent mr-3"></i> Commissions
            </a>
            <a href="{{ route('admin.payments.index') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition
                {{ request()->routeIs('admin.payments*') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-money-bill mr-3"></i> Paiements
            </a>
            <a href="{{ route('admin.leaves.index') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition
            {{ request()->routeIs('admin.leaves*') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-calendar-alt mr-3"></i> Pauses
            </a>
            <a href="{{ route('admin.users.index') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition
            {{ request()->routeIs('admin.users*') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-user mr-3"></i> Utilisateurs
            </a>
            <a href="{{ route('admin.roles.index') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition
            {{ request()->routeIs('admin.roles*') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-user-shield mr-3"></i> Rôles
            </a>
        @endif

        @if (auth()->user()->profil === 'driver')
            {{-- if conductor --}}
            <a href="{{ route('driver.dashboard') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition 
                {{ request()->routeIs('driver.dashboard') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-tachometer-alt mr-3"></i> Vue d'ensemble
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
                <i class="fas fa-calendar-alt mr-3"></i> Pauses
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

        @if (auth()->user()->profil === 'client')
            @hasrole('client')
                <a href="{{ route('client.dashboard') }}"
                    class="flex items-center px-6 py-3 hover:bg-green-600 transition 
            {{ request()->routeIs('client.dashboard') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                    <i class="fas fa-tachometer-alt mr-3"></i> Vue d'ensemble
                </a>
            @endhasrole
            <a href="{{ route('admin.payments.index') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition
                {{ request()->routeIs('admin.payments*') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-money-bill mr-3"></i> Paiements
            </a>
            <a href="{{ route('admin.leaves.index') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition
            {{ request()->routeIs('admin.leaves*') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-calendar-alt mr-3"></i> Pauses
            </a>
        @endif

        @hasrole('proprietaire')
            <a href="{{ route('owner.vehicles.index') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition
                {{ request()->routeIs('owner.vehicles*') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-car-side mr-3"></i> Mes Véhicules
            </a>
        @endhasrole

        @hasanyrole('admin|driver')
            <a href="{{ route('bookings.histories') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition
            {{ request()->routeIs('bookings.histories') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-history mr-3"></i> Historique
            </a>

            <a href="{{ route('settings.settings') }}"
                class="flex items-center px-6 py-3 hover:bg-green-600 transition
                    {{ request()->routeIs('settings.settings') || request()->routeIs('profile') ? 'bg-green-600 border-l-4 border-white' : '' }}">
                <i class="fas fa-cog mr-3"></i> Paramètres
            </a>
        @endhasanyrole


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

        function toggleMenu(id) {
            const sub = document.getElementById(id);
            const icon = document.getElementById('contracts-icon');
            sub.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }
    </script>
@endpush
