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
                    <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode('Marie Dossou') }}" alt="Marie Dossou"
                        class="w-16 h-16 rounded-full mr-4">
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
                    <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode('Sophie Adèle') }}" alt="Sophie Adèle"
                        class="w-16 h-16 rounded-full mr-4">
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
