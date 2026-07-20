<div id="cancelModal" class="fixed px-4 inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-10">
    <div class="bg-white rounded-lg p-8 max-w-md w-full">

        <h3 class="text-2xl font-bold text-gray-800 mb-2" id="cancelModalTitle">
            Annuler la course
        </h3>

        <p class="text-gray-600 mb-2" id="cancelModalDesc">
            Êtes-vous sûr de vouloir annuler cette course ?
        </p>

        {{-- Avertissement abonnement --}}
        <div id="cancelSubscriptionWarning" class="hidden bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
            <p class="text-sm text-amber-700 flex items-start gap-2">
                <i class="fas fa-triangle-exclamation mt-0.5 shrink-0"></i>
                Annuler l'abonnement arrêtera toutes les courses futures.
                Les courses déjà effectuées ne sont pas affectées.
                Les courses enfants en attente seront également annulées.
            </p>
        </div>

        <form id="cancelForm" method="POST" action="">
            @csrf
            <input type="hidden" name="status" value="cancelled">
            <textarea name="cancellation_reason" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg mb-4"
                placeholder="Raison de l'annulation (optionnel)"></textarea>
            <div class="flex space-x-4">
                <button type="button" onclick="closeCancelModal()"
                    class="flex-1 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                    Retour
                </button>
                <button type="submit" class="flex-1 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    Confirmer
                </button>
            </div>
        </form>
    </div>
</div>
