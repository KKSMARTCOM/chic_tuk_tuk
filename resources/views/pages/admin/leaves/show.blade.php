@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Détails des Congés</h1>
                    <p class="text-gray-600">{{ $driver->name }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.leaves.index') }}"
                        class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-arrow-left mr-2"></i> Retour
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <!-- Contract Info -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 p-4 bg-blue-50 rounded-lg">
                <div>
                    <h3 class="text-sm font-medium text-blue-800">Date de début</h3>
                    <p class="text-lg font-semibold text-blue-900">
                        {{ $leaveInfo['contract_start'] ? formatDateFr($leaveInfo['contract_start']) : 'N/A' }}
                    </p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-blue-800">Durée du contrat</h3>
                    <p class="text-lg font-semibold text-blue-900">{{ $leaveInfo['contract_months'] ?? 'N/A' }} mois</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-blue-800">Date de fin</h3>
                    <p class="text-lg font-semibold text-blue-900">
                        @if ($leaveInfo['contract_start'] && $leaveInfo['contract_months'])
                            @php
                                $end = \Carbon\Carbon::parse($leaveInfo['contract_start'])->addMonths(
                                    (int) $leaveInfo['contract_months'],
                                );
                            @endphp
                            {{ formatDateFr($end) }}
                        @else
                            N/A
                        @endif
                    </p>
                </div>
            </div>

            <!-- Leave Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-800">Jours par mois</h3>
                    <p class="text-2xl font-bold text-blue-900">{{ $leaveInfo['leave_days_per_month'] }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-800">Jours total</h3>
                    <p class="text-2xl font-bold text-green-900">{{ $leaveInfo['total_leave_days'] }}</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-yellow-800">Jours utilisés</h3>
                    <p class="text-2xl font-bold text-yellow-900">{{ $leaveInfo['leave_days_used'] }}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-purple-800">Jours restants</h3>
                    <p class="text-2xl font-bold text-purple-900">{{ $leaveInfo['remaining_leave_days'] }}</p>
                </div>
            </div>

            <!-- Pending Requests -->
            @if ($pendingRequests->count() > 0)
                <div class="mb-8 p-6 bg-yellow-50 rounded-lg border-2 border-yellow-200">
                    <h2 class="text-xl font-semibold text-yellow-900 mb-4">Demandes en attente</h2>
                    <div class="space-y-4">
                        @foreach ($pendingRequests as $request)
                            <div class="bg-white p-4 rounded-lg border border-yellow-200">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <p class="text-sm text-gray-600">Demande du
                                            {{ formatDateFr($request->created_at) }}</p>
                                        <p class="font-semibold text-gray-800">Dates demandées:</p>
                                        <div class="flex flex-wrap gap-2 mt-2">
                                            @foreach ($request->dates as $date)
                                                <span
                                                    class="inline-block bg-yellow-100 text-yellow-800 px-3 py-1 rounded text-sm">
                                                    {{ formatDateFr($date) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-3 mt-4">
                                    <button type="button"
                                        onclick="openApproveModal('{{ $request->id }}', '{{ route('admin.leave.requests.approve', $request) }}')"
                                        class="flex-1 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                        Approuver
                                    </button>
                                    <button type="button" onclick="openRejectModal('{{ $request->id }}')"
                                        class="flex-1 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                        Rejeter
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Approved Leaves for Current Month -->
            @if ($approvedRequests->count() > 0)
                <div class="mb-8 p-6 bg-green-50 rounded-lg border-2 border-green-200">
                    <h2 class="text-xl font-semibold text-green-900 mb-4">Congés approuvés ce mois</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($approvedRequests as $request)
                            @foreach ($request->dates as $date)
                                <div class="bg-white p-4 rounded-lg border border-green-200">
                                    <p class="font-semibold text-green-800">
                                        {{ formatDateFr($date) }}
                                    </p>
                                    <p class="text-sm text-gray-600">Approuvé le
                                        {{ formatDateFr($request->updated_at) }}</p>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- All Taken Leaves -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Tous les jours de congé pris</h2>
                @if (count($leaveInfo['leave_dates']) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($leaveInfo['leave_dates'] as $date)
                            <div class="bg-red-50 p-4 rounded-lg flex justify-between items-center border border-red-200">
                                <span class="text-red-800 font-medium">{{ formatDateFr($date) }}</span>
                                <button type="button" class="text-red-600 hover:text-red-800 text-sm font-medium"
                                    onclick="openRevokeModal('{{ $date }}', '{{ route('admin.leaves.revoke', $driver) }}')">
                                    Révoquer
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">Aucun congé pris pour le moment.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div id="approveModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Confirmer l'approbation</h2>
            <p class="text-gray-700 mb-6">Êtes-vous sûr de vouloir approuver cette demande de congé ?</p>
            <form id="approveForm" method="POST">
                @csrf
                <div class="flex gap-3">
                    <button type="button" onclick="closeApproveModal()"
                        class="flex-1 bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
                        Annuler
                    </button>
                    <button type="submit" class="flex-1 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        Approuver
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Rejeter la demande</h2>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Motif du refus <span class="text-red-500">*</span>
                    </label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                        placeholder="Veuillez expliquer pourquoi cette demande est rejetée..." required></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeRejectModal()"
                        class="flex-1 bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
                        Annuler
                    </button>
                    <button type="submit" class="flex-1 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Rejeter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Revoke Modal -->
    <div id="revokeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Révoquer le congé</h2>
            <p class="text-gray-700 mb-6">Êtes-vous sûr de vouloir révoquer ce congé ? Cette action libérera un jour pour le
                conducteur.</p>
            <form id="revokeForm" method="POST">
                @csrf
                <input type="hidden" id="leave_date" name="leave_date" value="">
                <div class="flex gap-3">
                    <button type="button" onclick="closeRevokeModal()"
                        class="flex-1 bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
                        Annuler
                    </button>
                    <button type="submit" class="flex-1 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Révoquer
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function openApproveModal(requestId, action) {
                const form = document.getElementById('approveForm');
                form.action = action;
                document.getElementById('approveModal').classList.remove('hidden');
            }

            function closeApproveModal() {
                document.getElementById('approveModal').classList.add('hidden');
            }

            function openRejectModal(requestId) {
                const form = document.getElementById('rejectForm');
                form.action = `/admin/leave/requests/${requestId}/reject`;
                document.getElementById('rejectModal').classList.remove('hidden');
            }

            function closeRejectModal() {
                document.getElementById('rejectModal').classList.add('hidden');
                document.getElementById('rejection_reason').value = '';
            }

            function openRevokeModal(date, action) {
                const form = document.getElementById('revokeForm');
                form.action = action;
                document.getElementById('leave_date').value = date;
                document.getElementById('revokeModal').classList.remove('hidden');
            }

            function closeRevokeModal() {
                document.getElementById('revokeModal').classList.add('hidden');
                document.getElementById('leave_date').value = '';
            }

            // Close modals when clicking outside
            document.getElementById('approveModal')?.addEventListener('click', function(e) {
                if (e.target === this) closeApproveModal();
            });
            document.getElementById('rejectModal')?.addEventListener('click', function(e) {
                if (e.target === this) closeRejectModal();
            });
            document.getElementById('revokeModal')?.addEventListener('click', function(e) {
                if (e.target === this) closeRevokeModal();
            });
        </script>
    @endpush
@endsection
