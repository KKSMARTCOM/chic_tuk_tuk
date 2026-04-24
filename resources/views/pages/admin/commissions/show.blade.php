@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Détails de la Commission</h1>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.commissions.index') }}"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Informations principales -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Commission Info -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Informations de la Commission</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Revenue agent</p>
                            <p class="text-2xl font-bold text-green-600">
                                {{ number_format($commission->booking->driver_earning, 0, ',', ' ') }} FCFA</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Montant</p>
                            <p class="text-2xl font-bold text-green-600">
                                {{ number_format($commission->amount, 0, ',', ' ') }} FCFA</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Statut Paiement</p>
                            <span
                                class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                                {{ $commission->is_paid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $commission->is_paid ? 'Payé' : 'Non payé' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Date</p>
                            <p class="text-lg text-gray-900">
                                {{ formatDateFr($commission->date) ?? formatDateFr($commission->created_at) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions Sidebar -->
        <div class="space-y-6">
            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Actions</h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    @if (!$commission->is_paid)
                        <button onclick="openPaymentModal('{{ $commission->id }}', '{{ $commission->driver->user->name }}')"
                            class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                            <i class="fas fa-check-circle mr-2"></i> Valider Paiement
                        </button>
                    @else
                        <button onclick="openUnpaidModal('{{ $commission->id }}', '{{ $commission->driver->user->name }}')"
                            class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                            <i class="fas fa-undo mr-2"></i> Marquer Non Payée
                        </button>
                    @endif
                    <a href="{{ route('admin.drivers.show', $commission->driver->user) }}"
                        class="block w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-center">
                        <i class="fas fa-user mr-2"></i> Voir Conducteur
                    </a>
                    <a href="{{ route('admin.bookings.show', $commission->booking) }}"
                        class="block w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition text-center">
                        <i class="fas fa-route mr-2"></i> Voir Course
                    </a>
                </div>
            </div>

            <!-- System Info -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Informations Système</h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Créée le</span>
                        <span class="text-gray-900 font-medium">{{ $commission->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Modifiée le</span>
                        <span class="text-gray-900 font-medium">{{ $commission->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-20">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Valider le Paiement</h3>
            <p class="text-gray-600 mb-6" id="paymentMessage"></p>
            <input type="hidden" id="commissionId" value="">
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

    <!-- Unpaid Modal -->
    <div id="unpaidModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-20">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Marquer comme Non Payée</h3>
            <p class="text-gray-600 mb-6" id="unpaidMessage"></p>
            <input type="hidden" id="unpaidCommissionId" value="">
            <div class="flex space-x-4">
                <button type="button" onclick="closeUnpaidModal()"
                    class="flex-1 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                    Annuler
                </button>
                <button type="button" onclick="confirmUnpaid()"
                    class="flex-1 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
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

            function openPaymentModal(commissionId, driverName) {
                const message = `Êtes-vous sûr de vouloir marquer la commission de ${driverName} comme payée ?`;
                document.getElementById('paymentMessage').textContent = message;
                document.getElementById('commissionId').value = commissionId;
                document.getElementById('paymentModal').classList.remove('hidden');
                document.getElementById('paymentModal').classList.add('flex');
            }

            function closePaymentModal() {
                document.getElementById('paymentModal').classList.add('hidden');
                document.getElementById('paymentModal').classList.remove('flex');
            }

            function confirmPayment() {
                const commissionId = document.getElementById('commissionId').value;

                $.ajax({
                    url: `/admin/commissions/${commissionId}/mark-paid`,
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

            function openUnpaidModal(commissionId, driverName) {
                const message = `Êtes-vous sûr de vouloir marquer la commission de ${driverName} comme non payée ?`;
                document.getElementById('unpaidMessage').textContent = message;
                document.getElementById('unpaidCommissionId').value = commissionId;
                document.getElementById('unpaidModal').classList.remove('hidden');
                document.getElementById('unpaidModal').classList.add('flex');
            }

            function closeUnpaidModal() {
                document.getElementById('unpaidModal').classList.add('hidden');
                document.getElementById('unpaidModal').classList.remove('flex');
            }

            function confirmUnpaid() {
                const commissionId = document.getElementById('unpaidCommissionId').value;

                $.ajax({
                    url: `/admin/commissions/${commissionId}/mark-unpaid`,
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
