<!DOCTYPE html>
<html lang="fr">

@include('inc.frontend.head')

<body class="bg-gray-50">
    {{-- Global alerts (success / error / validation) --}}
    @include('inc.global.alerts')

    <!-- Navigation -->
    @include('inc.frontend.header')

    <main class="mt-20">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('inc.frontend.footer')

    @include('inc.frontend.scripts')
</body>

</html>
