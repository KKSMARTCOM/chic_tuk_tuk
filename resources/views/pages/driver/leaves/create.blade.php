@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">Demande de Pauses</h1>

                <a href="{{ route('driver.leaves.index') }}"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Retour
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h1 class="text-lg md:text-2xl font-bold text-gray-800 mb-6">Demander une Pause</h1>

                    <!-- Leave Summary -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8 p-4 bg-blue-50 rounded-lg">
                        <div>
                            <p class="text-xs md:text-sm text-blue-600 font-semibold uppercase">Disponibles à date</p>
                            <p class="text-2xl font-bold text-blue-900">{{ $leaveInfo['available_leave_days'] }} jours</p>
                        </div>
                        <div>
                            <p class="text-xs md:text-sm text-blue-600 font-semibold uppercase">Restants</p>
                            <p class="text-2xl font-semibold text-blue-900">{{ $leaveInfo['remaining_leave_days'] }} jours
                            </p>
                        </div>
                    </div>

                    @if ($canRequest)
                        <form method="POST" action="{{ route('driver.leaves.store') }}">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                                    <input type="date" name="start_date" min="{{ now()->addDay()->toDateString() }}"
                                        required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                                    @error('start_date')
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de jours</label>
                                    <input type="number" name="requested_days" min="1" value="1" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2">
                                    @error('requested_days')
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <p id="expectedEndPreview" class="text-sm text-indigo-700 mb-6"></p>
                            <button type="submit"
                                class="w-full bg-green-500 text-white px-4 py-3 rounded-lg hover:bg-green-600 font-semibold">
                                Soumettre ma demande
                            </button>
                            <p class="text-xs text-gray-500 mt-4">
                                ℹ️ Votre pause sera active à partir de la date choisie jusqu'à ce qu'un administrateur y
                                mette fin — elle peut donc dépasser le nombre de jours indiqué.<br>
                                ℹ️ Demande à faire au moins 24h à l'avance.
                            </p>
                        </form>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                            <p class="text-yellow-800 font-semibold">
                                @if ($ongoingLeave)
                                    Vous avez déjà une pause en cours.
                                @else
                                    Vous avez déjà une demande en attente.
                                @endif
                            </p>
                            <a href="{{ route('driver.leaves.index') }}"
                                class="inline-block mt-3 text-sm text-yellow-800 underline">
                                Voir mes pauses
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar - Recent Requests -->
            <div>
                @if ($ongoingLeave)
                    <div class="bg-orange-50 rounded-lg shadow-md p-6 mb-6 border-l-4 border-orange-500">
                        <h2 class="text-lg font-semibold text-orange-900 mb-2">Pause en cours</h2>
                        <p class="text-sm text-gray-700">
                            Depuis le {{ formatDateFr($ongoingLeave->start_date) }}
                            — {{ $ongoingLeave->requested_days }} jour(s) demandé(s)
                        </p>
                        <p class="text-xs text-gray-600 mt-1">
                            Fin prévue le {{ formatDateFr($ongoingLeave->expected_end_date) }}
                        </p>
                    </div>
                @endif

                <!-- Pending Requests -->
                @if ($pendingRequests->count() > 0)
                    <div class="bg-yellow-50 rounded-lg shadow-md p-6 mb-6 border-l-4 border-yellow-500">
                        <h2 class="text-lg font-semibold text-yellow-900 mb-4">En attente</h2>
                        <div class="space-y-3">
                            @foreach ($pendingRequests as $request)
                                <div class="bg-white p-3 rounded border border-yellow-200">
                                    <p class="text-sm text-gray-600">Demande du {{ formatDateFr($request->created_at) }}
                                    </p>
                                    <p class="font-semibold text-gray-800">
                                        Du {{ formatDateFr($request->start_date) }} — {{ $request->requested_days }}
                                        jour(s) demandé(s)
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Fin prévue le {{ formatDateFr($request->expected_end_date) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Rejected Requests -->
                @if ($rejectedRequests->count() > 0)
                    <div class="bg-red-50 rounded-lg shadow-md p-6 border-l-4 border-red-500">
                        <h2 class="text-lg font-semibold text-red-900 mb-4">Rejetés récemment</h2>
                        <div class="space-y-3 max-h-48 overflow-y-auto custom-scrollbar">
                            @foreach ($rejectedRequests as $request)
                                <div class="bg-white p-3 rounded border border-red-200">
                                    <p class="text-sm text-gray-600">Demande du {{ formatDateFr($request->created_at) }}
                                    </p>
                                    <p class="font-semibold text-gray-800">
                                        Du {{ formatDateFr($request->start_date) }} — {{ $request->requested_days }}
                                        jour(s) demandé(s)
                                    </p>
                                    <p class="text-xs text-red-700 font-semibold mt-1">{{ $request->rejection_reason }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function updateExpectedEndPreview() {
                const startInput = document.getElementById('startDateInput').value;
                const days = parseInt(document.getElementById('requestedDaysInput').value, 10);
                const preview = document.getElementById('expectedEndPreview');

                if (!startInput || !days || days < 1) {
                    preview.textContent = '';
                    return;
                }

                let cursor = new Date(startInput + 'T00:00:00');
                while (cursor.getDay() === 0 || cursor.getDay() === 6) {
                    cursor.setDate(cursor.getDate() + 1);
                }

                let remaining = days;
                while (remaining > 1) {
                    cursor.setDate(cursor.getDate() + 1);
                    if (cursor.getDay() !== 0 && cursor.getDay() !== 6) {
                        remaining--;
                    }
                }

                preview.textContent =
                    `📅 Fin prévue le ${cursor.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' })} (les week-ends ne sont pas comptés)`;
            }

            document.getElementById('startDateInput').addEventListener('change', updateExpectedEndPreview);
            document.getElementById('requestedDaysInput').addEventListener('input', updateExpectedEndPreview);
        </script>
    @endpush
@endsection
