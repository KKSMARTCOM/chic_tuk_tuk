@if ($activeContract && $contractEditable)
    <div id="editContractModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-30 px-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">Modifier le contrat</h3>
                <button onclick="closeEditContractModal()" class="text-gray-400 hover:text-gray-600"><i
                        class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('admin.driver-contracts.update', $activeContract) }}" method="POST"
                class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Durée du contrat</label>
                        <select name="contract_months"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="24" {{ $activeContract->contract_months == 24 ? 'selected' : '' }}>24
                                mois</option>
                            <option value="30" {{ $activeContract->contract_months == 30 ? 'selected' : '' }}>30
                                mois</option>
                            <option value="36" {{ $activeContract->contract_months == 36 ? 'selected' : '' }}>36
                                mois</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de début</label>
                        <input type="date" name="start_date"
                            value="{{ $activeContract->start_date->format('Y-m-d') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Véhicule</label>
                        <select name="vehicle_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="{{ $currentVehicle->id }}" selected>
                                {{ $currentVehicle->vehicle_number }} (actuel)
                            </option>
                            @foreach ($owners as $owner)
                                @foreach ($owner->vehicles as $v)
                                    @if ($v->id !== $currentVehicle->id)
                                        <option value="{{ $v->id }}">{{ $v->vehicle_number }} —
                                            {{ $owner->name }}</option>
                                    @endif
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeEditContractModal()"
                        class="flex-1 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Annuler</button>
                    <button type="submit"
                        class="flex-1 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                        <i class="fas fa-save mr-1"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
