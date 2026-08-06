{{-- resources/views/inc/partials/vehicle-contract-fields.blade.php
     Paramètres :
       $prefix   → nom des champs (ex: "vehicles[uuid]", "new", "existing_vehicle")
       $context  → identifiant unique pour les ids JS (ex: "existing_uuid", "add-new")
       $contract → App\Models\VehicleContract|null (null = création)
--}}

<div class="bg-purple-50 border border-purple-100 rounded-lg p-3 space-y-3">
    <p class="text-xs font-bold text-purple-700 uppercase tracking-wide">
        Contrat propriétaire-véhicule
    </p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Type de contrat</label>
            <select name="{{ $prefix }}[contract_months]" id="contract_months_{{ $context }}"
                onchange="onContractMonthsChange('{{ $context }}')"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                <option value="">— Sélectionnez —</option>
                @foreach ([24, 30, 36] as $m)
                    @php
                        $contractMonths = $contract?->contract_months;
                        $oldVal = old("{$prefix}.contract_months");
                        $selected = ($oldVal ?? $contractMonths) == $m;
                    @endphp
                    <option value="{{ $m }}" {{ $selected ? 'selected' : '' }}>{{ $m }} mois
                    </option>
                @endforeach
                {{-- @php
                    $isOther = $contract && !in_array($contract->contract_months, [24, 30, 36]);
                    $oldIsOther = old("{$prefix}.contract_months") === 'other';
                @endphp
                <option value="other" {{ $oldIsOther || $isOther ? 'selected' : '' }}>Autre</option> --}}
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Montant total (FCFA)</label>
            <input type="number" name="{{ $prefix }}[contract_total_amount]"
                id="contract_total_{{ $context }}" min="0"
                value="{{ old("{$prefix}.contract_total_amount", $contract?->total_amount) }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none"
                placeholder="ex: 3 100 000">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Date de début</label>
            <input type="date" name="{{ $prefix }}[contract_start_date]"
                value="{{ old("{$prefix}.contract_start_date", $contract?->start_date?->format('Y-m-d')) }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
        </div>
    </div>

    {{-- Charges mensuelles --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Internet illimité (FCFA)</label>
            <input type="number" name="{{ $prefix }}[unlimited_internet]" min="0"
                value="{{ old("{$prefix}.unlimited_internet", $contract?->unlimited_internet ?? \App\Consts\VehicleContract::DEFAULT_UNLIMITED_INTERNET) }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Spotify Premium (FCFA)</label>
            <input type="number" name="{{ $prefix }}[spotify_premium]" min="0"
                value="{{ old("{$prefix}.spotify_premium", $contract?->spotify_premium ?? \App\Consts\VehicleContract::DEFAULT_SPOTIFY_PREMIUM) }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Rémunération manager (FCFA)</label>
            <input type="number" name="{{ $prefix }}[manager_remuneration]" min="0"
                value="{{ old("{$prefix}.manager_remuneration", $contract?->manager_remuneration ?? \App\Consts\VehicleContract::DEFAULT_MANAGER_REMUNERATION) }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
        </div>
    </div>
</div>
