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
                    class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border z-50 hidden">
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

            <div class="flex items-center space-x-3">
                <img src="{{ asset('assets/images/png/user_avatar.png') }}" alt="User Avatar"
                    class="w-10 h-10 rounded-full object-cover">
                <span class="text-gray-800 font-medium">{{ auth()->user()->name }}</span>
            </div>
        </div>
    </div>
</header>

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

        // Charger les notifications
        function loadNotifications() {
            fetch('/notifications/unread-count')
                .then(response => response.json())
                .then(data => {
                    const countElement = document.getElementById('notification-count');
                    if (data.count > 0) {
                        countElement.textContent = data.count > 99 ? '99+' : data.count;
                        countElement.classList.remove('opacity-0');
                    } else {
                        countElement.classList.add('opacity-0');
                    }
                });

            // Charger les 5 dernières notifications pour le dropdown
            /* fetch('/notifications?limit=5')
                .then(response => response.json())
                .then(data => {
                    const listElement = document.getElementById('notification-list');
                    if (data.data && data.data.length > 0) {
                        let html = '';
                        data.data.forEach(notification => {
                            html += `
                    <div class="p-4 border-b hover:bg-gray-50 cursor-pointer" onclick="markAsRead(${notification.id})">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <i class="${notification.icon} text-lg"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 ${notification.is_read ? '' : 'font-bold'}">${notification.title}</p>
                                <p class="text-sm text-gray-500">${notification.message}</p>
                                <p class="text-xs text-gray-400 mt-1">${notification.created_at_human}</p>
                            </div>
                        </div>
                    </div>
                `;
                        });
                        listElement.innerHTML = html;
                    } else {
                        listElement.innerHTML = '<div class="p-4 text-center text-gray-500">Aucune notification</div>';
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des notifications:', error);
                    document.getElementById('notification-list').innerHTML =
                        `<div class="p-4 text-center text-gray-500">Erreur de chargement</div>`;
                }); */
        }

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

        // Charger le compteur au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            loadNotifications();
        });
    </script>
@endpush
