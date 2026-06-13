@extends('layouts.app')

@section('content')
    <!-- Réservations Disponibles -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg md:text-xl font-bold text-gray-800">Réservations Disponibles</h3>
            <button onclick="location.reload()" class="text-green-600 hover:text-green-700">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
        </div>
        <div class="p-6">
            @if ($bookings->count() > 0)
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($bookings as $booking)
                        @php
                            $isParent = $booking->is_subscription_parent;
                            $isChild = $booking->is_subscription_child;
                            $isUnique = !$booking->is_recurring && !$isChild;
                            $isReturn = $booking->trip_type === 'return';

                            // Couleur bordure selon type
                            $borderClass = match (true) {
                                $isParent => 'border-emerald-400 bg-emerald-50/30',
                                $isChild => 'border-teal-400 bg-teal-50/30',
                                default => 'border-gray-200',
                            };
                        @endphp

                        <div class="border-2 {{ $borderClass }} rounded-lg p-4 hover:border-green-500 transition">

                            {{-- En-tête : badges --}}
                            <div class="flex flex-wrap items-center gap-1.5 mb-3">
                                {{-- Type de course --}}
                                @if ($isParent)
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                        <i class="fas fa-rotate"></i> Abonnement · {{ $booking->days }}j
                                    </span>
                                @elseif ($isChild)
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-teal-50 text-teal-700 border border-teal-200">
                                        <i class="fas fa-link"></i> {{ $booking->subscription_label }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-600 border border-blue-200">
                                        <i class="fas fa-car-side"></i> Course unique
                                    </span>
                                @endif

                                {{-- Aller-Retour --}}
                                @if ($booking->round_trip)
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-600 border border-purple-200">
                                        <i class="fas fa-arrows-left-right"></i>
                                        {{ $isReturn ? 'Retour' : 'Aller-Retour' }}
                                    </span>
                                @endif

                                {{-- Course révoquée --}}
                                @if ($booking->is_revoked)
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-500 border border-red-200">
                                        <i class="fas fa-ban"></i> Révoquée
                                    </span>
                                @endif

                            </div>

                            {{-- Référence abonnement parent pour les courses enfants --}}
                            @if ($isChild)
                                <div class="text-xs text-teal-600 font-medium mb-2 flex items-center gap-1">
                                    <i class="fas fa-link text-teal-400"></i>
                                    Abonnement {{ $booking->parentBooking?->booking_number }}
                                    @if ($booking->parentBooking?->user)
                                        · {{ $booking->parentBooking->user->name }}
                                    @elseif ($booking->parentBooking?->client_name)
                                        · {{ $booking->parentBooking->client_name }}
                                    @endif
                                </div>
                            @endif

                            {{-- Heure --}}
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-semibold text-gray-500">
                                    <i class="far fa-clock mr-1"></i>
                                    {{ formatDateFr($booking->pickup_date_time) }}
                                    · {{ formatTimeFr($booking->pickup_date_time) }}
                                </span>

                                {{-- Jours restants pour abonnement --}}
                                @if ($booking->days > 1)
                                    <span class="text-xs text-gray-400">
                                        {{ $booking->remaining_days }}j restants
                                    </span>
                                @endif
                            </div>

                            {{-- Trajet --}}
                            <div class="mb-3 space-y-1">
                                <div class="flex items-start text-sm text-gray-700">
                                    <i class="fas fa-map-marker-alt text-green-500 mr-2 mt-0.5 shrink-0"></i>
                                    <span class="font-semibold line-clamp-1">{{ $booking->from_location }}</span>
                                </div>
                                <div class="flex items-start text-sm text-gray-700">
                                    <i class="fas fa-map-marker-alt text-red-500 mr-2 mt-0.5 shrink-0"></i>
                                    <span class="line-clamp-1">{{ $booking->to_location }}</span>
                                </div>
                            </div>

                            {{-- Heure de retour : uniquement sur le parent --}}
                            @if ($isParent && $booking->round_trip && $booking->return_time)
                                <div class="text-xs text-purple-600 mb-3 flex items-center gap-1">
                                    <i class="fas fa-arrow-left"></i>
                                    Retour prévu à {{ $booking->return_time }} chaque jour
                                </div>
                            @endif

                            {{-- Infos abonnement --}}
                            @if ($booking->days > 1)
                                <div class="text-xs text-gray-500 mb-3 flex items-center gap-3">
                                    @if ($booking->week_days)
                                        <span>
                                            <i class="fas fa-calendar-week mr-1"></i>
                                            {{ match ($booking->week_days) {
                                                'lun_ven' => 'Lun → Ven',
                                                'lun_sam' => 'Lun → Sam',
                                                'lun_dim' => 'Lun → Dim',
                                                default => '',
                                            } }}
                                        </span>
                                    @endif
                                    @if ($booking->subscription_end_date)
                                        <span>
                                            <i class="fas fa-flag-checkered mr-1"></i>
                                            Fin le
                                            {{ \Carbon\Carbon::parse($booking->subscription_end_date)->locale('fr')->isoFormat('D MMM') }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <div class="flex items-center justify-between text-xs text-gray-600 mb-3">
                                <span><i class="far fa-clock mr-1"></i>
                                    {{ formatDateFr($booking->pickup_date_time) }}</span>
                            </div>

                            <form action="{{ route('driver.bookings.accept', $booking->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold text-sm">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    @if ($isParent)
                                        Accepter l'abonnement
                                    @elseif ($isChild)
                                        Accepter cette course
                                    @else
                                        Accepter
                                    @endif
                                </button>
                            </form>

                            {{-- Révoquer : uniquement pour les courses enfants liées au titulaire --}}
                            @if ($isChild && $booking->subscription_driver_id === auth()->user()->driver?->id)
                                <button onclick="revokeTrip('{{ $booking->id }}')"
                                    class="w-full mt-2 py-2 border border-amber-400 text-amber-600 rounded-lg hover:bg-amber-50 transition font-semibold text-sm">
                                    <i class="fas fa-ban mr-2"></i> Révoquer
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-clipboard-list text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600 text-lg">Aucune réservation disponible</p>
                    <p class="text-gray-500 text-sm">De nouvelles courses apparaîtront bientôt</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal pour revoquer une course enfant --}}
    <div id="revokeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h3 class="text-xl font-bold text-gray-800 mb-3">Révoquer cette course</h3>
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                <p class="text-sm text-amber-700 flex items-start gap-2">
                    <i class="fas fa-triangle-exclamation mt-0.5 shrink-0"></i>
                    Cette course sera libérée et accessible à tous les agents.
                    Vous restez titulaire des autres courses de cet abonnement.
                </p>
            </div>
            <form id="revokeForm" method="POST">
                @csrf
                <div class="flex space-x-4">
                    <button type="button" onclick="closeRevokeModal()"
                        class="flex-1 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                        Retour
                    </button>
                    <button type="submit"
                        class="flex-1 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition">
                        Confirmer la révocation
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function revokeTrip(bookingId) {
                const modal = document.getElementById('revokeModal');
                const form = document.getElementById('revokeForm');
                form.action = `/driver/bookings/${bookingId}/revoke-subscription`;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeRevokeModal() {
                document.getElementById('revokeModal').classList.add('hidden');
                document.getElementById('revokeModal').classList.remove('flex');
            }
        </script>
    @endpush
@endsection
