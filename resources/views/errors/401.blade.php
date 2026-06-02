<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} — 401 Non autorisé</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
</head>

<body class="min-h-screen bg-gray-50 flex items-center justify-center px-4">

    <div class="w-full max-w-2xl">
        <div class="bg-white shadow-lg rounded-2xl overflow-hidden">

            <div class="h-2 bg-[#286b41]"></div>

            <div class="p-8 sm:p-10 text-center">

                <a href="{{ url('/') }}" class="flex justify-center mb-8">
                    <img src="{{ asset('assets/images/png/chic_tuk_tuk_logo.png') }}" alt="Logo"
                        class="h-28 object-contain">
                </a>

                <div class="flex justify-center mb-6">
                    <div
                        class="w-20 h-20 rounded-full bg-amber-50 border-2 border-amber-200 flex items-center justify-center">
                        <i class="fas fa-lock text-3xl text-amber-500"></i>
                    </div>
                </div>

                <p class="text-8xl font-extrabold text-gray-100 leading-none select-none mb-2">401</p>

                <h1 class="text-2xl font-bold text-gray-800 mb-3">Accès non autorisé</h1>
                <p class="text-gray-500 text-sm leading-relaxed mb-8">
                    Vous devez être connecté pour accéder à cette page.<br>
                    Identifiez-vous pour continuer.
                </p>

                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-[#286b41] hover:opacity-90 text-white text-sm font-semibold rounded-lg transition">
                        <i class="fas fa-sign-in-alt"></i>
                        Se connecter
                    </a>
                    <a href="{{ url('/') }}"
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 border border-gray-200 text-gray-600 hover:border-gray-300 hover:text-gray-800 text-sm rounded-lg transition">
                        <i class="fas fa-home"></i>
                        Accueil
                    </a>
                </div>

            </div>

            <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-400">
                    Si vous pensez qu'il s'agit d'une erreur, contactez
                    <a href="mailto:kokamobilitysarl@gmail.com" class="text-[#286b41] hover:underline">le support</a>.
                </p>
            </div>

        </div>
    </div>

</body>

</html>
