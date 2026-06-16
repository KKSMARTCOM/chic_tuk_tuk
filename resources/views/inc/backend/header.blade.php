<header class="bg-white shadow-sm">
    <div class="flex items-center justify-between px-8 py-4">
        <!-- Hamburger Button for Mobile -->
        <button class="md:hidden text-gray-600 text-2xl mr-4" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <h2 class="text-2xl font-bold text-gray-800">Tableau de bord</h2>
        <div class="flex items-center space-x-8">
            <!-- Notifications Dropdown -->
            <div class="relative">
                <button id="notification-btn" class="relative">
                    <i class="fas fa-bell text-gray-600 text-xl"></i>
                    <span id="notification-count"
                        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center opacity-0">0</span>
                </button>
                <!-- Dropdown -->
                <div id="notification-dropdown"
                    class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border z-20 hidden">
                    <div class="p-4 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">Notifications</h3>
                    </div>
                    <div id="notification-list" class="max-h-64 overflow-y-auto">
                        <!-- Les notifications seront chargées ici -->
                        <div class="p-4 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin"></i> Chargement...
                        </div>
                    </div>
                    <div class="p-4 border-t text-center">
                        <a href="{{ route('notifications.index') }}"
                            class="text-sm text-blue-600 hover:text-blue-800">Voir
                            toutes les
                            notifications</a>
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-3 relative">
                <button id="profile-btn" class="flex items-center space-x-3 cursor-pointer hover:opacity-80 transition">
                    <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) }}"
                        class="w-8 h-8 rounded-full mr-3">
                </button>

                <!-- Profile Dropdown -->
                <div id="profile-dropdown"
                    class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border z-20 hidden top-8">
                    <div class="p-4 border-b">
                        <p class="text-sm text-gray-600">Connecté en tant que</p>
                        <p class="text-lg font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                    </div>
                    <div class="p-2">
                        <a href="{{ route('profile') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded transition">
                            <i class="fas fa-user mr-2"></i>Mon profil
                        </a>
                    </div>
                    <div class="p-2 border-t">
                        <button onclick="showLogoutModal()"
                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded transition">
                            <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Logout Confirmation Modal -->
@include('inc.global.logout')

@push('scripts')
    <!-- Sidebar Toggle Script -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const isOpen = !sidebar.classList.contains('-translate-x-full');

            if (isOpen) {
                closeSidebar();
            } else {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            }
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }

        // Close sidebar when clicking on a link (mobile)
        document.querySelectorAll('#sidebar a').forEach(link => {
            link.addEventListener('click', closeSidebar);
        });

        // Notification Dropdown Toggle
        document.getElementById('notification-btn').addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = document.getElementById('notification-dropdown');
            dropdown.classList.toggle('hidden');

            // Charger les notifications si le dropdown est ouvert
            if (!dropdown.classList.contains('hidden')) {
                loadNotifications();
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('notification-dropdown');
            const btn = document.getElementById('notification-btn');
            if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Profile Dropdown Toggle
        document.getElementById('profile-btn').addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = document.getElementById('profile-dropdown');
            dropdown.classList.toggle('hidden');
        });

        // Close profile dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('profile-dropdown');
            const btn = document.getElementById('profile-btn');
            if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Marquer une notification comme lue depuis le dropdown
        function markAsRead(id) {
            fetch(`/notifications/${id}/read`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(() => {
                    loadNotifications(); // Recharger les notifications
                });
        }

        function showLogoutModal() {
            document.getElementById('logoutModal').classList.remove('hidden');
        }

        function hideLogoutModal() {
            document.getElementById('logoutModal').classList.add('hidden');
        }
    </script>
@endpush
