<div class="fixed inset-x-0 top-22 z-50 flex justify-center px-4 sm:px-6 lg:px-8">
    {{-- Success / Error / Info / Warning messages --}}

    @if (session('success'))
        <div class="global-alert w-full max-w-2xl mb-4 rounded-lg bg-emerald-50 border-l-4 border-emerald-600 text-emerald-800 p-4 flex items-start justify-between"
            role="alert" aria-live="polite">
            <div>
                <strong class="font-semibold">Succès</strong>
                <div class="mt-1 text-sm">{{ session('success') }}</div>
            </div>
            <button type="button" class="alert-close ml-4 text-emerald-800 hover:opacity-80"
                aria-label="Fermer">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div class="global-alert w-full max-w-2xl mb-4 rounded-lg bg-red-50 border-l-4 border-red-600 text-red-800 p-4 flex items-start justify-between"
            role="alert" aria-live="assertive">
            <div>
                <strong class="font-semibold">Erreur</strong>
                <div class="mt-1 text-sm">{{ session('error') }}</div>
            </div>
            <button type="button" class="alert-close ml-4 text-red-800 hover:opacity-80"
                aria-label="Fermer">&times;</button>
        </div>
    @endif

    @if (session('warning'))
        <div class="global-alert w-full max-w-2xl mb-4 rounded-lg bg-yellow-50 border-l-4 border-yellow-600 text-yellow-800 p-4 flex items-start justify-between"
            role="alert" aria-live="polite">
            <div>
                <strong class="font-semibold">Attention</strong>
                <div class="mt-1 text-sm">{{ session('warning') }}</div>
            </div>
            <button type="button" class="alert-close ml-4 text-yellow-800 hover:opacity-80"
                aria-label="Fermer">&times;</button>
        </div>
    @endif

    @if (session('info'))
        <div class="global-alert w-full max-w-2xl mb-4 rounded-lg bg-blue-50 border-l-4 border-blue-600 text-blue-800 p-4 flex items-start justify-between"
            role="alert" aria-live="polite">
            <div>
                <strong class="font-semibold">Info</strong>
                <div class="mt-1 text-sm">{{ session('info') }}</div>
            </div>
            <button type="button" class="alert-close ml-4 text-blue-800 hover:opacity-80"
                aria-label="Fermer">&times;</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="global-alert w-full max-w-2xl mb-4 rounded-lg bg-red-50 border-l-4 border-red-600 text-red-800 p-4"
            role="alert" aria-live="assertive">
            <div class="flex items-start justify-between">
                <div>
                    <strong class="font-semibold">Formulaire invalide</strong>
                    <ul class="mt-2 text-sm list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="alert-close ml-4 text-red-800 hover:opacity-80"
                    aria-label="Fermer">&times;</button>
            </div>
        </div>
    @endif
</div>

@once
    @push('scripts')
        <script>
            (function($) {
                $(function() {
                    // Auto-hide after 6 seconds
                    setTimeout(function() {
                        $('.global-alert').slideUp(300, function() {
                            $(this).remove();
                        });
                    }, 6000);

                    // Manual close
                    $(document).on('click', '.alert-close', function() {
                        $(this).closest('.global-alert').slideUp(200, function() {
                            $(this).remove();
                        });
                    });
                });
            })
            (jQuery);
        </script>
    @endpush
@endonce
