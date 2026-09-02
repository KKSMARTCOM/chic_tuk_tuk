@extends('layouts.app')

@section('content')

    {{-- Header --}}
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">Gestion des Contrats</h1>
                <p class="text-xs md:text-base text-gray-600">Contrats agents et propriétaires-véhicules</p>
            </div>

        </div>
    </div>

    {{-- Onglets --}}
    <div class="bg-white rounded-lg shadow-md">
        {{-- ===== ONGLET CONTRATS AGENTS ===== --}}
        <div class="tab-pane p-6">
            @if ($contracts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="datatable-agent">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Ajouter le
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Agent</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Véhicule</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Début</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Durée</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Congés</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Statut</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($contracts as $contract)
                                @php
                                    $usedDays = $contract->used_leave_days;
                                    $accruedDays = $contract->accrued_leave_days;
                                    $available = $accruedDays - $usedDays;
                                    $surplus = $available < 0 ? abs($available) : 0;
                                    $leavePct =
                                        $accruedDays > 0 ? min(100, round(($usedDays / $accruedDays) * 100)) : 0;
                                    $totalPaid = $contract->total_paid;
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="text-sm px-4 py-3 whitespace-nowrap">
                                        {{ formatDateTimeFr($contract->created_at) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($contract->driver->user->name ?? 'A') }}"
                                                class="w-8 h-8 rounded-full shrink-0">
                                            <div class="min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    {{ $contract->driver->user->name ?? 'N/A' }}
                                                </p>
                                                <p class="text-xs text-gray-500 truncate">
                                                    {{ $contract->driver->user->phone ?? '' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                        {{ $contract->vehicle->vehicle_number ?? 'N/A' }}
                                        <span
                                            class="text-xs text-gray-400 block">{{ vehiculeType($contract->vehicle->vehicle_type) ?? '' }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                        {{ formatDateFr($contract->start_date) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                        {{ $contract->contract_months }} mois
                                        <span class="text-xs text-gray-400 block">
                                            {{ $contract->months_elapsed }}m écoulés
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-xs text-gray-600 mb-1 flex items-center justify-between">
                                            <span>{{ $usedDays }}j / {{ $accruedDays }}j</span>
                                            @if ($surplus > 0)
                                                <span class="text-red-500 font-medium">+{{ $surplus }}j surplus</span>
                                            @else
                                                <span class="text-green-600">{{ $available }}j dispo</span>
                                            @endif
                                        </div>
                                        <div class="w-24 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full {{ $surplus > 0 ? 'bg-red-400' : 'bg-purple-500' }}"
                                                style="width: {{ $leavePct }}%"></div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $contract->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $contract->status === 'active' ? 'Actif' : 'Terminé' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.driver-contracts.show', $contract) }}"
                                                class="text-blue-600 hover:text-blue-800" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button onclick="openEditAgentModal({{ $contract->toJson() }})"
                                                class="text-green-600 hover:text-green-800" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @if ($contract->status === 'active')
                                                <button
                                                    onclick="openEndModal('{{ $contract->id }}', '{{ $contract->driver->user->name ?? '' }}')"
                                                    class="text-orange-500 hover:text-orange-700" title="Terminer">
                                                    <i class="fas fa-stop-circle"></i>
                                                </button>
                                            @endif
                                            <button
                                                onclick="openDeleteModal('driver-contracts', '{{ $contract->id }}', '{{ $contract->driver->user->name ?? '' }}')"
                                                class="text-red-600 hover:text-red-800" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-file-contract text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">Aucun contrat agent trouvé.</p>
                </div>
            @endif
        </div>

    </div>

    {{-- ===== MODAL DÉTAIL ===== --}}
    <div id="detailModal" class="fixed inset-0 px-4 bg-black bg-opacity-50 hidden items-center justify-center z-30">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800" id="detailTitle">Détails du contrat</h3>
                <button onclick="closeModal('detailModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6 space-y-2" id="detailBody"></div>
            <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
                <button onclick="closeModal('detailModal')"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Fermer
                </button>
            </div>
        </div>
    </div>

    {{-- ===== MODAL MODIFIER CONTRAT AGENT ===== --}}
    <div id="editAgentModal" class="fixed inset-0 px-4 bg-black bg-opacity-50 hidden items-center justify-center z-30">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">Modifier le contrat agent</h3>
                <button onclick="closeModal('editAgentModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editAgentForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <input type="hidden" name="vehicle_id" id="vehicle_id">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de début</label>
                        <input type="date" name="start_date" id="edit_agent_start_date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Durée (mois)</label>
                        <select name="contract_months" id="edit_agent_months"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="24">24 mois</option>
                            <option value="30">30 mois</option>
                            <option value="36">36 mois</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                        <select name="status" id="edit_agent_status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="active">Actif</option>
                            <option value="ended">Terminé</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin</label>
                        <input type="date" name="end_date" id="edit_agent_end_date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('editAgentModal')"
                        class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Annuler
                    </button>
                    <button type="submit"
                        class="flex-1 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== MODAL TERMINER CONTRAT AGENT ===== --}}
    <div id="endModal" class="fixed inset-0 px-4 bg-black bg-opacity-50 hidden items-center justify-center z-30">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">Terminer le contrat</h3>
                <button onclick="closeModal('endModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="endForm" method="POST" class="p-6 space-y-4">
                @csrf
                <p class="text-sm text-gray-600">
                    Le contrat de <strong id="endAgentName"></strong> sera clôturé.
                    Une pause véhicule sera créée automatiquement.
                </p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Date de fin <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="end_date" required value="{{ now()->toDateString() }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Raison <span class="text-red-500">*</span>
                    </label>
                    <select name="end_reason" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="demission">Démission</option>
                        <option value="abandon">Abandon</option>
                        <option value="fin_contrat">Fin de contrat</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="end_notes" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                        placeholder="Informations complémentaires..."></textarea>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                    <p class="text-sm text-amber-700 flex items-start gap-2">
                        <i class="fas fa-triangle-exclamation mt-0.5 shrink-0"></i>
                        Une pause véhicule de type "Changement d'agent" sera créée automatiquement
                        à partir de la date de fin.
                    </p>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('endModal')"
                        class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Annuler
                    </button>
                    <button type="submit"
                        class="flex-1 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition font-semibold">
                        <i class="fas fa-stop-circle mr-2"></i>Confirmer la clôture
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== MODAL SUPPRESSION ===== --}}
    <div id="deleteModal" class="fixed inset-0 px-4 bg-black bg-opacity-50 hidden items-center justify-center z-30">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-red-600">
                    <i class="fas fa-triangle-exclamation mr-2"></i>Supprimer le contrat
                </h3>
            </div>
            <div class="p-6">
                <p class="text-gray-600 mb-6">
                    Êtes-vous sûr de vouloir supprimer le contrat de
                    <strong id="deleteContractName"></strong> ?
                    Cette action est irréversible.
                </p>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex gap-3">
                        <button type="button" onclick="closeModal('deleteModal')"
                            class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                            Annuler
                        </button>
                        <button type="submit"
                            class="flex-1 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold">
                            <i class="fas fa-trash mr-2"></i>Supprimer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // ── DataTable ────────────────────────────────────────────
            $("#datatable-agent").DataTable({
                order: [
                    [0, "asc"]
                ],
                language: {
                    processing: "Traitement...",
                    search: "Rechercher : ",
                    lengthMenu: "Afficher _MENU_ éléments",
                    info: "Affichage _START_ à _END_ sur _TOTAL_",
                    zeroRecords: "Aucun véhicule trouvé",
                    emptyTable: "Aucune donnée",
                },
                initComplete: function() {
                    if (typeof $.fn.select2 !== "undefined") {
                        $(".dataTables_length select").select2({
                            minimumResultsForSearch: Infinity
                        });
                    }
                },
            });

            // ===== MODALS =====
            function openModal(id) {
                document.getElementById(id).classList.remove('hidden');
                document.getElementById(id).classList.add('flex');
            }

            function closeModal(id) {
                document.getElementById(id).classList.add('hidden');
                document.getElementById(id).classList.remove('flex');
            }

            function formatAmount(n) {
                return Number(n).toLocaleString('fr-FR') + ' FCFA';
            }

            // ===== MODIFIER AGENT =====
            function openEditAgentModal(contract) {
                document.getElementById('edit_agent_start_date').value = contract.start_date ?
                    new Date(contract.start_date).toISOString().split('T')[0] :
                    '';

                document.getElementById('edit_agent_end_date').value = contract.start_date ?
                    new Date(contract.start_date).toISOString().split('T')[0] :
                    '';

                document.getElementById('vehicle_id').value = contract.vehicle.id;
                document.getElementById('edit_agent_months').value = contract.contract_months ?? 24;
                document.getElementById('edit_agent_status').value = contract.status ?? 'active';
                document.getElementById('editAgentForm').action = `/admin/driver-contracts/${contract.id}`;

                openModal('editAgentModal');
            }

            // ===== TERMINER =====
            function openEndModal(contractId, agentName) {
                document.getElementById('endAgentName').textContent = agentName;
                document.getElementById('endForm').action = `/admin/driver-contracts/${contractId}/end`;
                openModal('endModal');
            }

            // ===== SUPPRIMER =====
            function openDeleteModal(resource, id, name) {
                document.getElementById('deleteContractName').textContent = name;
                document.getElementById('deleteForm').action = `/admin/${resource}/${id}`;
                openModal('deleteModal');
            }
        </script>
    @endpush

@endsection
