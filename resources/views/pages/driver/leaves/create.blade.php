@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="px-6 py-4 border-b border-gray-200 block md:flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-800">Demande de congés</h1>

                <div class="mt-4 md:mt-0">
                    <a href="{{ route('driver.leaves.index') }}"
                        class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-arrow-left mr-2"></i> Retour
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h1 class="text-2xl font-bold text-gray-800 mb-6">Demander un Congé</h1>

                    <!-- Leave Summary -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8 p-4 bg-blue-50 rounded-lg">
                        <div>
                            <p class="text-sm text-blue-600 font-semibold uppercase">Jours restants</p>
                            <p class="text-3xl font-bold text-blue-900">{{ $leaveInfo['remaining_leave_days'] }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-blue-600 font-semibold uppercase">Total disponible</p>
                            <p class="text-2xl font-semibold text-blue-900">{{ $leaveInfo['total_leave_days'] }} jours</p>
                        </div>
                    </div>

                    @if ($leaveInfo['remaining_leave_days'] > 0)
                        <form method="POST" action="{{ route('driver.leaves.store') }}" id="leaveForm">
                            @csrf
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Sélectionnez vos dates (mois courant)
                                </label>
                                <div class="mb-4">
                                    <input type="date" id="leaveDate"
                                        class="border border-gray-300 rounded-lg px-4 py-2 w-full"
                                        min="{{ now()->toDateString() }}" max="{{ now()->endOfMonth()->toDateString() }}"
                                        title="Les dates doivent être dans le mois courant">
                                    <button type="button" onclick="addDate()"
                                        class="mt-2 bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 w-full font-medium">
                                        + Ajouter une date
                                    </button>
                                </div>

                                <!-- Selected Dates Display -->
                                <div id="selectedDatesContainer" class="space-y-2">
                                    <p class="text-xs text-gray-500 font-semibold uppercase">Dates sélectionnées:</p>
                                    <div id="selectedDates"
                                        class="flex flex-wrap gap-2 min-h-12 p-3 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                        <p class="text-gray-400 text-sm w-full text-center py-2">Aucune date sélectionnée
                                        </p>
                                    </div>
                                </div>

                                <!-- Hidden inputs for form submission -->
                                <div id="datesInputs"></div>
                                @error('dates')
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                                @error('dates.*')
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                                <p id="dateError" class="text-sm text-red-600 mt-2 hidden"></p>
                            </div>

                            <div class="block md:flex gap-3">
                                <button type="submit" id="submitBtn"
                                    class="w-full flex-1 bg-green-500 text-white px-4 py-3 rounded-lg hover:bg-green-600 font-semibold disabled:bg-gray-300"
                                    disabled>
                                    Soumettre ma demande
                                </button>
                                <button type="button" onclick="clearDates()"
                                    class="w-full mt-4 md:mt-0 flex-1 bg-gray-300 text-gray-800 px-4 py-3 rounded-lg hover:bg-gray-400 font-semibold">
                                    Réinitialiser
                                </button>
                            </div>

                            <p class="text-xs text-gray-500 mt-4">
                                ℹ️ Vous pouvez demander jusqu'à <strong>{{ $leaveInfo['remaining_leave_days'] }}</strong>
                                jour(s) de congé.
                                Votre demande sera examinée par un administrateur.
                            </p>
                        </form>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                            <p class="text-yellow-800 font-semibold">Aucun jour de congé restant</p>
                            <p class="text-yellow-600 text-sm mt-2">
                                Vous avez utilisé tous vos jours de congé pour cette période.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar - Recent Requests -->
            <div>
                <!-- Pending Requests -->
                @if ($pendingRequests->count() > 0)
                    <div class="bg-yellow-50 rounded-lg shadow-md p-6 mb-6 border-l-4 border-yellow-500">
                        <h2 class="text-lg font-semibold text-yellow-900 mb-4">En attente</h2>
                        <div class="space-y-3">
                            @foreach ($pendingRequests as $request)
                                <div class="bg-white p-3 rounded border border-yellow-200">
                                    <p class="text-xs text-gray-600">{{ formatDateTimeFr($request->created_at) }}</p>
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @foreach ($request->dates as $date)
                                            <span
                                                class="inline-block text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded">
                                                {{ formatDateFr($date) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Approved Requests -->
                @if ($approvedRequests->count() > 0)
                    <div class="bg-green-50 rounded-lg shadow-md p-6 mb-6 border-l-4 border-green-500">
                        <h2 class="text-lg font-semibold text-green-900 mb-4">Approuvés</h2>
                        <div class="space-y-3">
                            @foreach ($approvedRequests as $request)
                                @foreach ($request->dates as $date)
                                    <div class="bg-white p-3 rounded border border-green-200">
                                        <p class="font-semibold text-green-800 text-sm">
                                            {{ formatDateFr($date) }}
                                        </p>
                                        <p class="text-xs text-gray-600">
                                            {{ \Carbon\Carbon::parse($date)->locale('fr')->translatedFormat('l') }}
                                        </p>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Rejected Requests -->
                @if ($rejectedRequests->count() > 0)
                    <div class="bg-red-50 rounded-lg shadow-md p-6 border-l-4 border-red-500">
                        <h2 class="text-lg font-semibold text-red-900 mb-4">Rejetés</h2>
                        <div class="space-y-3 max-h-48 overflow-y-auto custom-scrollbar">
                            @foreach ($rejectedRequests as $request)
                                <div class="bg-white p-3 rounded border border-red-200">
                                    <p class="text-xs text-gray-600">{{ formatDateTimeFr($request->created_at) }}</p>
                                    <div class="flex flex-wrap gap-1 my-2">
                                        @foreach ($request->dates as $date)
                                            <span class="inline-block text-xs bg-red-100 text-red-700 px-2 py-1 rounded">
                                                {{ formatDateFr($date) }}
                                            </span>
                                        @endforeach
                                    </div>
                                    <p class="text-xs text-red-700 font-semibold mt-1">{{ $request->rejection_reason }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        let selectedDates = [];
        const maxDays = {{ $leaveInfo['remaining_leave_days'] }};

        function setError(message) {
            const error = document.getElementById('dateError');
            error.textContent = message;
            error.classList.remove('hidden');
        }

        function clearError() {
            const error = document.getElementById('dateError');
            error.textContent = '';
            error.classList.add('hidden');
        }

        function addDate() {
            clearError();
            const dateInput = document.getElementById('leaveDate');
            const date = dateInput.value;

            if (!date) {
                setError('Veuillez sélectionner une date.');
                return;
            }

            if (selectedDates.includes(date)) {
                setError('Cette date est déjà sélectionnée.');
                return;
            }

            if (selectedDates.length >= maxDays) {
                setError(`Vous ne pouvez demander que ${maxDays} jour(s) maximum.`);
                return;
            }

            selectedDates.push(date);
            selectedDates.sort();
            updateDisplay();
            dateInput.value = '';
            dateInput.focus();
        }

        function removeDate(date) {
            selectedDates = selectedDates.filter(d => d !== date);
            updateDisplay();
        }

        function updateDisplay() {
            const container = document.getElementById('selectedDates');
            const inputsContainer = document.getElementById('datesInputs');
            const submitBtn = document.getElementById('submitBtn');

            if (selectedDates.length === 0) {
                container.innerHTML =
                    '<p class="text-gray-400 text-sm w-full text-center py-2">Aucune date sélectionnée</p>';
                inputsContainer.innerHTML = '';
                submitBtn.disabled = true;
                return;
            }

            container.innerHTML = selectedDates.map(date => {
                const dateObj = new Date(date + 'T00:00:00');
                const dayName = dateObj.toLocaleDateString('fr-FR', {
                    weekday: 'short'
                });
                return `
                        <div class="inline-flex items-center bg-indigo-100 text-indigo-800 px-3 py-2 rounded-lg text-sm font-medium">
                            ${dateObj.toLocaleDateString('fr-FR')} (${dayName})
                            <button type="button" onclick="removeDate('${date}')" class="ml-2 hover:text-indigo-600 font-bold">
                                ✕
                            </button>
                        </div>
                    `;
            }).join('');

            inputsContainer.innerHTML = selectedDates.map(date => `
                <input type="hidden" name="dates[]" value="${date}">
            `).join('');

            submitBtn.disabled = false;
        }

        function clearDates() {
            selectedDates = [];
            document.getElementById('leaveDate').value = '';
            clearError();
            updateDisplay();
        }

        // Allow Enter key to add date
        document.getElementById('leaveDate').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                addDate();
                e.preventDefault();
            }
        });
    </script>
@endsection
