@extends('layouts.main')

@section('content')
    <!-- Hero Section avec Formulaire de Réservation -->
    <section id="reservation"
        style="background: url('{{ asset('assets/images/png/tricycle_bg.png') }}') no-repeat center center/cover"
        class="h-[750px] relative">
        <div class="w-full h-full absolute inset-0 bg-black/80"></div>
        <div class="w-full md:max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 absolute inset-0 flex items-center">
            <div class="w-full">
                <div class="block md:grid grid-cols-1 md:grid-cols-2 gap-12 items-center w-full">
                    <!-- Texte Hero -->
                    <div class="text-white hidden md:block">
                        <h2 class="text-5xl font-bold mb-6">Voyagez avec Style et Confort</h2>
                        <p class="text-xl mb-8 text-purple-100">Découvrez une nouvelle façon de vous déplacer avec nos
                            chic tuk tuk. Unique, modèle et confortable.</p>
                        <div class="flex space-x-6">
                            <div class="text-center">
                                <div class="text-4xl font-bold">500+</div>
                                <div class="text-purple-200">Courses réalisées</div>
                            </div>
                            <div class="text-center">
                                <div class="text-4xl font-bold">4.9/5</div>
                                <div class="text-purple-200">Note moyenne</div>
                            </div>
                            <div class="text-center">
                                <div class="text-4xl font-bold">50+</div>
                                <div class="text-purple-200">Conducteurs</div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulaire de Réservation Multi-étapes -->
                    <div class="bg-white rounded-2xl shadow-2xl p-8">
                        <h3 class="text-2xl font-bold text-gray-800 mb-6">Réservez votre course</h3>

                        <!-- Indicateur d'étapes -->
                        <div class="flex justify-between mb-8">
                            <div class="flex flex-col items-center flex-1">
                                <div id="step1-indicator"
                                    class="w-10 h-10 rounded-full step-active flex items-center justify-center font-bold mb-2">
                                    1</div>
                                <span class="text-xs text-gray-600">Trajet</span>
                            </div>
                            <div class="flex-1 flex items-center justify-center" style="margin-bottom: 26px;">
                                <div class="h-1 bg-gray-300 w-full"></div>
                            </div>
                            <div class="flex flex-col items-center flex-1">
                                <div id="step2-indicator"
                                    class="w-10 h-10 rounded-full bg-[#FFE7C1] text-gray-600 flex items-center justify-center font-bold mb-2">
                                    2</div>
                                <span class="text-xs text-gray-600">Date & Heure</span>
                            </div>
                            <div class="flex-1 flex items-center justify-center" style="margin-bottom: 26px;">
                                <div class="h-1 bg-gray-300 w-full"></div>
                            </div>
                            <div class="flex flex-col items-center flex-1">
                                <div id="step3-indicator"
                                    class="w-10 h-10 rounded-full bg-[#FFE7C1] text-gray-600 flex items-center justify-center font-bold mb-2">
                                    3</div>
                                <span class="text-xs text-gray-600">Détails</span>
                            </div>
                        </div>

                        <form id="bookingForm" action="{{ route('bookings.store') }}" method="POST">
                            @csrf

                            <!-- Étape 1: Trajet -->
                            <div id="step1" class="step-content">
                                {{-- <div class="mb-4">
                                    <label class="block text-gray-700 font-semibold mb-2">Type de trajet</label>
                                    <div class="grid grid-cols-2 gap-4">
                                        <button type="button" onclick="selectTripType(event, 'normal')"
                                            class="trip-type-btn border-2  border-emerald-600 text-emerald-600 rounded-lg p-4 hover:bg-purple-50 transition">
                                            <i class="fas fa-route text-2xl mb-2"></i>
                                            <div class="font-semibold">Trajet Simple</div>
                                        </button>
                                        <button type="button" onclick="selectTripType(event, 'circuit')"
                                            class="trip-type-btn border-2 border-gray-300 text-gray-600 rounded-lg p-4 hover:bg-gray-50 transition">
                                            <i class="fas fa-map-marked-alt text-2xl mb-2"></i>
                                            <div class="font-semibold">Circuit Touristique</div>
                                        </button>
                                    </div>
                                    <input type="hidden" name="trip_type" id="trip_type" value="normal">
                                </div> --}}

                                <div id="normalTrip">
                                    <div class="mb-4">
                                        @if ($zones && $zones->count())
                                            <label class="block text-gray-700 font-semibold mb-2">Point de départ</label>
                                            <select name="from_zone_id" id="from_zone_id"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent"
                                                required>
                                                <option value="">Entrez votre adresse de départ</option>
                                                @foreach ($zones as $item)
                                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>
                                    <div class="mb-4">
                                        @if ($zones && $zones->count())
                                            <label class="block text-gray-700 font-semibold mb-2">Destination</label>
                                            <select name="to_zone_id" id="to_zone_id"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent"
                                                required>
                                                <option value="">Où souhaitez-vous aller ?</option>
                                                @foreach ($zones as $item)
                                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>

                                    <!-- Aperçu du prix (affiché dès que départ + arrivée sont sélectionnés) -->
                                    <div id="pricePreview" class="my-4 text-sm text-gray-700 hidden">
                                        Prix de base: <span id="preview-price">-- FCFA</span>
                                    </div>
                                </div>

                                {{-- <div id="circuitTrip" class="hidden">
                                    <div class="mb-4">
                                        <label class="block text-gray-700 font-semibold mb-2">Choisir un circuit</label>
                                        <select name="tourist_circuit_id"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent">
                                            <option value="">Sélectionnez un circuit</option>
                                            <option value="1">Visite Ouidah - 10,000 FCFA</option>
                                            <option value="2">Visite Comè - 20,000 FCFA</option>
                                            <option value="3">Visite Grand-Popo - 25,000 FCFA</option>
                                        </select>
                                    </div>
                                </div> --}}

                                <button type="button" onclick="nextStep(2)"
                                    class="w-full py-3 bg-[#286b41] text-white rounded-lg font-semibold hover:opacity-90 transition">
                                    Suivant <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>

                            <!-- Étape 2: Date & Heure -->
                            <div id="step2" class="step-content hidden">
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-semibold mb-2">Date et heure</label>
                                    <input type="datetime-local" name="pickup_datetime"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent"
                                        required>
                                </div>

                                <!-- Option multi-jours -->
                                <div class="mb-4">
                                    <label class="inline-flex items-center text-gray-700 font-semibold">
                                        <input type="checkbox" id="multi_day" class="mr-3">
                                        <span>Réservation sur plusieurs jours</span>
                                    </label>

                                    <!-- Hidden input that holds the actual number of days sent to server (default 1) -->
                                    <input type="hidden" name="days" id="days_hidden" value="1">

                                    <!-- Visible input shown only when multi-day checked (no name attribute) -->
                                    <div id="daysWrapper" class="mt-3 hidden">
                                        <label class="block text-gray-700 font-semibold mb-2">Nombre de jours</label>
                                        <input type="text" id="days_input" inputmode="numeric" min="2"
                                            value="2"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent"
                                            placeholder="Entrez le nombre de jours">
                                    </div>
                                </div>

                                {{-- <div class="mb-6">
                                    <label class="block text-gray-700 font-semibold mb-2">Nombre de passagers</label>
                                    <select name="passengers"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent"
                                        required>
                                        <option value="1">1 passager</option>
                                        <option value="2">2 passagers</option>
                                        <option value="3">3 passagers</option>
                                    </select>
                                </div> --}}

                                <div class="flex space-x-4">
                                    <button type="button" onclick="prevStep(1)"
                                        class="flex-1 py-3 bg-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-400 transition">
                                        <i class="fas fa-arrow-left mr-2"></i> Retour
                                    </button>
                                    <button type="button" onclick="nextStep(3)"
                                        class="flex-1 py-3 bg-[#286b41] text-white rounded-lg font-semibold hover:opacity-90 transition">
                                        Suivant <i class="fas fa-arrow-right ml-2"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Étape 3: Détails et Confirmation -->
                            <div id="step3" class="step-content hidden">
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-semibold mb-2">Numéro de téléphone</label>
                                    <input type="tel" name="phone" id="phone" placeholder="01 90 12 34 56"
                                        pattern="^\d{6,15}$"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent"
                                        required>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-gray-700 font-semibold mb-2">Demandes spéciales, veuillez
                                        donner la localisation précise de l'adresse de départ et d'arrivée
                                        (optionnel)</label>
                                    <textarea name="special_requests" rows="3"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent"
                                        placeholder="Bagages volumineux, animaux, etc."></textarea>
                                </div>
                                {{-- <div class="mb-6">
                                    <label class="block text-gray-700 font-semibold mb-2">Code promo (optionnel)</label>
                                    <div class="flex space-x-2">
                                        <input type="text" name="promo_code" id="promo_code"
                                            class="flex-1 px-4 py-3 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent"
                                            placeholder="Entrez votre code">
                                        <button type="button" onclick="applyPromo()"
                                            class="px-6 py-3 bg-green-500 text-white rounded-lg font-semibold hover:bg-green-600 transition">Appliquer</button>
                                    </div>
                                    <div id="promo-message" class="mt-2 text-sm"></div>
                                </div> --}}

                                <div class="bg-purple-50 rounded-lg p-4 mb-6">
                                    {{-- <div class="flex justify-between mb-2">
                                        <span class="text-gray-700">Prix de base:</span>
                                        <span class="font-semibold" id="base-price">-- FCFA</span>
                                    </div>
                                    <div class="flex justify-between mb-2 text-green-600">
                                        <span>Réduction:</span>
                                        <span class="font-semibold" id="discount">0 FCFA</span>
                                    </div>
                                    <hr class="my-2"> --}}
                                    <div class="flex justify-between text-lg font-bold text-[#286b41]">
                                        <span>Total:</span>
                                        <span id="total-price">-- FCFA</span>
                                    </div>
                                </div>

                                <div class="flex space-x-4">
                                    <button type="button" onclick="prevStep(2)"
                                        class="flex-1 py-3 bg-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-400 transition">
                                        <i class="fas fa-arrow-left mr-2"></i> Retour
                                    </button>
                                    <button type="submit"
                                        class="flex-1 py-3 gradient-bg text-white rounded-lg font-semibold hover:opacity-90 transition">
                                        Confirmer la réservation <i class="fas fa-check ml-2"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Étape 4: Succès (invisible dans l’indicateur) -->
                            <div id="step4" class="step-content hidden">
                                <div
                                    class="relative overflow-hidden rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-emerald-50 p-8">
                                    <!-- Confettis CSS -->
                                    <div class="confetti">
                                        @for ($i = 1; $i <= 18; $i++)
                                            <span class="confetti-piece"></span>
                                        @endfor
                                    </div>

                                    <div class="text-center">
                                        <div
                                            class="mx-auto w-20 h-20 rounded-full bg-emerald-100 flex items-center justify-center shadow-lg success-pop">
                                            <i class="fas fa-check text-4xl text-emerald-600"></i>
                                        </div>

                                        <h4 class="mt-6 text-3xl font-extrabold text-gray-900 success-pop"
                                            style="animation-delay:.05s">
                                            Réservation envoyée !
                                        </h4>

                                        <p class="mt-2 text-gray-600 text-base success-pop" style="animation-delay:.1s">
                                            Merci 🙌 Votre demande a bien été enregistrée. Un chauffeur vous contactera très
                                            bientôt.
                                        </p>

                                        {{-- @php($sum = session('success_summary'))

                                        @if (is_array($sum))
                                            <div class="mt-6 mx-auto max-w-md text-left rounded-xl bg-white/80 backdrop-blur border border-emerald-100 p-4 shadow success-pop"
                                                style="animation-delay:.15s">
                                                <div class="flex items-center justify-between text-sm text-gray-700">
                                                    <span class="font-medium">Départ</span>
                                                    <span
                                                        class="font-semibold text-gray-900">{{ $sum['from'] ?: '—' }}</span>
                                                </div>
                                                <div class="flex items-center justify-between text-sm text-gray-700 mt-2">
                                                    <span class="font-medium">Destination</span>
                                                    <span
                                                        class="font-semibold text-gray-900">{{ $sum['to'] ?: '—' }}</span>
                                                </div>
                                                <div class="flex items-center justify-between text-sm text-gray-700 mt-2">
                                                    <span class="font-medium">Date & Heure</span>
                                                    <span
                                                        class="font-semibold text-gray-900">{{ $sum['datetime'] ?: '—' }}</span>
                                                </div>
                                                <div class="flex items-center justify-between text-sm mt-2">
                                                    <span class="font-medium text-gray-700">Total</span>
                                                    <span class="font-extrabold text-emerald-700">
                                                        {{ $sum['total'] ? number_format($sum['total'], 0, ',', ' ') . ' FCFA' : '—' }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif --}}

                                        <div class="mt-8">
                                            <button type="button" onclick="newBooking()"
                                                class="inline-flex items-center justify-center px-7 py-3 rounded-xl bg-[#286b41] text-white font-semibold shadow-lg hover:opacity-95 active:scale-[.99] transition success-pop"
                                                style="animation-delay:.2s">
                                                Nouvelle réservation <i class="fas fa-plus ml-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Comment ça marche -->
    <section id="comment-ca-marche" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Comment ça marche ?</h2>
                <p class="text-xl text-gray-600">Réservez votre tricycle en 3 étapes simples</p>
            </div>

            <div class="grid md:grid-cols-3 gap-12">
                <div class="text-center">
                    <div
                        class="w-20 h-20 bg-[#FFE7C1] border border-[#286b41] rounded-full flex items-center justify-center text-black text-3xl font-bold mx-auto mb-6">
                        <i class="fas fa-mobile-alt text-3xl"></i>
                    </div>

                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Réservez en ligne</h3>
                    <p class="text-gray-600">Remplissez le formulaire avec vos informations de trajet. C'est simple et
                        rapide !</p>
                </div>

                <div class="text-center">
                    <div
                        class="w-20 h-20 bg-[#FFE7C1] border border-[#286b41] rounded-full flex items-center justify-center text-black text-3xl font-bold mx-auto mb-6">
                        <i class="fas fa-user-check text-3xl"></i>
                    </div>

                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Confirmation instantanée</h3>
                    <p class="text-gray-600">Un conducteur accepte votre réservation et vous recevez une confirmation
                        immédiate par whatsapp ou par appel téléphonique.</p>
                </div>

                <div class="text-center">
                    <div
                        class="w-20 h-20 bg-[#FFE7C1] border border-[#286b41] rounded-full flex items-center justify-center text-black text-3xl font-bold mx-auto mb-6">
                        <i class="fas fa-route text-3xl"></i>
                    </div>

                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Profitez de votre trajet</h3>
                    <p class="text-gray-600">Votre conducteur vous attend à l'heure et au lieu convenus. Bon voyage !
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Ce que nous offrons -->
    <section id="avantages" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Ce que nous offrons</h2>
                <p class="text-xl text-gray-600">Des avantages qui font la différence</p>
            </div>

            <div class="advantage-slider">
                <!-- Carte 1 -->
                <div
                    class="bg-white rounded-2xl shadow-lg overflow-hidden group transition duration-300 hover:-translate-y-2">
                    <div class="h-60 overflow-hidden">
                        <img src="{{ asset('assets/images/jpg/child1.jpeg') }}"
                            class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                    </div>
                    <div class="p-6 h-48">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">
                            Transport scolaire en tricycle
                        </h3>
                        <p class="text-gray-600 text-sm">
                            Réservez un tricycle pour assurer les trajets aller et retour de vos enfants
                            vers l’école en toute sécurité. Une solution pratique, fiable et adaptée
                            pour accompagner les élèves chaque jour.
                        </p>
                    </div>
                </div>

                <!-- Carte 2 -->
                <div
                    class="bg-white rounded-2xl shadow-lg overflow-hidden group transition duration-300 hover:-translate-y-2">
                    <div class="h-60 overflow-hidden">
                        <img src="{{ asset('assets/images/jpg/wifi.jpg') }}"
                            class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                    </div>
                    <div class="p-6 h-48">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Tricycle connecté</h3>
                        <p class="text-gray-600 text-sm">
                            Profitez d’un tricycle équipé d’une connexion Wi-Fi fluide et stable,
                            idéale pour rester connecté, travailler ou se divertir pendant vos trajets.
                        </p>
                    </div>
                </div>

                <!-- Carte 3 -->
                <div
                    class="bg-white rounded-2xl shadow-lg overflow-hidden group transition duration-300 hover:-translate-y-2">
                    <div class="h-60 overflow-hidden">
                        <img src="{{ asset('assets/images/png/pub.png') }}"
                            class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                    </div>
                    <div class="p-6 h-48">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Publicité à travers la ville</h3>
                        <p class="text-gray-600 text-sm">
                            Faites connaître votre activité en affichant votre publicité sur nos tricycles.
                            Votre message circule dans toute la ville et touche un large public de façon
                            visible, dynamique et efficace.
                        </p>
                    </div>
                </div>

                <!-- Carte 4 -->
                <div
                    class="bg-white rounded-2xl shadow-lg overflow-hidden group transition duration-300 hover:-translate-y-2">
                    <div class="h-60 overflow-hidden">
                        <img src="{{ asset('assets/images/jpg/women2.jpeg') }}" alt="Security"
                            class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                    </div>
                    <div class="p-6 h-48">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Sécurité garantie</h3>
                        <p class="text-gray-600 text-sm">
                            Tous nos conducteurs sont formés et vérifiés pour votre sécurité.
                        </p>
                    </div>
                </div>

                <!-- Carte 5 -->
                <div
                    class="bg-white rounded-2xl shadow-lg overflow-hidden group transition duration-300 hover:-translate-y-2">
                    <div class="h-60 overflow-hidden">
                        <img src="{{ asset('assets/images/jpg/women1.jpeg') }}"
                            class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                    </div>
                    <div class="p-6 h-48">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Ponctualité</h3>
                        <p class="text-gray-600 text-sm">
                            Arrivée à l'heure garantie pour tous vos trajets.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Ils nous soutiennent -->
    <section id="partenaire" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Ils nous soutiennent</h2>
                <p class="text-xl text-gray-600">Nos partenaires de confiance</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-6">
                <!-- Item -->
                <span
                    class="group rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-lg transition p-6 flex items-center justify-center">
                    <img src="{{ asset('assets/images/png/gozem.png') }}" alt="Gozem"
                        class="h-12 w-auto object-cover grayscale contrast-200 opacity-70 group-hover:grayscale-0 group-hover:contrast-100 group-hover:opacity-100 transition duration-300"
                        loading="lazy" />
                </span>

                <span
                    class="group rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-lg transition p-6 flex items-center justify-center">
                    <img src="{{ asset('assets/images/png/yango.png') }}" alt="Yango"
                        class="h-12 w-auto object-cover grayscale contrast-200 opacity-70 group-hover:grayscale-0 group-hover:contrast-100 group-hover:opacity-100 transition duration-300"
                        loading="lazy" />
                </span>

                <span
                    class="group rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-lg transition p-6 flex items-center justify-center">
                    <img src="{{ asset('assets/images/png/bajaj.png') }}" alt="Bajaj"
                        class="h-12 w-auto object-cover grayscale contrast-200 opacity-70 group-hover:grayscale-0 group-hover:contrast-100 group-hover:opacity-100 transition duration-300"
                        loading="lazy" />
                </span>
            </div>
        </div>
    </section>

    <!-- Devenir investisseur -->
    <section id="partenaire" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Devenir investisseur</h2>
                <p class="text-xl text-gray-600">Un modèle de microfinancement participatif pour équiper les chauffeurs de
                    tricycles connectés et rentables.</p>
            </div>

            <div class="text-center">
                <a href="http://pitch.kksmartcom.com/chictuktuk" target="_blank"
                    class="px-6 py-2 bg-[#286b41] text-white rounded-full hover:opacity-90 shadow-lg shadow-emerald-600/30 transition font-medium">
                    Devenir investisseur
                </a>
            </div>
        </div>
    </section>

    <!-- Témoignages -->
    <section id="temoignages" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Ce que nos clients disent</h2>
                <p class="text-xl text-gray-600">Des milliers de clients satisfaits</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-gray-50 rounded-xl p-8 shadow-md hover:shadow-xl transition">
                    <div class="flex items-center mb-4">
                        <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode('Armand Kouassi') }}"
                            alt="Armand Kouassi" class="w-16 h-16 rounded-full mr-4">
                        <div>
                            <h4 class="font-bold text-gray-800">Armand Kouassi</h4>
                            <div class="flex text-yellow-400">
                                @for ($i = 0; $i < 5; $i++)
                                    <i class="fas fa-star"></i>
                                @endfor
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 italic">"Excellent service ! Le chauffeur était très poli et ponctuel. J'ai
                        vraiment apprécié la propreté du tricycle. Je recommande vivement !"</p>
                </div>

                <div class="bg-gray-50 rounded-xl p-8 shadow-md hover:shadow-xl transition">
                    <div class="flex items-center mb-4">
                        <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode('Marie Dossou') }}"
                            alt="Marie Dossou" class="w-16 h-16 rounded-full mr-4">
                        <div>
                            <h4 class="font-bold text-gray-800">Marie Dossou</h4>
                            <div class="flex text-yellow-400">
                                @for ($i = 0; $i < 5; $i++)
                                    <i class="fas fa-star"></i>
                                @endfor
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 italic">"Plateforme super facile à utiliser ! J'ai réservé en moins de 2
                        minutes. Les prix sont vraiment compétitifs comparé à la concurrence."</p>
                </div>

                <div class="bg-gray-50 rounded-xl p-8 shadow-md hover:shadow-xl transition">
                    <div class="flex items-center mb-4">
                        <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode('Pierre Ogueni') }}"
                            alt="Pierre Ogueni" class="w-16 h-16 rounded-full mr-4">
                        <div>
                            <h4 class="font-bold text-gray-800">Pierre Ogueni</h4>
                            <div class="flex text-yellow-400">
                                @for ($i = 0; $i < 4; $i++)
                                    <i class="fas fa-star"></i>
                                @endfor
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 italic">"Transport écologique et confortable. Je suis impressionné par
                        l'engagement environnemental de cette entreprise. Bravo !"</p>
                </div>

                <div class="bg-gray-50 rounded-xl p-8 shadow-md hover:shadow-xl transition">
                    <div class="flex items-center mb-4">
                        <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode('Yuki Tanaka') }}" alt="Yuki Tanaka"
                            class="w-16 h-16 rounded-full mr-4">
                        <div>
                            <h4 class="font-bold text-gray-800">Yuki Tanaka</h4>
                            <div class="flex text-yellow-400">
                                @for ($i = 0; $i < 5; $i++)
                                    <i class="fas fa-star"></i>
                                @endfor
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 italic">"En visite touristique, le circuit proposé était fantastique ! Guide
                        très accueillant et informé. Une expérience inoubliable !"</p>
                </div>

                <div class="bg-gray-50 rounded-xl p-8 shadow-md hover:shadow-xl transition">
                    <div class="flex items-center mb-4">
                        <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode('Sophie Adèle') }}"
                            alt="Sophie Adèle" class="w-16 h-16 rounded-full mr-4">
                        <div>
                            <h4 class="font-bold text-gray-800">Sophie Adèle</h4>
                            <div class="flex text-yellow-400">
                                @for ($i = 0; $i < 5; $i++)
                                    <i class="fas fa-star"></i>
                                @endfor
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 italic">"Client régulier depuis 3 mois. Service fiable et consistent.
                        L'application est intuitive et le support client très réactif."</p>
                </div>

                <div class="bg-gray-50 rounded-xl p-8 shadow-md hover:shadow-xl transition">
                    <div class="flex items-center mb-4">
                        <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode('Ibrahim Sow') }}" alt="Ibrahim Sow"
                            class="w-16 h-16 rounded-full mr-4">
                        <div>
                            <h4 class="font-bold text-gray-800">Ibrahim Sow</h4>
                            <div class="flex text-yellow-400">
                                @for ($i = 0; $i < 5; $i++)
                                    <i class="fas fa-star"></i>
                                @endfor
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 italic">"J'utilise ce service pour mes déplacements quotidiens. Sûr, économique
                        et écologique. Meilleur choix pour se déplacer en ville !"</p>
                </div>
                {{-- @foreach ($testimonials as $testimonial)
                @endforeach --}}
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            $(function() {
                var currentStep = 1;

                function validateStep(stepNumber) {
                    const $step = $('#step' + stepNumber);
                    const $required = $step
                        .find('input[required], select[required], textarea[required]')
                        .filter(':visible');

                    let isValid = true;

                    $required.each(function() {
                        if (!this.checkValidity()) {
                            this.reportValidity();
                            isValid = false;
                            return false; // break
                        }
                    });

                    return isValid;
                }

                function setIndicator(activeStep) {
                    // Indicateurs seulement 1..3 (step4 est invisible)
                    for (let i = 1; i <= 3; i++) {
                        const $ind = $('#step' + i + '-indicator');
                        if (!$ind.length) continue;

                        if (i === activeStep) {
                            $ind.addClass('step-active')
                                .removeClass('bg-[#FFE7C1] text-gray-600 bg-gray-300');
                        } else {
                            $ind.removeClass('step-active')
                                .addClass('bg-[#FFE7C1] text-gray-600');
                        }
                    }
                }

                function showStep(step) {
                    // cacher 1..4
                    for (let i = 1; i <= 4; i++) $('#step' + i).addClass('hidden');

                    // afficher step demandé
                    $('#step' + step).removeClass('hidden');

                    currentStep = step;

                    // Indicateur : si step4 => on garde step3 actif (ou step1 si tu préfères)
                    if (step === 4) {
                        setIndicator(3);
                    } else {
                        setIndicator(step);
                    }

                    // recalcul à l’entrée de l’étape 3 (si nécessaire)
                    if (step === 3 && typeof calculatePrice === 'function') {
                        calculatePrice();
                    }
                }

                window.nextStep = function(step) {
                    if (!validateStep(currentStep)) return;
                    showStep(step);
                };

                window.prevStep = function(step) {
                    showStep(step);
                };

                window.newBooking = function() {
                    const form = document.getElementById('bookingForm');
                    form.reset();

                    // reset multi-day
                    $('#days_hidden').val(1);
                    $('#daysWrapper').addClass('hidden');
                    $('#multi_day').prop('checked', false);

                    // reset prix UI
                    $('#pricePreview').addClass('hidden');
                    $('#preview-price').text('-- FCFA');
                    $('#total-price').text('-- FCFA');

                    // reset message promo si tu l’utilises
                    $('#promo-message').html('');

                    showStep(1);
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                };

                window.selectTripType = function(e, type) {
                    $('#trip_type').val(type);

                    $('.trip-type-btn').each(function() {
                        $(this).removeClass('border-emerald-600 text-emerald-600 bg-emerald-50').addClass(
                            'border-gray-300 text-gray-600');
                    });

                    var $btn = $(e.target).closest('.trip-type-btn');
                    $btn.addClass('border-emerald-600 text-emerald-600 bg-emerald-50').removeClass(
                        'border-gray-300 text-gray-600');

                    if (type === 'normal') {
                        $('#normalTrip').removeClass('hidden');
                        $('#circuitTrip').addClass('hidden');
                    } else {
                        $('#normalTrip').addClass('hidden');
                        $('#circuitTrip').removeClass('hidden');
                    }
                };

                window.calculatePrice = function() {
                    var from = $('#from_zone_id').val();
                    var to = $('#to_zone_id').val();

                    // If from or to not selected, clear values, hide preview and return
                    if (!from || !to) {
                        $('#base-price').text('-- FCFA');
                        $('#discount').text('0 FCFA');
                        $('#total-price').text('-- FCFA');
                        if ($('#pricePreview').length) {
                            $('#pricePreview').addClass('hidden');
                            $('#preview-price').text('-- FCFA');
                        }
                        return;
                    }

                    var days = ($('#days_hidden').length ? $('#days_hidden').val() : 1) || 1;
                    var promo = $('#promo_code').val() || '';

                    var url = '/pricing/price/' + encodeURIComponent(from) + '/' + encodeURIComponent(to) +
                        '?days=' + encodeURIComponent(days);
                    if (promo) {
                        url += '&promo_code=' + encodeURIComponent(promo);
                    }

                    $.getJSON(url)
                        .done(function(data) {
                            $('#base-price').text((data.base_price).toLocaleString() + ' FCFA');
                            $('#discount').text((data.discount).toLocaleString() + ' FCFA');
                            $('#total-price').text((data.total_price).toLocaleString() + ' FCFA');

                            // Update inline preview if present
                            if ($('#pricePreview').length) {
                                $('#pricePreview').removeClass('hidden');
                                var previewText = (data.base_price).toLocaleString() + ' FCFA';
                                if (data.days && data.days > 1) {
                                    previewText = 'Base: ' + (data.base_price).toLocaleString() +
                                        ' FCFA — Total: ' + (data.total_price).toLocaleString() + ' FCFA';
                                }
                                $('#preview-price').text(previewText);
                            }
                        })
                        .fail(function(xhr) {
                            var msg = 'Erreur lors du calcul du prix';
                            if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
                                msg = xhr.responseJSON.error;
                            }
                            $('#promo-message').html('<span class="text-red-600">' + msg + '</span>');
                            $('#base-price').text('-- FCFA');
                            $('#discount').text('0 FCFA');
                            $('#total-price').text('-- FCFA');
                            if ($('#pricePreview').length) {
                                $('#pricePreview').addClass('hidden');
                                $('#preview-price').text('-- FCFA');
                            }
                        });
                };

                // Calcul automatique quand on change les selects départ/destination
                $('#from_zone_id, #to_zone_id').on('change', function() {
                    // Clear previous promo message
                    $('#promo-message').html('');
                    calculatePrice();
                });

                // Essayer au chargement de la page si les selects ont une valeur
                if ($('#from_zone_id').val() && $('#to_zone_id').val()) {
                    calculatePrice();
                }

                window.applyPromo = function() {
                    calculatePrice();
                    var promo = $('#promo_code').val();
                    if (promo) {
                        $('#promo-message').html(
                            '<span class="text-green-600"><i class="fas fa-check-circle"></i> Code promo appliqué</span>'
                        );
                    }
                };

                // Gestion réservation multi-jours
                $('#multi_day').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('#daysWrapper').removeClass('hidden');
                        // Update hidden days value with visible input value
                        $('#days_hidden').val($('#days_input').val());
                    } else {
                        $('#daysWrapper').addClass('hidden');
                        // Revert to single-day default
                        $('#days_hidden').val(1);
                    }
                    // Recalculate price whenever the option changes
                    calculatePrice();
                });

                $('#days_input').on('input', function() {
                    // Remove non-numeric characters in real-time
                    var v = $(this).val().replace(/[^0-9]/g, '');
                    $(this).val(v);

                    // Update hidden input while typing
                    if (v) {
                        $('#days_hidden').val(v);
                    }
                    calculatePrice();
                });

                // Validate on blur (when user leaves the field)
                $('#days_input').on('blur', function() {
                    var v = $(this).val().replace(/[^0-9]/g, '');
                    var num = parseInt(v, 10);

                    // If empty or less than 2, set to 2
                    if (isNaN(num) || num < 2) {
                        num = 2;
                    }

                    $(this).val(num);
                    $('#days_hidden').val(num);
                    calculatePrice();
                });

                $('a[href^="#"]').on('click', function(e) {
                    e.preventDefault();
                    var target = $($(this).attr('href'));
                    if (target.length) {
                        $('html, body').animate({
                            scrollTop: target.offset().top
                        }, 600);
                    }
                });

                // ✅ Afficher step4 automatiquement si succès (flash session)
                const hasSuccess = @json((bool) session('success'));
                if (hasSuccess) {
                    showStep(4);
                } else {
                    showStep(1);
                }
            });
        </script>
    @endpush
@endsection
