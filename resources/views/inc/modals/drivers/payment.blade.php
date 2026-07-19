<div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center px-4 z-20">
    <div class="bg-white rounded-lg p-6 max-w-lg w-full">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Enregistrer un paiement</h3>
        <form action="{{ route('admin.payments.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="driver_id" value="{{ $driverProfile->id }}">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Montant (FCFA) <span
                        class="text-red-500">*</span></label>
                <input type="number" name="amount" step="0.01" min="0" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Moyen de paiement <span
                        class="text-red-500">*</span></label>
                <select name="payment_method" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="cash">Espèces</option>
                    <option value="bank_transfer">Virement</option>
                    <option value="check">Chèque</option>
                    <option value="mobile_money">Mobile Money</option>
                    <option value="other">Autre</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date <span
                        class="text-red-500">*</span></label>
                <input type="date" name="payment_date" required value="{{ date('Y-m-d') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="flex-1 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-semibold">
                    <i class="fas fa-save mr-1"></i> Enregistrer
                </button>
                <button type="button" onclick="closePaymentModal()"
                    class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Annuler</button>
            </div>
        </form>
    </div>
</div>
