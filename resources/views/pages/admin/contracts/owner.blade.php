@extends('layouts.app')

@section('content')

    {{-- Header --}}
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">Gestion des Contrats</h1>
                <p class="text-xs md:text-base text-gray-600">Contrats propriétaires-véhicules</p>
            </div>
            <button onclick="openCreateModal()"
                class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition flex items-center gap-2">
                <i class="fas fa-plus"></i> Nouveau contrat
            </button>
        </div>
    </div>

    {{-- Onglets --}}
    <div class="bg-white rounded-lg shadow-md">
        {{-- ===== ONGLET CONTRATS PROPRIÉTAIRES ===== --}}
        <div class="tab-pane p-6">
            @if ($contracts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="datatable-owner">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Ajouter le
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Propriétaire
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Véhicule</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Début</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Montant total
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Progression
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Mensualité
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Statut</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($contracts as $contract)
                                @php
                                    $stats = $contract->stats;
                                    $pct = $stats['progress_percent'];
                                    $isSolde = $stats['remaining'] <= 0;
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="text-sm px-4 py-3 whitespace-nowrap">
                                        {{ formatDateTimeFr($contract->created_at) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($contract->owner->name ?? 'P') }}"
                                                class="w-8 h-8 rounded-full shrink-0">
                                            <div class="min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    {{ $contract->owner->name ?? 'N/A' }}
                                                </p>
                                                <p class="text-xs text-gray-500 truncate">
                                                    {{ $contract->owner->phone ?? '' }}
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
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        {{ number_format($stats['total_amount'], 0, ',', ' ') }} FCFA
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-2 mb-1">
                                            <div class="w-24 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full {{ $isSolde ? 'bg-green-500' : 'bg-purple-500' }}"
                                                    style="width: {{ $pct }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-500">{{ $pct }}%</span>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ number_format($stats['total_paid'], 0, ',', ' ') }} /
                                            {{ number_format($stats['total_amount'], 0, ',', ' ') }} FCFA
                                            @if ($stats['surplus'] > 0)
                                                <span class="text-amber-500 font-medium ml-1">
                                                    +{{ number_format($stats['surplus'], 0, ',', ' ') }} surplus
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                        {{ number_format($stats['monthly_payment'], 0, ',', ' ') }} FCFA
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $contract->status === 'active'
                                            ? 'bg-green-100 text-green-700'
                                            : ($contract->status === 'completed'
                                                ? 'bg-blue-100 text-blue-700'
                                                : 'bg-red-100 text-red-700') }}">
                                            {{ match ($contract->status) {
                                                'active' => 'Actif',
                                                'completed' => 'Soldé',
                                                default => 'Annulé',
                                            } }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <button
                                                onclick="openDetailModal('owner', '{{ $contract->id }}', {{ $contract->toJson() }}, {{ json_encode($stats) }})"
                                                class="text-blue-600 hover:text-blue-800" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button onclick="openEditOwnerModal({{ $contract->toJson() }})"
                                                class="text-green-600 hover:text-green-800" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button
                                                onclick="openDeleteModal('vehicle-contracts', '{{ $contract->id }}', '{{ $contract->owner->name ?? '' }}')"
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
                    <p class="text-gray-500">Aucun contrat propriétaire trouvé.</p>
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

    {{-- ===== MODAL MODIFIER CONTRAT PROPRIO ===== --}}
    <div id="editOwnerModal" class="fixed inset-0 px-4 bg-black bg-opacity-50 hidden items-center justify-center z-30">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">Modifier le contrat propriétaire</h3>
                <button onclick="closeModal('editOwnerModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editOwnerForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Montant total (FCFA)</label>
                        <input type="number" name="total_amount" id="edit_owner_total"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mensualité (FCFA)</label>
                        <input type="number" name="monthly_payment" id="edit_owner_monthly"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de début</label>
                        <input type="date" name="start_date" id="edit_owner_start"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin</label>
                        <input type="date" name="end_date" id="edit_owner_end"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                        <select name="status" id="edit_owner_status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="active">Actif</option>
                            <option value="completed">Soldé</option>
                            <option value="cancelled">Annulé</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" id="edit_owner_notes" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('editOwnerModal')"
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

    {{-- ===== MODAL CRÉER ===== --}}
    <div id="createModal" class="fixed inset-0 px-4 bg-black bg-opacity-50 hidden items-center justify-center z-30">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800" id="createModalTitle">Nouveau contrat</h3>
                <button onclick="closeModal('createModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-4 border-b border-gray-100">
                <div class="flex gap-2">
                    <button onclick="setCreateType('agent')" id="create-type-agent"
                        class="flex-1 py-2 text-sm rounded-lg bg-purple-100 text-purple-700 font-medium border border-purple-200">
                        <i class="fas fa-user mr-1"></i> Contrat agent
                    </button>
                    <button onclick="setCreateType('owner')" id="create-type-owner"
                        class="flex-1 py-2 text-sm rounded-lg bg-gray-100 text-gray-600 border border-gray-200">
                        <i class="fas fa-store mr-1"></i> Contrat propriétaire
                    </button>
                </div>
            </div>

            {{-- Formulaire agent --}}
            <form id="createAgentForm" action="{{ route('admin.driver-contracts.store') }}" method="POST"
                class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Agent <span
                                class="text-red-500">*</span></label>
                        <select name="driver_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">-- Sélectionnez --</option>
                            @foreach ($availableDrivers ?? [] as $driver)
                                <option value="{{ $driver->id }}">{{ $driver->user->name ?? 'N/A' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Véhicule <span
                                class="text-red-500">*</span></label>
                        <select name="vehicle_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">-- Sélectionnez --</option>
                            @foreach ($availableVehicles ?? [] as $vehicle)
                                <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de début <span
                                class="text-red-500">*</span></label>
                        <input type="date" name="start_date" required value="{{ now()->toDateString() }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Durée <span
                                class="text-red-500">*</span></label>
                        <select name="contract_months" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="24">24 mois</option>
                            <option value="30">30 mois</option>
                            <option value="36">36 mois</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('createModal')"
                        class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Annuler
                    </button>
                    <button type="submit"
                        class="flex-1 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                        Créer le contrat
                    </button>
                </div>
            </form>

            {{-- Formulaire proprio --}}
            <form id="createOwnerForm" action="{{ route('admin.vehicle-contracts.store') }}" method="POST"
                class="p-6 space-y-4 hidden">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Véhicule <span
                                class="text-red-500">*</span></label>
                        <select name="vehicle_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">-- Sélectionnez --</option>
                            @foreach ($availableVehicles ?? [] as $vehicle)
                                <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_number }} —
                                    {{ $vehicle->owner->name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Montant total (FCFA) <span
                                class="text-red-500">*</span></label>
                        <input type="number" name="total_amount" required placeholder="2000000"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mensualité (FCFA) <span
                                class="text-red-500">*</span></label>
                        <input type="number" name="monthly_payment" required placeholder="83333"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de début <span
                                class="text-red-500">*</span></label>
                        <input type="date" name="start_date" required value="{{ now()->toDateString() }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('createModal')"
                        class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Annuler
                    </button>
                    <button type="submit"
                        class="flex-1 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                        Créer le contrat
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            // ===== MODALS =====
            function openModal(id) {
                document.getElementById(id).classList.remove('hidden');
                document.getElementById(id).classList.add('flex');
            }

            function closeModal(id) {
                document.getElementById(id).classList.add('hidden');
                document.getElementById(id).classList.remove('flex');
            }

            // ===== DÉTAIL =====
            function openDetailModal(type, id, contract, stats) {
                const body = document.getElementById('detailBody');
                const title = document.getElementById('detailTitle');

                if (type === 'agent') {
                    title.textContent = 'Contrat agent — ' + (contract.driver?.user?.name ?? 'N/A');
                    const avail = (contract.accrued_leave_days ?? 0) - (contract.used_leave_days ?? 0);
                    body.innerHTML = detailRow('Agent', contract.driver?.user?.name ?? 'N/A') +
                        detailRow('Véhicule', contract.vehicle?.vehicle_number ?? 'N/A') +
                        detailRow('Début', contract.start_date) +
                        detailRow('Durée', (contract.contract_months ?? '?') + ' mois') +
                        detailRow('Mois écoulés', (contract.months_elapsed ?? '?') + 'm') +
                        detailRow('Congés acquis', (contract.accrued_leave_days ?? 0) + 'j') +
                        detailRow('Congés utilisés', (contract.used_leave_days ?? 0) + 'j') +
                        detailRow('Congés disponibles', avail < 0 ? '<span class="text-red-500 font-medium">+' + Math.abs(
                            avail) + 'j surplus</span>' : '<span class="text-green-600">' + avail + 'j</span>') +
                        detailRow('Total payé', formatAmount(contract.total_paid ?? 0)) +
                        detailRow('Statut', contract.status === 'active' ?
                            '<span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Actif</span>' :
                            '<span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium">Terminé</span>'
                        );
                } else {
                    title.textContent = 'Contrat propriétaire — ' + (contract.owner?.name ?? 'N/A');
                    const rem = (stats?.remaining ?? 0);
                    const surplus = (stats?.surplus ?? 0);
                    body.innerHTML = detailRow('Propriétaire', contract.owner?.name ?? 'N/A') +
                        detailRow('Véhicule', contract.vehicle?.vehicle_number ?? 'N/A') +
                        detailRow('Début', contract.start_date) +
                        detailRow('Montant total', formatAmount(contract.total_amount ?? 0)) +
                        detailRow('Mensualité', formatAmount(contract.monthly_payment ?? 0)) +
                        detailRow('Total payé', '<span class="text-green-600 font-medium">' + formatAmount(stats?.total_paid ??
                            0) + '</span>') +
                        detailRow('Restant', rem <= 0 ? '<span class="text-green-600 font-medium">Soldé</span>' :
                            '<span class="text-red-500 font-medium">' + formatAmount(rem) + '</span>') +
                        (surplus > 0 ? detailRow('Surplus', '<span class="text-amber-500 font-medium">+' + formatAmount(
                            surplus) + '</span>') : '') +
                        detailRow('Progression', (stats?.progress_percent ?? 0) + '%') +
                        detailRow('Paiements', (stats?.payments_count ?? 0) + ' paiement(s)');
                }
                openModal('detailModal');
            }

            function detailRow(label, value) {
                return `<div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
            <span class="text-sm text-gray-500">${label}</span>
            <span class="text-sm font-medium text-gray-900">${value}</span>
        </div>`;
            }

            function formatAmount(n) {
                return Number(n).toLocaleString('fr-FR') + ' FCFA';
            }

            // ===== MODIFIER PROPRIO =====
            function openEditOwnerModal(contract) {
                document.getElementById('edit_owner_total').value = contract.total_amount ?? '';
                document.getElementById('edit_owner_monthly').value = contract.monthly_payment ?? '';
                document.getElementById('edit_owner_start').value = contract.start_date ?? '';
                document.getElementById('edit_owner_end').value = contract.end_date ?? '';
                document.getElementById('edit_owner_status').value = contract.status ?? 'active';
                document.getElementById('edit_owner_notes').value = contract.notes ?? '';
                document.getElementById('editOwnerForm').action = `/admin/vehicle-contracts/${contract.id}`;
                openModal('editOwnerModal');
            }

            // ===== SUPPRIMER =====
            function openDeleteModal(resource, id, name) {
                document.getElementById('deleteContractName').textContent = name;
                document.getElementById('deleteForm').action = `/admin/${resource}/${id}`;
                openModal('deleteModal');
            }

            // ===== CRÉER =====
            function openCreateModal() {
                setCreateType('agent');
                openModal('createModal');
            }

            function setCreateType(type) {
                const isOwner = type === 'owner';
                document.getElementById('createAgentForm').classList.toggle('hidden', isOwner);
                document.getElementById('createOwnerForm').classList.toggle('hidden', !isOwner);

                const agentBtn = document.getElementById('create-type-agent');
                const ownerBtn = document.getElementById('create-type-owner');

                if (isOwner) {
                    agentBtn.className = 'flex-1 py-2 text-sm rounded-lg bg-gray-100 text-gray-600 border border-gray-200';
                    ownerBtn.className =
                        'flex-1 py-2 text-sm rounded-lg bg-purple-100 text-purple-700 font-medium border border-purple-200';
                } else {
                    agentBtn.className =
                        'flex-1 py-2 text-sm rounded-lg bg-purple-100 text-purple-700 font-medium border border-purple-200';
                    ownerBtn.className = 'flex-1 py-2 text-sm rounded-lg bg-gray-100 text-gray-600 border border-gray-200';
                }
            }
        </script>
    @endpush

@endsection
