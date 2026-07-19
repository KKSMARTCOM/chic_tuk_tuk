@extends('layouts.app')

@section('content')
    {{-- Header --}}
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">Gestion des Véhicules</h1>
                <p class="text-xs md:text-base text-gray-600">Gérez les véhicules des propriétaires</p>
            </div>

            <button onclick="openAddModal()"
                class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-plus mr-2"></i> Ajouter un véhicule
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach ([['label' => 'Total', 'value' => $vehicles->count(), 'color' => 'blue', 'icon' => 'fa-truck-pickup'], ['label' => 'Actifs', 'value' => $vehicles->where('is_active', true)->count(), 'color' => 'green', 'icon' => 'fa-check-circle'], ['label' => 'En pause', 'value' => $vehicles->filter(fn($v) => $v->activePause)->count(), 'color' => 'yellow', 'icon' => 'fa-pause-circle'], ['label' => 'Sans contrat', 'value' => $vehicles->filter(fn($v) => !$v->activeVehicleContract)->count(), 'color' => 'red', 'icon' => 'fa-file']] as $stat)
            <div class="bg-white rounded-lg shadow-md p-4 flex items-center gap-3">
                <div
                    class="w-12 h-12 rounded-full bg-{{ $stat['color'] }}-100 text-{{ $stat['color'] }}-600 flex items-center justify-center flex-shrink-0">
                    <i class="fas {{ $stat['icon'] }}"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ $stat['label'] }}</p>
                    <p class="text-xl font-bold text-gray-900">{{ $stat['value'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Liste des Véhicules</h3>
        </div>
        <div class="overflow-x-auto p-4">
            <table class="min-w-full divide-y divide-gray-200 display" id="datatable1">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">N° Véhicule</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Propriétaire</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Agent actuel</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Contrat véhicule</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Statut</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($vehicles as $vehicle)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="font-semibold text-gray-900">{{ $vehicle->vehicle_number }}</div>
                                @if ($vehicle->color)
                                    <div class="text-xs text-gray-400">{{ $vehicle->color }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if ($vehicle->owner)
                                    <div class="text-sm font-medium text-gray-900">{{ $vehicle->owner->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $vehicle->owner->phone }}</div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Aucun propriétaire</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @php $activeDriver = $vehicle->activeDriverContract?->driver?->user; @endphp
                                @if ($activeDriver)
                                    <div class="text-sm font-medium text-gray-900">{{ $activeDriver->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $activeDriver->phone }}</div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Aucun agent</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if ($vehicle->activeVehicleContract)
                                    @php
                                        $c = $vehicle->activeVehicleContract;
                                        $progress = $c->progress_percentage;
                                    @endphp
                                    <div class="text-xs text-gray-700 mb-1">
                                        {{ number_format($c->total_paid, 0, ',', ' ') }}
                                        / {{ number_format($c->total_amount, 0, ',', ' ') }} FCFA
                                    </div>
                                    <div class="w-24 bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $progress }}%">
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Aucun contrat</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $vehicle->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $vehicle->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                    @if ($vehicle->activePause)
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                            En pause
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.vehicles.show', $vehicle) }}"
                                        class="text-blue-600 hover:text-blue-800" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <button onclick="openEditModal({{ $vehicle->toJson() }})"
                                        class="text-green-600 hover:text-green-800" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    @if ($vehicle->activePause)
                                        <form action="{{ route('admin.vehicles.end-pause', $vehicle->activePause) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}">
                                            <button type="submit" class="text-emerald-600 hover:text-emerald-800"
                                                title="Terminer la pause">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button
                                            onclick="openPauseModal('{{ $vehicle->id }}', '{{ $vehicle->vehicle_number }}')"
                                            class="text-yellow-600 hover:text-yellow-800" title="Mettre en pause">
                                            <i class="fas fa-pause"></i>
                                        </button>
                                    @endif

                                    <form action="{{ route('admin.vehicles.destroy', $vehicle) }}" method="POST"
                                        class="inline"
                                        onsubmit="return confirm('Supprimer le véhicule {{ $vehicle->vehicle_number }} ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:text-red-800" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                <i class="fas fa-truck-pickup text-4xl mb-3"></i>
                                <p>Aucun véhicule enregistré.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===== MODAL AJOUT/MODIFICATION ===== --}}
    @include('inc.modals.vehicles.add')

    {{-- ===== MODAL PAUSE ===== --}}
    @include('inc.modals.vehicles.pause')

    @push('scripts')
        <script>
            // ── DataTable ────────────────────────────────────────────
            $("#datatable1").DataTable({
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

            async function generateOwnerPassword() {
                const res = await fetch('{{ route('admin.users.generate-password') }}');
                const data = await res.json();
                document.getElementById('new_owner_password').value = data.password;
            }

            document.addEventListener('DOMContentLoaded', () => {
                generateOwnerPassword();
            });

            // ── Mode propriétaire ────────────────────────────────────
            document.querySelectorAll('input[name="_owner_mode"]').forEach(r => {
                r.addEventListener('change', () => switchOwnerMode(r.value));
            });
            document.querySelectorAll('.owner-mode-card').forEach(card => {
                card.addEventListener('click', () => {
                    const r = card.querySelector('input');
                    r.checked = true;
                    switchOwnerMode(r.value);
                });
            });

            function switchOwnerMode(mode) {
                const isExisting = mode === 'existing';

                document.getElementById('owner-mode-existing-label').className =
                    `owner-mode-card cursor-pointer rounded-xl border-2 p-3 flex items-center gap-2 transition ${isExisting ? 'border-[#286b41] bg-[#286b41]/10' : 'border-gray-200 bg-white'}`;
                document.getElementById('owner-mode-new-label').className =
                    `owner-mode-card cursor-pointer rounded-xl border-2 p-3 flex items-center gap-2 transition ${!isExisting ? 'border-purple-600 bg-purple-50' : 'border-gray-200 bg-white'}`;

                document.getElementById('section-owner-existing').classList.toggle('hidden', !isExisting);
                document.getElementById('section-owner-new').classList.toggle('hidden', isExisting);
            }

            // ── Modal Ajout ──────────────────────────────────────────
            function openAddModal() {
                const form = document.getElementById('vehicle-form');
                document.getElementById('modal-title').textContent = 'Ajouter un véhicule';
                form.action = "{{ route('admin.vehicles.store') }}";
                document.getElementById('method-field').innerHTML = '';
                form.reset();

                // reset mode proprio
                document.querySelector('input[name="_owner_mode"][value="existing"]').checked = true;
                switchOwnerMode('existing');
                document.getElementById('existing-contract-info').classList.add('hidden');
                document.getElementById('contract-section').classList.remove('hidden');

                document.getElementById('modal-form').classList.remove('hidden');
                document.getElementById('modal-form').classList.add('flex');
            }

            // ── Modal Modification ───────────────────────────────────
            function openEditModal(vehicle) {
                const form = document.getElementById('vehicle-form');
                document.getElementById('modal-title').textContent = 'Modifier le véhicule';
                form.action = `/admin/vehicles/${vehicle.id}`;

                // Ajouter PUT
                document.getElementById('method-field').innerHTML =
                    '<input type="hidden" name="_method" value="PUT">';

                // Remplir les champs
                document.getElementById('f_vehicle_number').value = vehicle.vehicle_number;
                document.getElementById('f_vehicle_type').value = vehicle.vehicle_type;
                document.getElementById('f_notes').value = vehicle.notes ?? '';
                document.getElementById('f_owner_id').value = vehicle.owner_id ?? '';

                // Reset mode proprio sur "existing"
                document.querySelector('input[name="_owner_mode"][value="existing"]').checked = true;
                switchOwnerMode('existing');

                // Contrat actif existant → afficher l'info
                if (vehicle.has_active_contract) {
                    document.getElementById('existing-contract-info').classList.remove('hidden');
                    document.getElementById('contract-link').href = `/admin/vehicle-contracts?vehicle=${vehicle.id}`;
                    // Masquer les champs contrat (déjà actif)
                    ['f_contract_total', 'f_contract_monthly', 'f_contract_start', 'f_contract_end', 'f_contract_notes']
                    .forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.disabled = true;
                    });
                } else {
                    document.getElementById('existing-contract-info').classList.add('hidden');
                    ['f_contract_total', 'f_contract_monthly', 'f_contract_start', 'f_contract_end', 'f_contract_notes']
                    .forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.disabled = false;
                    });
                }

                document.getElementById('modal-form').classList.remove('hidden');
                document.getElementById('modal-form').classList.add('flex');
            }

            function closeFormModal() {
                document.getElementById('modal-form').classList.add('hidden');
                document.getElementById('modal-form').classList.remove('flex');
            }

            // ── Modal Pause ──────────────────────────────────────────
            function openPauseModal(id, number) {
                document.getElementById('pause_vehicle_id').value = id;
                document.getElementById('pause-vehicle-label').textContent = number;
                document.getElementById('pause-form').action = `/admin/vehicles/${id}/add-pause`;
                document.getElementById('modal-pause').classList.remove('hidden');
                document.getElementById('modal-pause').classList.add('flex');
            }

            function closePauseModal() {
                document.getElementById('modal-pause').classList.add('hidden');
                document.getElementById('modal-pause').classList.remove('flex');
            }
        </script>
    @endpush
@endsection
