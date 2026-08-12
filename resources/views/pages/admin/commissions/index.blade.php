@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">Gestion des Commissions</h1>
                <p class="text-sm md:text-base text-gray-600">Gérez les commissions des Agents</p>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Revenu Total -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Revenu Total</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">
                        {{ number_format($stats['total_revenue'], 0, ',', ' ') }} FCFA</p>
                </div>
                <div class="bg-blue-100 rounded-full w-16 h-16 flex justify-center items-center">
                    <i class="fas fa-dollar-sign text-blue-600 text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-gray-600">
                <i class="fas fa-info-circle"></i> Total des commissions
            </div>
        </div>

        <!-- Total Commissions -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Total Commissions</p>
                    <p class="text-3xl font-bold text-purple-600 mt-2">{{ $stats['total_count'] }}</p>
                </div>
                <div class="bg-purple-100 rounded-full w-16 h-16 flex justify-center items-center">
                    <i class="fas fa-list text-purple-600 text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-gray-600">
                <i class="fas fa-info-circle"></i> Nombre total de commissions
            </div>
        </div>

        <!-- Lien Paiements -->
        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center justify-between h-full">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Gestion automatique des commissions</p>
                    <p class="text-sm text-gray-600 mt-2">Enregistrez et gérez les commissions des agents</p>
                </div>
                <a href="{{ route('admin.payments.index') }}"
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-money-bill-wave"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Commissions Table -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Liste des Commissions</h3>
        </div>
        @if ($commissions && $commissions->count() > 0)
            <div class="overflow-x-auto p-4">
                <table class="min-w-full divide-y divide-gray-200 display" id="datatable1">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Numéro
                                Course</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Agent</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Montant</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Date d'arrivée
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($commissions as $commission)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('admin.bookings.show', $commission->booking) }}"
                                        class="text-blue-600 hover:text-blue-800 font-medium">
                                        {{ $commission->booking->booking_number ?? 'N/A' }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode($commission->driver->user->name) }}"
                                            class="w-10 h-10 rounded-full mr-3">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $commission->driver->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $commission->driver->user->phone }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-lg font-semibold text-green-600">
                                        {{ number_format($commission->amount, 0, ',', ' ') }} FCFA
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ formatDateFr($commission->date) ?? formatDateFr($commission->created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.commissions.show', $commission) }}"
                                            class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="text-red-600 hover:text-red-800"
                                            title="Supprimer la commission"
                                            onclick="openDeleteCommissionModal('{{ $commission->id }}', '{{ addslashes($commission->driver->user->name ?? 'N/A') }}', '{{ number_format($commission->amount, 0, ',', ' ') }} FCFA', '{{ $commission->booking->booking_number ?? 'N/A' }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    Aucune commission trouvée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <p class="px-6 py-4 text-center text-gray-500">Aucune commission</p>
                <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
            </div>
        @endif
    </div>

    <!-- Modal: Supprimer Commission -->
    <div id="deleteCommissionModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-times-circle text-red-600 mr-2"></i> Supprimer la commission
                </h3>
                <button type="button" onclick="closeDeleteCommissionModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="deleteCommissionForm" method="POST" action="">
                @csrf
                @method('PATCH')
                <div class="px-6 py-4">
                    <p class="text-gray-700">
                        Confirmez-vous la suppression de la commission
                        <span id="deleteCommissionBooking" class="font-semibold"></span>
                        de <span id="deleteCommissionAmount" class="font-semibold"></span>
                        pour <span id="deleteCommissionName" class="font-semibold"></span> ?
                    </p>
                    <p class="text-sm text-red-500 mt-2">Cette action est irréversible.</p>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                    <button type="button" onclick="closeDeleteCommissionModal()"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg">
                        Retour
                    </button>
                    <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg">
                        <i class="fas fa-ban mr-2"></i> Supprimer la commission
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            $("#datatable1").DataTable({
                order: [
                    [3, "desc"]
                ],
                columnDefs: [{
                    targets: 3,
                    searchable: false,
                }, ],
                language: {
                    processing: "Traitement en cours...",
                    search: "Rechercher : ",
                    lengthMenu: "Afficher _MENU_ éléments",
                    info: "Affichage de _START_ à _END_ sur _TOTAL_ ",
                    infoEmpty: "Affichage de 0 à 0 sur 0",
                    infoFiltered: "(filtré de _MAX_ éléments au total)",
                    loadingRecords: "Chargement en cours...",
                    zeroRecords: "Aucun élément à afficher",
                    emptyTable: "Aucune donnée disponible dans le tableau",
                },
                // Callback pour appliquer select2 après init
                initComplete: function() {
                    if (typeof $.fn.select2 !== "undefined") {
                        $(".dataTables_length select").select2({
                            minimumResultsForSearch: Infinity,
                        });
                    }
                },
            })

            function openDeleteCommissionModal(commissionId, driverName, amount, bookingNumber) {
                document.getElementById('deleteCommissionName').textContent = driverName;
                document.getElementById('deleteCommissionAmount').textContent = amount;
                document.getElementById('deleteCommissionBooking').textContent = bookingNumber;

                const form = document.getElementById('deleteCommissionForm');
                form.action = `{{ url('admin/commissions') }}/${commissionId}`;

                const modal = document.getElementById('deleteCommissionModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeDeleteCommissionModal() {
                const modal = document.getElementById('deleteCommissionModal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        </script>
    @endpush
@endsection
