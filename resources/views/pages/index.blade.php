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
                            tricycles chic. Rapide, confortable et écologique.</p>
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
                                    class="w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold mb-2">
                                    2</div>
                                <span class="text-xs text-gray-600">Date & Heure</span>
                            </div>
                            <div class="flex-1 flex items-center justify-center" style="margin-bottom: 26px;">
                                <div class="h-1 bg-gray-300 w-full"></div>
                            </div>
                            <div class="flex flex-col items-center flex-1">
                                <div id="step3-indicator"
                                    class="w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold mb-2">
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
                                        <input type="number" id="days_input" min="2" value="2"
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
                        class="w-20 h-20 gradient-bg rounded-full flex items-center justify-center text-white text-3xl font-bold mx-auto mb-6">
                        1</div>
                    <i class="fas fa-mobile-alt text-5xl text-[#286b41] mb-4"></i>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Réservez en ligne</h3>
                    <p class="text-gray-600">Remplissez le formulaire avec vos informations de trajet. C'est simple et
                        rapide !</p>
                </div>

                <div class="text-center">
                    <div
                        class="w-20 h-20 gradient-bg rounded-full flex items-center justify-center text-white text-3xl font-bold mx-auto mb-6">
                        2</div>
                    <i class="fas fa-user-check text-5xl text-[#286b41] mb-4"></i>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Confirmation instantanée</h3>
                    <p class="text-gray-600">Un conducteur accepte votre réservation et vous recevez une confirmation
                        immédiate.</p>
                </div>

                <div class="text-center">
                    <div
                        class="w-20 h-20 gradient-bg rounded-full flex items-center justify-center text-white text-3xl font-bold mx-auto mb-6">
                        3</div>
                    <i class="fas fa-route text-5xl text-[#286b41] mb-4"></i>
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

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-2xl transition transform hover:-translate-y-2">
                    <div class="w-16 h-16 gradient-bg rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-shield-alt text-3xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Sécurité garantie</h3>
                    <p class="text-gray-600">Tous nos conducteurs sont formés et vérifiés pour votre sécurité.</p>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-2xl transition transform hover:-translate-y-2">
                    <div class="w-16 h-16 gradient-bg rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-clock text-3xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Ponctualité</h3>
                    <p class="text-gray-600">Arrivée à l'heure garantie ou remboursement de votre trajet.</p>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-2xl transition transform hover:-translate-y-2">
                    <div class="w-16 h-16 gradient-bg rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-leaf text-3xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Écologique</h3>
                    <p class="text-gray-600">Réduisez votre empreinte carbone avec nos véhicules écologiques.</p>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-2xl transition transform hover:-translate-y-2">
                    <div class="w-16 h-16 gradient-bg rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-dollar-sign text-3xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Prix compétitifs</h3>
                    <p class="text-gray-600">Les meilleurs tarifs du marché sans compromis sur la qualité.</p>
                </div>
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

                window.nextStep = function(step) {
                    $('#step' + currentStep).addClass('hidden');
                    $('#step' + currentStep + '-indicator').removeClass('step-active').addClass(
                        'bg-gray-300 text-gray-600');

                    currentStep = step;

                    $('#step' + currentStep).removeClass('hidden');
                    $('#step' + currentStep + '-indicator').addClass('step-active').removeClass(
                        'bg-gray-300 text-gray-600');

                    if (currentStep === 3) {
                        calculatePrice();
                    }
                };

                window.prevStep = function(step) {
                    $('#step' + currentStep).addClass('hidden');
                    $('#step' + currentStep + '-indicator').removeClass('step-active').addClass(
                        'bg-gray-300 text-gray-600');

                    currentStep = step;

                    $('#step' + currentStep).removeClass('hidden');
                    $('#step' + currentStep + '-indicator').addClass('step-active').removeClass(
                        'bg-gray-300 text-gray-600');
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
                    var v = parseInt($(this).val(), 10);
                    if (isNaN(v) || v < 2) {
                        v = 2;
                        $(this).val(v);
                    }
                    $('#days_hidden').val(v);
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
            });
        </script>
    @endpush
@endsection
