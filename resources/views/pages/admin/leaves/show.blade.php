@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Détails des Pauses</h1>
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
        <div class="mb-8 p-6 bg-indigo-50 rounded-lg border-2 border-indigo-200">
            <h2 class="text-xl font-semibold text-indigo-900 mb-4">Ajouter une Pause instantanée</h2>
            <p class="text-sm text-indigo-700 mb-6">Permet d'ajouter une Pause directement sans passer par le processus de
                demande.</p>

            <form action="{{ route('admin.leaves.add-instant', $driver->driver->id) }}" method="POST"
                id="addInstantLeaveForm">
                @csrf
                <div class="space-y-4">
                    <!-- Date Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Sélectionnez les dates (mois courant)
                        </label>
                        <div class="mb-4">
                            <input type="date" id="adminLeaveDate"
                                class="border border-gray-300 rounded-lg px-4 py-2 w-full"
                                min="{{ now()->toDateString() }}" max="{{ now()->endOfMonth()->toDateString() }}"
                                title="Les dates doivent être dans le mois courant">
                            <button type="button" onclick="adminAddDate()"
                                class="mt-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 w-full font-medium">
                                + Ajouter une date
                            </button>
                        </div>

                        <!-- Selected Dates Display -->
                        <div id="adminSelectedDatesContainer" class="space-y-2">
                            <p class="text-xs text-gray-500 font-semibold uppercase">Dates sélectionnées:</p>
                            <div id="adminSelectedDates"
                                class="flex flex-wrap gap-2 min-h-12 p-3 bg-white rounded-lg border-2 border-dashed border-indigo-300">
                                <p class="text-gray-400 text-sm w-full text-center py-2">Aucune date sélectionnée</p>
                            </div>
                        </div>

                        <!-- Hidden inputs for form submission -->
                        <div id="adminDatesInputs"></div>
                        <p id="adminDateError" class="text-sm text-red-600 mt-2 hidden"></p>
                    </div>

                    <!-- Validation Info -->
                    <div class="p-3 bg-indigo-100 border border-indigo-300 rounded text-sm text-indigo-800">
                        ℹ️ Les jours doivent être consécutifs. Agent dispose de
                        <strong>{{ $leaveInfo['available_leave_days'] }}</strong> jour(s) disponible(s).
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 pt-2">
                        <button type="submit" id="adminSubmitBtn"
                            class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium transition disabled:bg-gray-300"
                            disabled>
                            Ajouter la Pause
                        </button>
                        <button type="button" onclick="adminClearDates()"
                            class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 font-medium transition">
                            Réinitialiser
                        </button>
                    </div>
                </div>
            </form>
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
                <h2 class="text-xl font-semibold text-green-900 mb-4">Pauses approuvés ce mois</h2>
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
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Tous les jours de Pause pris</h2>
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
                <p class="text-gray-500">Aucun Pause pris pour le moment.</p>
            @endif
        </div>
    </div>

    <!-- Approve Modal -->
    <div id="approveModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-20">
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
    <div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-20">
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

    <!-- Revoke Modal -->
    <div id="revokeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-20">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Révoquer le Pause</h2>
            <p class="text-gray-700 mb-6">Êtes-vous sûr de vouloir révoquer ce Pause ? Cette action libérera un jour pour
                le
                Agent.</p>
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

            // Admin instant leave form functions
            let adminSelectedDates = [];
            const adminMaxDays = {{ $leaveInfo['available_leave_days'] }};

            function adminSetError(message) {
                const error = document.getElementById('adminDateError');
                error.textContent = message;
                error.classList.remove('hidden');
            }

            function adminClearError() {
                const error = document.getElementById('adminDateError');
                error.textContent = '';
                error.classList.add('hidden');
            }

            function adminAddDate() {
                adminClearError();
                const dateInput = document.getElementById('adminLeaveDate');
                const date = dateInput.value;

                if (!date) {
                    adminSetError('Veuillez sélectionner une date.');
                    return;
                }

                if (adminSelectedDates.includes(date)) {
                    adminSetError('Cette date est déjà sélectionnée.');
                    return;
                }

                if (adminSelectedDates.length >= adminMaxDays) {
                    adminSetError(`Vous ne pouvez ajouter que ${adminMaxDays} jour(s) maximum.`);
                    return;
                }

                adminSelectedDates.push(date);
                adminSelectedDates.sort();
                adminUpdateDisplay();
                dateInput.value = '';
                dateInput.focus();
            }

            function adminRemoveDate(date) {
                adminSelectedDates = adminSelectedDates.filter(d => d !== date);
                adminUpdateDisplay();
            }

            function adminUpdateDisplay() {
                const container = document.getElementById('adminSelectedDates');
                const inputsContainer = document.getElementById('adminDatesInputs');
                const submitBtn = document.getElementById('adminSubmitBtn');

                if (adminSelectedDates.length === 0) {
                    container.innerHTML =
                        '<p class="text-gray-400 text-sm w-full text-center py-2">Aucune date sélectionnée</p>';
                    inputsContainer.innerHTML = '';
                    submitBtn.disabled = true;
                    return;
                }

                container.innerHTML = adminSelectedDates.map(date => {
                    const dateObj = new Date(date + 'T00:00:00');
                    const dayName = dateObj.toLocaleDateString('fr-FR', {
                        weekday: 'short'
                    });
                    return `
                        <div class="inline-flex items-center bg-indigo-100 text-indigo-800 px-3 py-2 rounded-lg text-sm font-medium">
                            ${dateObj.toLocaleDateString('fr-FR')} (${dayName})
                            <button type="button" onclick="adminRemoveDate('${date}')" class="ml-2 hover:text-indigo-600 font-bold">
                                ✕
                            </button>
                        </div>
                    `;
                }).join('');

                inputsContainer.innerHTML = adminSelectedDates.map(date => `
                    <input type="hidden" name="dates[]" value="${date}">
                `).join('');

                submitBtn.disabled = false;
            }

            function adminClearDates() {
                adminSelectedDates = [];
                document.getElementById('adminLeaveDate').value = '';
                adminClearError();
                adminUpdateDisplay();
            }

            // Allow Enter key to add date
            document.getElementById('adminLeaveDate').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    adminAddDate();
                    e.preventDefault();
                }
            });

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
