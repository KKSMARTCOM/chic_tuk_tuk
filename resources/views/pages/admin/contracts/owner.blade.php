@extends('layouts.app')

@section('content')

    {{-- Header --}}
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">Gestion des Contrats</h1>
                <p class="text-xs md:text-base text-gray-600">Contrats propriétaires-véhicules</p>
            </div>
            {{-- <button onclick="openCreateModal()"
                class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition flex items-center gap-2">
                <i class="fas fa-plus"></i> Nouveau contrat
            </button> --}}
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
                                {{-- <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Mensualité
                                </th> --}}
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
                                    {{-- <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                        {{ number_format($stats['monthly_payment'], 0, ',', ' ') }} FCFA
                                    </td> --}}
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
                                            <a href="{{ route('admin.vehicle-contracts.show', $contract) }}"
                                                class="text-blue-600 hover:text-blue-800" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button onclick="openEditOwnerModal({{ $contract->toJson() }})"
                                                class="text-green-600 hover:text-green-800" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button
                                                onclick="openDeleteModal('vehicle-contracts', '{{ $contract->id }}', '{{ $contract->owner->name ?? '' }} - {{ $contract->vehicle->vehicle_number }}')"
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

    {{-- ===== MODAL MODIFIER CONTRAT PROPRIO ===== --}}
    <div id="editOwnerModal" class="fixed inset-0 px-4 bg-black bg-opacity-50 hidden items-center justify-center z-30">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl mx-4">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 id="edit_owner_name" class="text-lg font-bold text-gray-800">Modifier le contrat du propriétaire</h3>
                <button onclick="closeModal('editOwnerModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            {{-- Formulaire proprio --}}
            <form id="editOwnerForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Véhicule <span class="text-gray-500">(laisser vide pour conserver le véhicule actuel)</span>
                        </label>
                        <select name="vehicle_id" id="vehicle_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">-- Conserver le véhicule actuel --</option>
                            @foreach ($availableVehicles ?? [] as $vehicle)
                                <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_number }} —
                                    {{ $vehicle->owner->name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Durée du contrat <span class="text-red-500">*</span>
                        </label>
                        <select name="contract_months" id="edit_owner_months"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">-- Sélectionnez --</option>
                            @foreach ([24, 30, 36] as $m)
                                <option value="{{ $m }}">{{ $m }} mois</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Revenus totaux (FCFA) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="contract_total_amount" id="contract_total_amount"
                            value="{{ old('contract_total_amount') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Date de début <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="contract_start_date" id="contract_start_date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Statut
                        </label>
                        <select name="status" id="edit_owner_status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="active">Actif</option>
                            <option value="completed">Soldé</option>
                            <option value="cancelled">Annulé</option>
                        </select>
                    </div>
                    <div class="col-span-2 grid grid-cols-1 md:grid-cols-3 gap-2">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">
                                <i class="fas fa-wifi mr-1"></i> Internet illimité (FCFA)
                            </label>
                            <input type="number" name="unlimited_internet" id="unlimited_internet"
                                value="{{ old('unlimited_internet', \App\Consts\VehicleContractConsts::DEFAULT_UNLIMITED_INTERNET) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">
                                <i class="fab fa-spotify mr-1"></i> Spotify Premium (FCFA)
                            </label>
                            <input type="number" name="spotify_premium" id="spotify_premium"
                                value="{{ old('spotify_premium', \App\Consts\VehicleContractConsts::DEFAULT_SPOTIFY_PREMIUM) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">
                                <i class="fas fa-user-tie mr-1"></i> Rémunération manager (FCFA)
                            </label>
                            <input type="number" name="manager_remuneration" id="manager_remuneration"
                                value="{{ old('manager_remuneration', \App\Consts\VehicleContractConsts::DEFAULT_MANAGER_REMUNERATION) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" id="vehicles_notes" rows="2"
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
                        Enrégistrer
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
            $("#datatable-owner").DataTable({
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

            // ===== MODIFIER PROPRIO =====
            function openEditOwnerModal(contract) {
                document.getElementById('edit_owner_name').textContent =
                    `Modifier le contrat du propriétaire ${contract.owner.name}-${contract.vehicle.vehicle_number}.`;

                // Statut
                const statusSelect = document.getElementById('edit_owner_status');
                if (statusSelect) statusSelect.value = contract.status ?? 'active';

                // Durée du contrat
                const monthsSelect = document.getElementById('edit_owner_months');
                if (monthsSelect) {
                    monthsSelect.value = contract.contract_months ?? 24;

                    // Déclencher le préremplissage automatique du montant si la durée change
                    monthsSelect.addEventListener('change', function() {
                        const AMOUNTS = @json(\App\Consts\VehicleContractConsts::TOTAL_AMOUNTS);
                        const m = parseInt(this.value);
                        if (AMOUNTS[m]) {
                            document.getElementById('contract_total_amount').value = AMOUNTS[m];
                        }
                    });
                }

                document.getElementById('contract_start_date').value = contract.start_date ?
                    new Date(contract.start_date).toISOString().split('T')[0] :
                    '';

                document.getElementById('vehicle_id').value = '';
                document.getElementById('contract_total_amount').value = contract.total_amount ?? '';
                document.getElementById('contract_start_date').value = contract.start_date ?
                    new Date(contract.start_date).toISOString().split('T')[0] : '';
                document.getElementById('unlimited_internet').value = contract.unlimited_internet ?? '';
                document.getElementById('spotify_premium').value = contract.spotify_premium ?? '';
                document.getElementById('manager_remuneration').value = contract.manager_remuneration ?? '';
                document.getElementById('vehicles_notes').value = contract.notes ?? '';
                document.getElementById('editOwnerForm').action = `/admin/vehicle-contracts/${contract.id}`;

                openModal('editOwnerModal');
            }

            // ===== SUPPRIMER =====
            function openDeleteModal(resource, id, name) {
                document.getElementById('deleteContractName').textContent = name;
                document.getElementById('deleteForm').action = `/admin/${resource}/${id}`;
                openModal('deleteModal');
            }

            document.getElementById('contract_months').addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                const total = selected.dataset.total;
                const monthly = selected.dataset.monthly;

                if (total) {
                    document.getElementById('contract_total_amount').value = total;
                } else {
                    document.getElementById('contract_total_amount').value = '';
                }
            });
        </script>
    @endpush

@endsection
