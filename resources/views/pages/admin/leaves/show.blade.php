@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">Détails des Pauses</h1>
                <p class="text-sm md:text-base text-gray-600">{{ $driver->name }}</p>
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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-blue-800">Jours par mois</h3>
                <p class="text-2xl font-bold text-blue-900">{{ $leaveInfo['leave_days_per_month'] }}</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-purple-800">Jours total</h3>
                <p class="text-2xl font-bold text-purple-900">{{ $leaveInfo['total_leave_days'] }}</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-yellow-800">Jours utilisés</h3>
                <p class="text-2xl font-bold text-yellow-900">{{ $leaveInfo['leave_days_used'] }}</p>
            </div>
            <div class="bg-indigo-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-indigo-800">Disponibles à date</h3>
                <p class="text-2xl font-bold text-indigo-900">{{ $leaveInfo['available_leave_days'] }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-green-800">Jours restants</h3>
                <p class="text-2xl font-bold text-green-900">{{ $leaveInfo['remaining_leave_days'] }}</p>
            </div>
        </div>

        <!-- Add Instant Leave Form -->
        @if ($ongoingLeave)
            <div class="mb-8 p-6 bg-orange-50 rounded-lg border-2 border-orange-200">
                <h2 class="text-xl font-semibold text-orange-900 mb-2">Pause en cours</h2>
                <p class="text-gray-700">
                    Depuis le <strong>{{ formatDateFr($ongoingLeave->start_date) }}</strong>
                    — {{ $ongoingLeave->requested_days }} jour(s) demandé(s)
                    @if ($ongoingLeave->is_overdue)
                        <span class="text-red-600 font-semibold">(dépasse la durée prévue)</span>
                    @endif
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    Fin prévue le {{ formatDateFr($ongoingLeave->expected_end_date) }}
                </p>
                <div class="flex gap-3 mt-4">
                    <button type="button"
                        onclick="openCorrectOngoingModal(
                    '{{ route('admin.leaves.ongoing.update', $ongoingLeave) }}',
                    '{{ $ongoingLeave->start_date->toDateString() }}',
                    '{{ $ongoingLeave->requested_days }}'
                )"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium">
                        <i class="fas fa-edit"></i> Corriger
                    </button>
                    <button type="button" onclick="openEndLeaveModal('{{ route('admin.leaves.end', $ongoingLeave) }}')"
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 font-medium">
                        Mettre fin à la pause
                    </button>
                </div>
            </div>
        @else
            <div class="mb-8 p-6 bg-indigo-50 rounded-lg border-2 border-indigo-200">
                <h2 class="text-xl font-semibold text-indigo-900 mb-4">Ajouter une Pause</h2>

                <div class="flex gap-3 mb-4">
                    <button type="button" onclick="switchLeaveTab('ongoing')" id="tabOngoingBtn"
                        class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium">Pause en cours</button>
                    <button type="button" onclick="switchLeaveTab('historical')" id="tabHistoricalBtn"
                        class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium">Pause passée</button>
                </div>

                <form id="ongoingLeaveForm" action="{{ route('admin.leaves.add-ongoing', $driver->driver->id) }}"
                    method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                            <input type="date" name="start_date" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de jours demandés</label>
                            <input type="number" name="requested_days" min="1" value="1" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        </div>
                    </div>
                    <p class="text-xs text-indigo-700 mb-4">ℹ️ Cette pause démarre immédiatement (ou à la date choisie) et
                        restera active jusqu'à ce qu'un administrateur y mette fin, même si elle dépasse le nombre de jours
                        indiqué.</p>
                    <button type="submit"
                        class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">
                        Démarrer la pause
                    </button>
                </form>

                <form id="historicalLeaveForm" action="{{ route('admin.leaves.add-historical', $driver->driver->id) }}"
                    method="POST" class="hidden">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                            <input type="date" name="start_date" max="{{ now()->subDay()->toDateString() }}" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de jours</label>
                            <input type="number" name="requested_days" min="1" value="1" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mb-4">ℹ️ Utilisez ceci uniquement pour une pause déjà entièrement
                        terminée. Aucun impact sur le statut actuel de l'agent.</p>
                    <button type="submit"
                        class="w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 font-medium">
                        Ajouter la pause passée
                    </button>
                </form>
            </div>
        @endif

        <!-- Pending Requests -->
        @if ($pendingRequests->count() > 0)
            <div class="mb-8 p-6 bg-yellow-50 rounded-lg border-2 border-yellow-200">
                <h2 class="text-xl font-semibold text-yellow-900 mb-4">Demandes en attente</h2>
                <div class="space-y-4">
                    @foreach ($pendingRequests as $request)
                        <div class="bg-white p-4 rounded-lg border border-yellow-200">
                            <p class="text-sm text-gray-600">Demande du {{ formatDateFr($request->created_at) }}</p>
                            <p class="font-semibold text-gray-800">
                                Du {{ formatDateFr($request->start_date) }} — {{ $request->requested_days }} jour(s)
                                demandé(s)
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Fin prévue le {{ formatDateFr($request->expected_end_date) }}
                            </p>
                            <div class="flex gap-3 mt-4">
                                <button type="button"
                                    onclick="openApproveModal('{{ route('admin.leave.requests.approve', $request) }}')"
                                    class="flex-1 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Approuver</button>
                                <button type="button" onclick="openRejectModal('{{ $request->id }}')"
                                    class="flex-1 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Rejeter</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- All Taken Leaves -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Historique des pauses</h2>
            @forelse ($history as $leave)
                <div class="bg-gray-50 p-4 rounded-lg flex justify-between items-center border border-gray-200 mb-2">
                    <div>
                        <span class="font-medium text-gray-800">
                            {{ formatDateFr($leave->start_date) }} → {{ formatDateFr($leave->end_date) }}
                        </span>
                        <span class="text-sm text-gray-500 ml-2">({{ $leave->effective_days }} jour(s) effectif(s))</span>
                        @if ($leave->is_historical)
                            <span class="text-xs bg-gray-200 text-gray-700 px-2 py-0.5 rounded ml-2">Historique</span>
                        @endif
                    </div>

                    @if ($leave->is_historical)
                        <div class="flex gap-3">
                            <button type="button" class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                onclick="openEditHistoricalModal(
                            '{{ route('admin.leaves.history.update', $leave) }}',
                            '{{ $leave->start_date->toDateString() }}',
                            '{{ $leave->requested_days }}'
                        )">
                                <i class="fas fa-edit"></i> Modifier
                            </button>
                            <button type="button" class="text-red-600 hover:text-red-800 text-sm font-medium"
                                onclick="openDeleteHistoricalModal('{{ route('admin.leaves.history.destroy', $leave) }}')">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-gray-500">Aucune pause terminée pour le moment.</p>
            @endforelse
        </div>
    </div>

    <!-- Approve Modal -->
    <div id="approveModal" class="fixed px-4 inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-20">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Confirmer l'approbation</h2>
            <p class="text-gray-700 mb-6">Êtes-vous sûr de vouloir approuver cette demande de Pause ?</p>
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
    <div id="rejectModal" class="fixed px-4 inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-20">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
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

    <div id="endLeaveModal" class="fixed px-4 inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-20">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Mettre fin à la pause</h2>
            <form id="endLeaveForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                    <input type="date" name="end_date" value="{{ now()->toDateString() }}" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeEndLeaveModal()"
                        class="flex-1 bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Annuler</button>
                    <button type="submit"
                        class="flex-1 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Terminer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Historical Leave Modal -->
    <div id="editHistoricalModal"
        class="fixed px-4 inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-20">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Modifier la pause historique</h2>
            <form id="editHistoricalForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="grid grid-cols-1 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                        <input type="date" name="start_date" id="editHistoricalStartDate"
                            max="{{ now()->subDay()->toDateString() }}" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de jours</label>
                        <input type="number" name="requested_days" id="editHistoricalDays" min="1" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeEditHistoricalModal()"
                        class="flex-1 bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Annuler</button>
                    <button type="submit"
                        class="flex-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Correct Ongoing Leave Modal -->
    <div id="correctOngoingModal"
        class="fixed px-4 inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-20">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Corriger la pause en cours</h2>
            <p class="text-sm text-gray-500 mb-4">
                La modification de la date de début répercute automatiquement le changement sur la pause du véhicule et sur
                la disponibilité de l'agent.
            </p>
            <form id="correctOngoingForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="grid grid-cols-1 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                        <input type="date" name="start_date" id="correctOngoingStartDate" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de jours demandés</label>
                        <input type="number" name="requested_days" id="correctOngoingDays" min="1" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeCorrectOngoingModal()"
                        class="flex-1 bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Annuler</button>
                    <button type="submit"
                        class="flex-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Historical Leave Modal -->
    <div id="deleteHistoricalModal"
        class="fixed px-4 inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-20">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Supprimer la pause</h2>
            <p class="text-gray-700 mb-6">Êtes-vous sûr de vouloir supprimer cette pause historique ? Cette action est
                irréversible et mettra à jour le compteur de jours utilisés.</p>
            <form id="deleteHistoricalForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex gap-3">
                    <button type="button" onclick="closeDeleteHistoricalModal()"
                        class="flex-1 bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Annuler</button>
                    <button type="submit"
                        class="flex-1 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Supprimer</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function openApproveModal(action) {
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

            function switchLeaveTab(tab) {
                document.getElementById('ongoingLeaveForm').classList.toggle('hidden', tab !== 'ongoing');
                document.getElementById('historicalLeaveForm').classList.toggle('hidden', tab !== 'historical');
                document.getElementById('tabOngoingBtn').className = tab === 'ongoing' ?
                    'flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium' :
                    'flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium';
                document.getElementById('tabHistoricalBtn').className = tab === 'historical' ?
                    'flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium' :
                    'flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium';
            }

            function openEndLeaveModal(action) {
                document.getElementById('endLeaveForm').action = action;
                document.getElementById('endLeaveModal').classList.remove('hidden');
                document.getElementById('endLeaveModal').classList.add('flex');
            }

            function closeEndLeaveModal() {
                document.getElementById('endLeaveModal').classList.add('hidden');
                document.getElementById('endLeaveModal').classList.remove('flex');
            }

            function openEditHistoricalModal(action, startDate, requestedDays) {
                document.getElementById('editHistoricalForm').action = action;
                document.getElementById('editHistoricalStartDate').value = startDate;
                document.getElementById('editHistoricalDays').value = requestedDays;
                document.getElementById('editHistoricalModal').classList.remove('hidden');
                document.getElementById('editHistoricalModal').classList.add('flex');
            }

            function closeEditHistoricalModal() {
                document.getElementById('editHistoricalModal').classList.add('hidden');
                document.getElementById('editHistoricalModal').classList.remove('flex');
            }

            function openDeleteHistoricalModal(action) {
                document.getElementById('deleteHistoricalForm').action = action;
                document.getElementById('deleteHistoricalModal').classList.remove('hidden');
                document.getElementById('deleteHistoricalModal').classList.add('flex');
            }

            function closeDeleteHistoricalModal() {
                document.getElementById('deleteHistoricalModal').classList.add('hidden');
                document.getElementById('deleteHistoricalModal').classList.remove('flex');
            }

            function openCorrectOngoingModal(action, startDate, requestedDays) {
                document.getElementById('correctOngoingForm').action = action;
                document.getElementById('correctOngoingStartDate').value = startDate;
                document.getElementById('correctOngoingDays').value = requestedDays;
                document.getElementById('correctOngoingModal').classList.remove('hidden');
                document.getElementById('correctOngoingModal').classList.add('flex');
            }

            function closeCorrectOngoingModal() {
                document.getElementById('correctOngoingModal').classList.add('hidden');
                document.getElementById('correctOngoingModal').classList.remove('flex');
            }

            // Close modals when clicking outside
            document.getElementById('approveModal')?.addEventListener('click', function(e) {
                if (e.target === this) closeApproveModal();
            });
            document.getElementById('rejectModal')?.addEventListener('click', function(e) {
                if (e.target === this) closeRejectModal();
            });
            document.getElementById('endLeaveModal')?.addEventListener('click', function(e) {
                if (e.target === this) closeEndLeaveModal();
            });
        </script>
    @endpush
@endsection
