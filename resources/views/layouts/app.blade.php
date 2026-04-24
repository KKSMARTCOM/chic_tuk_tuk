<!DOCTYPE html>
<html lang="fr">

@include('inc.backend.head')

<body class="bg-gray-100">
    <div class="h-screen">
        {{-- Global alerts (success / error / validation) --}}
        @include('inc.global.alerts')

        <div id="js-alert-container" class="fixed inset-x-0 top-22 z-50 flex flex-col items-center px-4 sm:px-6 lg:px-8">
        </div>

        <!-- Sidebar -->
        @include('inc.backend.sidebar')

        <!-- Main Content -->
        <main class="md:ml-64 h-full overflow-y-auto">
            <!-- Header -->
            @include('inc.backend.header')

            <div class="p-1 md:p-4">
                @yield('content')
            </div>
        </main>
    </div>

    @include('inc.backend.scripts')

    @stack('scripts')
</body>

</html>
