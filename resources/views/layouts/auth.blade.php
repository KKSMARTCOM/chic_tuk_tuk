<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Authentification">

    <title>{{ config('app.name', 'Reservation') }} — @yield('title', 'Authentification')</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body class="min-h-screen bg-gray-50 flex items-center justify-center">

    <div class="w-full max-w-xl px-4">
        <div class="bg-white shadow-lg rounded-2xl overflow-hidden">
            <div class="p-6 sm:p-8">
                <a href="{{ route('home') }}" class="flex justify-center mb-6">
                    <img src="{{ asset('assets/images/png/chic_tuk_tuk_logo.png') }}" alt="Logo"
                        class="h-28 object-contain">
                </a>

                <h1 class="text-center text-2xl font-bold text-gray-800 mb-6">@yield('title', 'Inscription')</h1>

                {{-- Global alerts (session + validation) --}}
                @includeIf('inc.frontend.alerts')

                @yield('form')
            </div>
        </div>
    </div>

    @stack('scripts')
    <script>
        // Toggle password visibility and switch Font Awesome icon
        document.addEventListener('click', function(e) {
            var t = e.target;
            var btn = t.closest('.toggle-password');
            if (btn) {
                var input = document.querySelector('#password');
                if (!input) return;
                var icon = btn.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    if (icon) {
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    }
                    btn.setAttribute('aria-label', 'Masquer le mot de passe');
                } else {
                    input.type = 'password';
                    if (icon) {
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                    btn.setAttribute('aria-label', 'Afficher le mot de passe');
                }
            }
        });
    </script>
</body>

</html>
