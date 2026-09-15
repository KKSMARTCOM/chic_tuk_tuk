@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Notifications</h1>
            <button id="mark-all-read"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200">
                Tout marquer comme lu
            </button>
        </div>

        @if ($notifications->count() > 0)
            <div class="space-y-4">
                @foreach ($notifications as $notification)
                    <div
                        class="border rounded-lg p-4 {{ $notification->is_read ? 'bg-gray-50' : 'bg-blue-50 border-blue-200' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-3 flex-1">
                                <div class="flex-shrink-0 mt-1">
                                    <i class="{{ $notification->icon }} text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <h3
                                        class="text-lg font-semibold text-gray-900 {{ $notification->is_read ? '' : 'font-bold' }}">
                                        {{ $notification->title }}
                                    </h3>
                                    <p class="text-gray-600 mt-1">{{ $notification->message }}</p>
                                    <p class="text-sm text-gray-500 mt-2">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                @if (!$notification->is_read)
                                    <button
                                        class="mark-read-btn bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition duration-200"
                                        data-id="{{ $notification->id }}">
                                        Marquer comme lu
                                    </button>
                                @endif
                                <button
                                    class="delete-notification-btn bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition duration-200"
                                    data-id="{{ $notification->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-bell-slash text-gray-400 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">Aucune notification</h3>
                <p class="text-gray-500">Vous n'avez pas de notifications pour le moment.</p>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Marquer une notification comme lue
                document.querySelectorAll('.mark-read-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const notificationId = this.dataset.id;
                        fetch(`/notifications/${notificationId}/read`, {
                                method: 'PATCH',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    location.reload();
                                }
                            });
                    });
                });

                // Supprimer une notification
                document.querySelectorAll('.delete-notification-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        if (!confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')) return;

                        const notificationId = this.dataset.id;
                        fetch(`/notifications/${notificationId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    location.reload();
                                }
                            });
                    });
                });

                // Marquer toutes les notifications comme lues
                document.getElementById('mark-all-read').addEventListener('click', function() {
                    fetch('/notifications/mark-all-read', {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            }
                        });
                });
            });
        </script>
    @endpush
@endsection
