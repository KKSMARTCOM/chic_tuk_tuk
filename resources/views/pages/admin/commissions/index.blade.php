@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Gestion des Commissions</h1>
                <p class="text-gray-600">Gérez les commissions des conducteurs</p>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
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

        <!-- Commission Payée -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Commission Payée</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ number_format($stats['paid_commissions'], 0, ',', ' ') }} FCFA</p>
                </div>
                <div class="bg-green-100 rounded-full w-16 h-16 flex justify-center items-center">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-green-600">
                <i class="fas fa-arrow-up"></i> {{ $stats['paid_count'] }} paiement(s)
            </div>
        </div>

        <!-- Commission Due -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Commission Due</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">
                        {{ number_format($stats['unpaid_commissions'], 0, ',', ' ') }} FCFA</p>
                </div>
                <div class="bg-red-100 rounded-full w-16 h-16 flex justify-center items-center">
                    <i class="fas fa-exclamation-circle text-red-600 text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-red-600">
                <i class="fas fa-arrow-up"></i> {{ $stats['unpaid_count'] }} paiement(s) en attente
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
                <i class="fas fa-info-circle"></i> Total des commissions
            </div>
        </div>
    </div>

    <!-- Top Drivers Revenue -->
    {{-- <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Top 5 Conducteurs par Revenu</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rang
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Conducteur</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Revenu
                            Total</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($stats['driver_revenues'] as $index => $driver)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-purple-100 text-purple-600 font-bold">
                                    {{ $index + 1 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode($driver->user->name) }}"
                                        class="w-10 h-10 rounded-full mr-3">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $driver->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $driver->user->phone }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-lg font-semibold text-green-600">
                                    {{ number_format($driver->commissions_sum_amount ?? 0, 0, ',', ' ') }} FCFA
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="{{ route('admin.drivers.show', $driver->user) }}"
                                    class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-eye mr-2"></i> Voir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                Aucune donnée de revenu disponible
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div> --}}

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Filtres</h3>
        </div>
        <div class="px-6 py-4">
            <form method="GET" action="{{ route('admin.commissions.index') }}" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Rechercher</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Nom du conducteur ou numéro de course..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="md:w-48">
                    <label for="is_paid" class="block text-sm font-medium text-gray-700 mb-1">Statut Paiement</label>
                    <select name="is_paid" id="is_paid"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Tous les statuts</option>
                        <option value="1" {{ request('is_paid') == '1' ? 'selected' : '' }}>Payé</option>
                        <option value="0" {{ request('is_paid') == '0' ? 'selected' : '' }}>Non payé</option>
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit"
                        class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-search mr-2"></i> Rechercher
                    </button>
                    <a href="{{ route('admin.commissions.index') }}"
                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                        <i class="fas fa-times mr-2"></i> Effacer
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Commissions Table -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Liste des Commissions</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Numéro
                            Course</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Conducteur</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Statut
                            Paiement</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date
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
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $commission->is_paid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $commission->is_paid ? 'Payé' : 'Non payé' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ formatDateFr($commission->date) ?? formatDateFr($commission->created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button
                                    onclick="openPaymentModal('{{ $commission->id }}', '{{ $commission->driver->user->name }}', {{ $commission->is_paid ? 'false' : 'true' }})"
                                    class="text-{{ $commission->is_paid ? 'red' : 'green' }}-600 hover:text-{{ $commission->is_paid ? 'red' : 'green' }}-800 mr-3">
                                    <i class="fas fa-{{ $commission->is_paid ? 'undo' : 'check-circle' }}"></i>
                                </button>
                                <a href="{{ route('admin.commissions.show', $commission) }}"
                                    class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                Aucune commission trouvée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if ($commissions->hasPages())
        <div class="mt-8">
            {{ $commissions->links('pagination::tailwind') }}
        </div>
    @endif

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-20">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Valider le Paiement</h3>
            <p class="text-gray-600 mb-6" id="paymentMessage"></p>
            <input type="hidden" id="commissionId" value="">
            <input type="hidden" id="markAsPaid" value="">
            <div class="flex space-x-4">
                <button type="button" onclick="closePaymentModal()"
                    class="flex-1 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                    Annuler
                </button>
                <button type="button" onclick="confirmPayment()"
                    class="flex-1 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    Confirmer
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                }
            });

            function openPaymentModal(commissionId, driverName, markAsPaid) {
                const action = markAsPaid ? 'comme payée' : 'comme non payée';
                const message = `Êtes-vous sûr de vouloir marquer la commission de ${driverName} ${action} ?`;

                document.getElementById('paymentMessage').textContent = message;
                document.getElementById('commissionId').value = commissionId;
                document.getElementById('markAsPaid').value = markAsPaid;
                document.getElementById('paymentModal').classList.remove('hidden');
                document.getElementById('paymentModal').classList.add('flex');
            }

            function closePaymentModal() {
                document.getElementById('paymentModal').classList.add('hidden');
                document.getElementById('paymentModal').classList.remove('flex');
            }

            function confirmPayment() {
                const commissionId = document.getElementById('commissionId').value;
                const markAsPaid = document.getElementById('markAsPaid').value === 'true';

                const url = markAsPaid ?
                    `/admin/commissions/${commissionId}/mark-paid` :
                    `/admin/commissions/${commissionId}/mark-unpaid`;

                $.ajax({
                    url: url,
                    method: 'PATCH',
                    success: function(data) {
                        if (data.success) {
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        alert('Erreur: ' + (xhr.responseJSON?.message || 'Une erreur est survenue'));
                    }
                });
            }
        </script>
    @endpush
@endsection
