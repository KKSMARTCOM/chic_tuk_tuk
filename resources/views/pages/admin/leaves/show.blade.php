@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.leaves.index') }}" class="text-indigo-600 hover:text-indigo-900">&larr; Retour à la liste</a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Détails des Congés - {{ $driver->name }}</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-blue-800">Jours par mois</h3>
                <p class="text-2xl font-bold text-blue-900">{{ $leaveInfo['leave_days_per_month'] }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-green-800">Total disponible</h3>
                <p class="text-2xl font-bold text-green-900">{{ $leaveInfo['total_leave_days'] }}</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-yellow-800">Jours utilisés</h3>
                <p class="text-2xl font-bold text-yellow-900">{{ $leaveInfo['leave_days_used'] }}</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-purple-800">Jours restants</h3>
                <p class="text-2xl font-bold text-purple-900">{{ $leaveInfo['remaining_leave_days'] }}</p>
            </div>
        </div>

        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Dates de congé prises</h2>
            @if(count($leaveInfo['leave_dates']) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($leaveInfo['leave_dates'] as $date)
                    <div class="bg-red-50 p-4 rounded-lg flex justify-between items-center">
                        <span class="text-red-800">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
                        <form method="POST" action="{{ route('admin.leaves.revoke', $driver) }}" class="inline">
                            @csrf
                            <input type="hidden" name="leave_date" value="{{ $date }}">
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Êtes-vous sûr de vouloir révoquer ce congé ?')">Révoquer</button>
                        </form>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">Aucun congé pris pour le moment.</p>
            @endif
        </div>

        @if($leaveInfo['can_request_leave'])
        <div class="bg-gray-50 p-6 rounded-lg">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Approuver une demande de congé</h2>
            <form method="POST" action="{{ route('admin.leaves.approve', $driver) }}">
                @csrf
                <div class="mb-4">
                    <label for="leave_dates" class="block text-sm font-medium text-gray-700 mb-2">Sélectionner les dates</label>
                    <input type="date" name="leave_dates[]" id="leave_dates" class="border border-gray-300 rounded-md px-3 py-2" min="{{ now()->addDay()->toDateString() }}" required>
                    <button type="button" id="add_date" class="ml-2 bg-blue-500 text-white px-3 py-1 rounded text-sm">Ajouter une date</button>
                </div>
                <div id="selected_dates" class="mb-4"></div>
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Approuver la demande</button>
            </form>
        </div>
        @else
        <div class="bg-yellow-50 p-4 rounded-lg">
            <p class="text-yellow-800">Le conducteur ne peut pas demander de congé actuellement (pas assez de jours restants ou pas dans le mois en cours).</p>
        </div>
        @endif
    </div>
</div>

<script>
document.getElementById('add_date').addEventListener('click', function() {
    const dateInput = document.getElementById('leave_dates');
    const selectedDates = document.getElementById('selected_dates');
    const date = dateInput.value;

    if (date) {
        const dateDiv = document.createElement('div');
        dateDiv.className = 'inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded mr-2 mb-2';
        dateDiv.innerHTML = date + ' <button type="button" onclick="removeDate(this)" class="ml-1 text-red-600">&times;</button>';
        
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'leave_dates[]';
        hiddenInput.value = date;
        dateDiv.appendChild(hiddenInput);
        
        selectedDates.appendChild(dateDiv);
        dateInput.value = '';
    }
});

function removeDate(button) {
    button.parentElement.remove();
}
</script>
@endsection
