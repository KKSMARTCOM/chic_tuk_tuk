@extends('layouts.app')

@section('content')

    @php
        $activeContract = $vehicle->activeVehicleContract;
        $activeDriverContract = $vehicle->activeDriverContract;
        $activeDriver = $activeDriverContract?->driver?->user;
        $activePause = $vehicle->activePause;
    @endphp

    {{-- Header --}}
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800 flex items-center gap-3">
                    <span>{{ $vehicle->vehicle_number }}</span>
                    <span
                        class="px-2 py-0.5 text-sm rounded-full font-semibold {{ $vehicle->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $vehicle->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                    @if ($activePause)
                        <span class="px-2 py-0.5 text-sm rounded-full font-semibold bg-yellow-100 text-yellow-700">
                            En pause
                        </span>
                    @endif
                </h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ vehiculeType($vehicle->vehicle_type) }}
                    {{ $vehicle->color ? '· ' . $vehicle->color : '' }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <form action="{{ route('admin.vehicles.toggle-status', $vehicle) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition
                    {{ $vehicle->is_active ? 'bg-gray-100 text-gray-700 hover:bg-gray-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                        <i class="fas {{ $vehicle->is_active ? 'fa-toggle-off' : 'fa-toggle-on' }} mr-1"></i>
                        {{ $vehicle->is_active ? 'Désactiver' : 'Activer' }}
                    </button>
                </form>

                @if ($activePause)
                    <form action="{{ route('admin.vehicles.end-pause', $activePause) }}" method="POST">
                        @csrf
                        <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}">
                        <button type="submit"
                            class="px-4 py-2 bg-emerald-100 text-emerald-700 hover:bg-emerald-200 rounded-lg text-sm font-semibold transition">
                            <i class="fas fa-play mr-1"></i> Terminer la pause
                        </button>
                    </form>
                @else
                    <button onclick="openPauseModal('{{ $vehicle->id }}', '{{ $vehicle->vehicle_number }}')"
                        class="px-4 py-2 bg-yellow-100 text-yellow-700 hover:bg-yellow-200 rounded-lg text-sm font-semibold transition">
                        <i class="fas fa-pause mr-1"></i> Mettre en pause
                    </button>
                @endif

                <a href="{{ route('admin.vehicles.index') }}"
                    class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-sm font-semibold transition">
                    <i class="fas fa-arrow-left mr-1"></i> Retour
                </a>
            </div>
        </div>
    </div>

    {{-- Pause active --}}
    @if ($activePause)
        <div class="bg-yellow-50 border border-yellow-300 rounded-lg px-5 py-4 mb-6 flex items-start gap-3">
            <i class="fas fa-pause-circle text-yellow-500 text-xl mt-0.5 flex-shrink-0"></i>
            <div>
                <p class="font-semibold text-yellow-800">Véhicule en pause depuis le
                    {{ $activePause->start_date->format('d/m/Y') }}</p>
                <p class="text-sm text-yellow-700">
                    Motif : {{ $activePause->reason_label ?? $activePause->reason_type }}
                    @if ($activePause->reason_notes)
                        — {{ $activePause->reason_notes }}
                    @endif
                </p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Colonne gauche ── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Informations générales --}}
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-base font-bold text-gray-800">Informations générales</h3>
                    <button onclick="openEditModal({{ $vehicle->toJson() }})"
                        class="text-sm text-purple-600 hover:text-purple-800 font-semibold">
                        <i class="fas fa-edit mr-1"></i> Modifier
                    </button>
                </div>
                <div class="px-6 py-5 grid grid-cols-2 md:grid-cols-2 gap-5">
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">N° Immatriculation</p>
                        <p class="font-semibold text-gray-800">{{ $vehicle->vehicle_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Type</p>
                        <p class="font-semibold text-gray-800">{{ vehiculeType($vehicle->vehicle_type) }}</p>
                    </div>
                    <div class="col-span-2 md:col-span-3">
                        <p class="text-xs text-gray-500 mb-0.5">Notes</p>
                        <p class="text-sm text-gray-700">{{ $vehicle->notes ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Contrat véhicule actif --}}
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-base font-bold text-gray-800">Contrat propriétaire-véhicule</h3>
                    @if (!$activeContract)
                        <button onclick="openContractModal()"
                            class="text-sm text-purple-600 hover:text-purple-800 font-semibold">
                            <i class="fas fa-plus mr-1"></i> Créer un contrat
                        </button>
                    @endif
                </div>
                <div class="px-6 py-5">
                    @if ($activeContract)
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Montant total</p>
                                <p class="font-bold text-gray-800">
                                    {{ number_format($activeContract->total_amount, 0, ',', ' ') }} <span
                                        class="text-xs font-normal">FCFA</span></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Mensualité</p>
                                <p class="font-bold text-gray-800">
                                    {{ number_format($activeContract->monthly_payment, 0, ',', ' ') }} <span
                                        class="text-xs font-normal">FCFA</span></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Total payé</p>
                                <p class="font-bold text-emerald-700">
                                    {{ number_format($activeContract->total_paid, 0, ',', ' ') }} <span
                                        class="text-xs font-normal">FCFA</span></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Restant / Surplus</p>
                                @if ($activeContract->surplus > 0)
                                    <p class="font-bold text-orange-600">
                                        +{{ number_format($activeContract->surplus, 0, ',', ' ') }} <span
                                            class="text-xs font-normal">FCFA surplus</span></p>
                                @else
                                    <p class="font-bold text-red-600">
                                        {{ number_format($activeContract->remaining_amount, 0, ',', ' ') }} <span
                                            class="text-xs font-normal">FCFA</span></p>
                                @endif
                            </div>
                        </div>

                        {{-- Barre de progression --}}
                        <div class="mb-1 flex justify-between text-xs text-gray-500">
                            <span>Progression</span>
                            <span>{{ $activeContract->progress_percentage }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2.5 mb-4">
                            <div class="h-2.5 rounded-full {{ $activeContract->progress_percentage >= 100 ? 'bg-orange-500' : 'bg-emerald-500' }}"
                                style="width: {{ min(100, $activeContract->progress_percentage) }}%"></div>
                        </div>

                        {{-- Dates --}}
                        <div class="flex gap-6 text-sm text-gray-600">
                            <span><i class="fas fa-calendar-alt text-gray-400 mr-1"></i> Début :
                                {{ $activeContract->start_date->format('d/m/Y') }}</span>
                            @if ($activeContract->end_date)
                                <span><i class="fas fa-calendar-check text-gray-400 mr-1"></i> Fin :
                                    {{ $activeContract->end_date->format('d/m/Y') }}</span>
                            @endif
                        </div>

                        {{-- Paiements récents --}}
                        @if ($activeContract->payments->count() > 0)
                            <div class="mt-5">
                                <p class="text-xs font-bold text-gray-600 uppercase tracking-wide mb-3">Derniers paiements
                                </p>
                                <div class="space-y-2">
                                    @foreach ($activeContract->payments->sortByDesc('payment_date')->take(5) as $payment)
                                        <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-2.5">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800">
                                                    {{ number_format($payment->amount, 0, ',', ' ') }} FCFA</p>
                                                <p class="text-xs text-gray-400">
                                                    {{ $payment->payment_date?->format('d/m/Y') }} ·
                                                    {{ $payment->payment_method ?? '—' }}</p>
                                            </div>
                                            @if ($payment->reference_number)
                                                <span class="text-xs text-gray-400">Réf.
                                                    {{ $payment->reference_number }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                @if ($activeContract->payments->count() > 5)
                                    <a href="{{ route('admin.vehicle-contracts.show', $activeContract) }}"
                                        class="block text-center text-xs text-purple-600 hover:underline mt-2">
                                        Voir tous les paiements
                                    </a>
                                @endif
                            </div>
                        @endif
                    @else
                        <div class="py-6 text-center text-gray-400">
                            <i class="fas fa-file-contract text-3xl mb-2"></i>
                            <p class="text-sm">Aucun contrat actif pour ce véhicule.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Historique des contrats --}}
            @php $oldContracts = $vehicle->vehicleContracts->where('status', '!=', 'active'); @endphp
            @if ($oldContracts->count() > 0)
                <div class="bg-white rounded-lg shadow-md">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-base font-bold text-gray-800">Historique des contrats véhicule</h3>
                    </div>
                    <div class="px-6 py-4 space-y-3">
                        @foreach ($oldContracts->sortByDesc('start_date') as $old)
                            <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-700">
                                        {{ number_format($old->total_amount, 0, ',', ' ') }} FCFA
                                        <span class="text-xs font-normal text-gray-400 ml-2">
                                            {{ number_format($old->total_paid, 0, ',', ' ') }} payés
                                        </span>
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $old->start_date->format('d/m/Y') }} →
                                        {{ $old->end_date?->format('d/m/Y') ?? '…' }}
                                    </p>
                                </div>
                                <span
                                    class="px-2 py-0.5 text-xs rounded-full font-semibold
                        {{ $old->status === 'completed' ? 'bg-green-100 text-green-700' : ($old->status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') }}">
                                    {{ match ($old->status) {'completed' => 'Terminé','cancelled' => 'Annulé',default => ucfirst($old->status)} }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Historique des agents --}}
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-base font-bold text-gray-800">Historique des agents</h3>
                </div>
                <div class="px-6 py-4">
                    @if ($vehicle->driverContracts->count() > 0)
                        <div class="space-y-3">
                            @foreach ($vehicle->driverContracts->sortByDesc('start_date') as $dc)
                                <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($dc->driver?->user?->name ?? 'Agent') }}&size=36"
                                            class="w-9 h-9 rounded-full">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800">
                                                {{ $dc->driver?->user?->name ?? '—' }}</p>
                                            <p class="text-xs text-gray-400">
                                                {{ $dc->start_date->format('d/m/Y') }} →
                                                {{ $dc->end_date?->format('d/m/Y') ?? 'en cours' }}
                                                · {{ $dc->contract_months }} mois
                                            </p>
                                        </div>
                                    </div>
                                    <span
                                        class="px-2 py-0.5 text-xs rounded-full font-semibold
                            {{ $dc->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $dc->status === 'active' ? 'Actif' : $dc->end_reason ?? 'Terminé' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-400 py-4 text-center">Aucun agent associé.</p>
                    @endif
                </div>
            </div>

        </div>

        {{-- ── Colonne droite ── --}}
        <div class="space-y-6">

            {{-- Propriétaire --}}
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-base font-bold text-gray-800">Propriétaire</h3>
                </div>
                <div class="px-6 py-5">
                    @if ($vehicle->owner)
                        <div class="flex items-center gap-3 mb-4">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($vehicle->owner->name) }}&size=48"
                                class="w-12 h-12 rounded-full">
                            <div>
                                <p class="font-bold text-gray-800">{{ $vehicle->owner->name }}</p>
                                <p class="text-sm text-gray-500">{{ $vehicle->owner->phone }}</p>
                                @if ($vehicle->owner->email)
                                    <p class="text-xs text-gray-400">{{ $vehicle->owner->email }}</p>
                                @endif
                            </div>
                        </div>
                        {{-- <a href="{{ route('owner.dashboard') }}?owner={{ $vehicle->owner->id }}"
                            class="block text-center px-4 py-2 bg-[#286b41]/10 text-[#286b41] hover:bg-[#286b41]/20 rounded-lg text-sm font-semibold transition">
                            <i class="fas fa-external-link-alt mr-1"></i> Espace propriétaire
                        </a> --}}
                    @else
                        <p class="text-sm text-gray-400 text-center py-3">Aucun propriétaire associé.</p>
                    @endif
                </div>
            </div>

            {{-- Agent actuel --}}
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-base font-bold text-gray-800">Agent actuel</h3>
                </div>
                <div class="px-6 py-5">
                    @if ($activeDriver)
                        <div class="flex items-center gap-3 mb-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($activeDriver->name) }}&size=48"
                                class="w-12 h-12 rounded-full">
                            <div>
                                <p class="font-bold text-gray-800">{{ $activeDriver->name }}</p>
                                <p class="text-sm text-gray-500">{{ $activeDriver->phone }}</p>
                            </div>
                        </div>
                        @if ($activeDriverContract)
                            <div class="bg-gray-50 rounded-lg px-4 py-3 space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Depuis</span>
                                    <span
                                        class="font-semibold text-gray-800">{{ $activeDriverContract->start_date->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Durée contrat</span>
                                    <span class="font-semibold text-gray-800">{{ $activeDriverContract->contract_months }}
                                        mois</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Pauses acquises</span>
                                    <span
                                        class="font-semibold text-gray-800">{{ $activeDriverContract->accrued_leave_days }}j</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Pauses utilisées</span>
                                    @php
                                        $usedLeave = $activeDriverContract->used_leave_days;
                                        $surplus = $usedLeave - $activeDriverContract->accrued_leave_days;
                                    @endphp
                                    <span class="font-semibold {{ $surplus > 0 ? 'text-orange-600' : 'text-gray-800' }}">
                                        {{ $usedLeave }}j {{ $surplus > 0 ? '(+' . $surplus . ' surplus)' : '' }}
                                    </span>
                                </div>
                            </div>
                        @endif
                        <a href="{{ route('admin.drivers.show', $activeDriver) }}"
                            class="block mt-3 text-center px-4 py-2 bg-purple-50 text-purple-700 hover:bg-purple-100 rounded-lg text-sm font-semibold transition">
                            <i class="fas fa-external-link-alt mr-1"></i> Fiche de l'agent
                        </a>
                    @else
                        <p class="text-sm text-gray-400 text-center py-3">Aucun agent assigné.</p>
                    @endif
                </div>
            </div>

            {{-- Pauses du véhicule --}}
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-base font-bold text-gray-800">Pauses</h3>
                    <span class="text-xs text-gray-400">{{ $vehicle->pauses->count() }} au total</span>
                </div>
                <div class="px-6 py-4">
                    @if ($vehicle->pauses->count() > 0)
                        <div class="space-y-2">
                            @foreach ($vehicle->pauses->sortByDesc('start_date') as $pause)
                                <div class="bg-gray-50 rounded-lg px-4 py-3">
                                    <div class="flex items-center justify-between mb-1">
                                        <span
                                            class="text-xs font-semibold {{ is_null($pause->end_date) ? 'text-yellow-600' : 'text-gray-600' }}">
                                            {{ is_null($pause->end_date) ? '⏸ En cours' : '✓ Terminée' }}
                                        </span>
                                        <span
                                            class="text-xs text-gray-400">{{ $pause->is_auto ? 'Auto' : 'Manuel' }}</span>
                                    </div>
                                    <p class="text-xs text-gray-700">
                                        {{ $pause->start_date->format('d/m/Y') }}
                                        → {{ $pause->end_date ? $pause->end_date->format('d/m/Y') : 'en cours' }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $pause->reason_label ?? $pause->reason_type }}
                                        @if ($pause->reason_notes)
                                            — {{ Str::limit($pause->reason_notes, 60) }}
                                        @endif
                                    </p>
                                    @if (is_null($pause->end_date))
                                        <form action="{{ route('admin.vehicles.end-pause', $pause) }}" method="POST"
                                            class="mt-2">
                                            @csrf
                                            <input type="hidden" name="end_date" value="{{ date('Y-m-d') }}">
                                            <button type="submit" class="text-xs text-emerald-600 hover:underline">
                                                <i class="fas fa-play mr-0.5"></i> Terminer aujourd'hui
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-400 text-center py-4">Aucune pause enregistrée.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- ===== MODAL EDIT ===== --}}
    @include('inc.modals.vehicles.add')

    {{-- ===== MODAL PAUSE ===== --}}
    @include('inc.modals.vehicles.pause')

    {{-- ===== MODAL CONTRAT ===== --}}
    <div id="modal-contract" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-30">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Créer un contrat · {{ $vehicle->vehicle_number }}</h3>
                <button onclick="closeContractModal()" class="text-gray-400 hover:text-gray-600"><i
                        class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('admin.vehicle-contracts.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Montant total (FCFA) <span
                                class="text-red-500">*</span></label>
                        <input type="number" name="total_amount" min="1" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="ex: 2500000">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mensualité (FCFA) <span
                                class="text-red-500">*</span></label>
                        <input type="number" name="monthly_payment" min="0" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="ex: 104167">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de début <span
                                class="text-red-500">*</span></label>
                        <input type="date" name="start_date" required value="{{ date('Y-m-d') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin</label>
                        <input type="date" name="end_date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="Conditions particulières..."></textarea>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeContractModal()"
                        class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Annuler</button>
                    <button type="submit"
                        class="flex-1 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                        <i class="fas fa-save mr-1"></i> Créer le contrat
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function openPauseModal() {
                document.getElementById('modal-pause').classList.replace('hidden', 'flex');
            }

            function closePauseModal() {
                document.getElementById('modal-pause').classList.replace('flex', 'hidden');
            }

            function openContractModal() {
                document.getElementById('modal-contract').classList.replace('hidden', 'flex');
            }

            function closeContractModal() {
                document.getElementById('modal-contract').classList.replace('flex', 'hidden');
            }

            // ── Modal Edit ──────────────────────────────────────────
            function openEditModal(vehicle) {
                const form = document.getElementById('vehicle-form');
                document.getElementById('modal-title').textContent = 'Modifier le véhicule';
                form.action = `/admin/vehicles/${vehicle.id}`;
                document.getElementById('method-field').innerHTML =
                    '<input type="hidden" name="_method" value="PUT">';

                // Champs véhicule
                document.getElementById('f_vehicle_number').value = vehicle.vehicle_number ?? '';
                document.getElementById('f_vehicle_type').value = vehicle.vehicle_type ?? 'tricycle';
                document.getElementById('f_notes').value = vehicle.notes ?? '';
                document.getElementById('f_owner_id').value = vehicle.owner_id ?? '';

                // Reset mode proprio sur "existing"
                document.querySelector('input[name="_owner_mode"][value="existing"]').checked = true;
                switchOwnerMode('existing');

                const badge = document.getElementById('contract-badge');

                console.log(vehicle);
                if (vehicle.active_vehicle_contract) {
                    const c = vehicle.active_vehicle_contract;


                    // Afficher le résumé du contrat actif
                    const summary = document.getElementById('existing-contract-summary');
                    summary.classList.remove('hidden');

                    document.getElementById('ec-total').textContent = formatFcfa(c.total_amount);
                    document.getElementById('ec-monthly').textContent = formatFcfa(c.monthly_payment);
                    document.getElementById('ec-paid').textContent = formatFcfa(c.total_paid);
                    document.getElementById('ec-remaining').textContent = c.surplus > 0 ?
                        '+' + formatFcfa(c.surplus) + ' surplus' :
                        formatFcfa(c.remaining_amount);

                    const pct = Math.min(100, c.progress_percentage || 0);
                    document.getElementById('ec-progress-bar').style.width = pct + '%';
                    document.getElementById('ec-progress-bar').className =
                        `h-2 rounded-full ${pct >= 100 ? 'bg-orange-500' : 'bg-emerald-500'}`;
                    document.getElementById('ec-progress-pct').textContent = pct + '%';
                    document.getElementById('ec-link').href =
                        `/admin/vehicle-contracts/${c.id}`;

                    // Pré-remplir le formulaire avec les valeurs du contrat existant
                    document.getElementById('f_existing_contract_id').value = c.id;
                    document.getElementById('f_contract_total').value = c.total_amount ?? '';
                    document.getElementById('f_contract_monthly').value = c.monthly_payment ?? '';
                    document.getElementById('f_contract_start').value = new Date(c.start_date).toISOString().split('T')[0] ??
                        '';
                    document.getElementById('f_contract_end').value = c.end_date ?
                        new Date(c.end_date).toISOString().split('T')[0] :
                        '';
                    document.getElementById('f_contract_notes').value = c.notes ?? '';

                    // Badge
                    badge.textContent = 'Contrat actif';
                    badge.className = 'text-xs px-2 py-0.5 rounded-full font-semibold bg-emerald-100 text-emerald-700';
                    badge.classList.remove('hidden');

                } else {
                    // Pas de contrat → mode ajout
                    document.getElementById('existing-contract-summary').classList.add('hidden');
                    document.getElementById('f_existing_contract_id').value = '';
                    document.getElementById('f_contract_total').value = '';
                    document.getElementById('f_contract_monthly').value = '';
                    document.getElementById('f_contract_start').value = '';
                    document.getElementById('f_contract_end').value = '';
                    document.getElementById('f_contract_notes').value = '';

                    badge.textContent = 'Aucun contrat';
                    badge.className = 'text-xs px-2 py-0.5 rounded-full font-semibold bg-gray-100 text-gray-500';
                    badge.classList.remove('hidden');
                }

                document.getElementById('modal-form').classList.remove('hidden');
                document.getElementById('modal-form').classList.add('flex');
            }

            function closeFormModal() {
                document.getElementById('modal-form').classList.add('hidden');
                document.getElementById('modal-form').classList.remove('flex');
            }

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

            // ── Utilitaire ───────────────────────────────────────────────
            function formatFcfa(n) {
                return new Intl.NumberFormat('fr-FR').format(Math.round(n || 0)) + ' FCFA';
            }

            function switchOwnerMode(mode) {
                const isExisting = mode === 'existing';

                document.getElementById('owner-mode-existing-label').className =
                    `owner-mode-card cursor-pointer rounded-xl border-2 p-3 flex items-center gap-2 transition ${isExisting ? 'border-[#286b41] bg-[#286b41]/10' : 'border-gray-200 bg-white'}`;
                document.getElementById('owner-mode-new-label').className =
                    `owner-mode-card cursor-pointer rounded-xl border-2 p-3 flex items-center gap-2 transition ${!isExisting ? 'border-purple-600 bg-purple-50' : 'border-gray-200 bg-white'}`;

                document.getElementById('section-owner-existing').classList.toggle('hidden', !isExisting);
                document.getElementById('section-owner-new').classList.toggle('hidden', isExisting);
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
