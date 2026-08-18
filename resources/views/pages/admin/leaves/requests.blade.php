@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h1 class="text-lg md:text-2xl font-bold text-gray-800">Demandes de pause en Attente</h1>

            <div class="flex space-x-3">
                <a href="{{ route('admin.leaves.index') }}"
                    class="bg-gray-600 text-white text-nowrap px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Retour
                </a>
            </div>
        </div>
    </div>

    @if ($requests->isEmpty())
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
            <p class="text-green-800 text-lg font-semibold">Aucune demande de pause en attente ✓</p>
            <p class="text-green-600 text-sm mt-2">Toutes les demandes de pause ont été traitées.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($requests as $request)
                @php
                    $hasActiveContract = (bool) $request->driver->activeDriverContract;
                @endphp
                <div
                    class="bg-white rounded-lg shadow-md p-6 border-l-4 {{ $hasActiveContract ? 'border-yellow-500' : 'border-red-400' }}">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800">
                                {{ $request->driver->user->name }}
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">
                                Demande du {{ formatDateTimeFr($request->created_at) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span
                                class="inline-block bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
                                En attente
                            </span>
                            @unless ($hasActiveContract)
                                <span
                                    class="block mt-2 inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Sans contrat actif
                                </span>
                            @endunless
                        </div>
                    </div>

                    <!-- Driver Info -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 p-4 bg-blue-50 rounded-lg">
                        <div>
                            <p class="text-xs text-blue-600 font-semibold uppercase">Jours disponibles (indicatif)</p>
                            <p class="text-2xl font-bold text-blue-900">
                                {{ $request->driver->available_leave_days }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-blue-600 font-semibold uppercase">Jours restants total</p>
                            <p class="text-2xl font-bold text-blue-900">
                                {{ $request->driver->getRemainingLeaveDays() }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-blue-600 font-semibold uppercase">Jours demandés</p>
                            <p class="text-2xl font-bold text-blue-900">
                                {{ $request->requested_days }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-blue-600 font-semibold uppercase">Contrat actif</p>
                            <p class="text-xl font-bold {{ $hasActiveContract ? 'text-green-600' : 'text-red-600' }}">
                                {{ $hasActiveContract ? '✓ Oui' : '✗ Non' }}
                            </p>
                        </div>
                    </div>

                    <!-- Requested Period -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Période demandée:</h3>
                        <div class="flex flex-wrap gap-3 items-center">
                            <span class="inline-block bg-yellow-100 text-yellow-800 px-4 py-2 rounded-lg font-medium">
                                Du {{ formatDateFr($request->start_date) }}
                                ({{ $request->start_date->locale('fr')->translatedFormat('l') }})
                            </span>
                            <span class="text-gray-400">→</span>
                            <span class="inline-block bg-yellow-100 text-yellow-800 px-4 py-2 rounded-lg font-medium">
                                Fin prévue le {{ formatDateFr($request->expected_end_date) }}
                                ({{ $request->expected_end_date->locale('fr')->translatedFormat('l') }})
                            </span>
                            <span class="text-sm text-gray-500">— {{ $request->requested_days }} jour(s)</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            ℹ️ La pause restera active jusqu'à ce qu'un administrateur y mette fin, indépendamment de la
                            durée indiquée ici.
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        @if ($hasActiveContract)
                            <button type="button"
                                onclick="openApproveModal('{{ route('admin.leave.requests.approve', $request) }}')"
                                class="flex-1 bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 font-medium transition">
                                Approuver
                            </button>
                        @else
                            <div class="flex-1 bg-gray-100 text-gray-500 px-4 py-2 rounded-lg text-center font-medium cursor-not-allowed"
                                title="L'agent doit être sous contrat actif pour bénéficier d'une pause">
                                <i class="fas fa-lock mr-1"></i> Contrat requis
                            </div>
                        @endif

                        <button type="button" onclick="openRejectModal('{{ $request->id }}')"
                            class="flex-1 bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 font-medium transition">
                            Rejeter
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Approve Modal -->
    <div id="approveModal"
        class="fixed px-4 inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-20 w-full h-full">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Confirmer l'approbation</h2>
            <p class="text-gray-700 mb-6">Êtes-vous sûr de vouloir approuver cette demande de Pause ? Elle deviendra active
                immédiatement (ou à la date de début choisie).</p>
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
    <div id="rejectModal" class="fixed px-4 inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
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

    @push('scripts')
        <script>
            function openApproveModal(action) {
                const form = document.getElementById('approveForm');
                form.action = action;
                document.getElementById('approveModal').classList.remove('hidden');
                document.getElementById('approveModal').classList.add('flex');
            }

            function closeApproveModal() {
                document.getElementById('approveModal').classList.add('hidden');
                document.getElementById('approveModal').classList.remove('flex');
            }

            function openRejectModal(requestId) {
                const form = document.getElementById('rejectForm');
                form.action = `/admin/leave/requests/${requestId}/reject`;
                document.getElementById('rejectModal').classList.remove('hidden');
                document.getElementById('rejectModal').classList.add('flex');
            }

            function closeRejectModal() {
                document.getElementById('rejectModal').classList.add('hidden');
                document.getElementById('rejectModal').classList.remove('flex');
                document.getElementById('rejection_reason').value = '';
            }

            document.getElementById('approveModal')?.addEventListener('click', function(e) {
                if (e.target === this) closeApproveModal();
            });
            document.getElementById('rejectModal')?.addEventListener('click', function(e) {
                if (e.target === this) closeRejectModal();
            });
        </script>
    @endpush
@endsection
