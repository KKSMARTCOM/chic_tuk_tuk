<!-- JQUERY JS -->
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>

<!-- SPARKLINE JS-->
<script src="{{ asset('assets/js/jquery.sparkline.min.js') }}"></script>

<!-- Sticky js -->
<script src="{{ asset('assets/js/sticky.js') }}"></script>

<!-- CHART-CIRCLE JS-->
<script src="{{ asset('assets/js/circle-progress.min.js') }}"></script>

<!-- PIETY CHART JS-->
<script src="{{ asset('assets/plugins/peitychart/jquery.peity.min.js') }}"></script>
<script src="{{ asset('assets/plugins/peitychart/peitychart.init.js') }}"></script>

<!-- INTERNAL SELECT2 JS -->
<script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>

<!-- INTERNAL Flot JS -->
<script src="{{ asset('assets/plugins/flot/jquery.flot.js') }}"></script>
<script src="{{ asset('assets/plugins/flot/jquery.flot.fillbetween.js') }}"></script>
<script src="{{ asset('assets/plugins/flot/chart.flot.sampledata.js') }}"></script>
<script src="{{ asset('assets/plugins/flot/dashboard.sampledata.js') }}"></script>

<!-- INTERNAL CHARTJS CHART JS-->
<script src="{{ asset('assets/plugins/chart/Chart.bundle.js') }}"></script>
<script src="{{ asset('assets/plugins/chart/rounded-barchart.js') }}"></script>
<script src="{{ asset('assets/plugins/chart/utils.js') }}"></script>

<!-- INTERNAL APEXCHART JS -->
<script src="{{ asset('assets/js/apexcharts.js') }}"></script>
<script src="{{ asset('assets/plugins/apexchart/irregular-data-series.js') }}"></script>

<!-- INTERNAL Vector js -->
<script src="{{ asset('assets/plugins/jvectormap/jquery-jvectormap-2.0.2.min.js') }}"></script>
<script src="{{ asset('assets/plugins/jvectormap/jquery-jvectormap-world-mill-en.js') }}"></script>

<!-- DATATABLES JS -->
<script src="{{ asset('assets/js/select2.js') }}"></script>
<script src="{{ asset('assets/js/datatables.min.js') }}"></script>

{{-- <script src="{{ asset('assets/js/datatable.js') }}"></script> --}}
<!-- INTERNAL INDEX JS -->
<script src="{{ asset('assets/js/index1.js') }}"></script>

<script>
    /**
     * Affiche une alerte dynamique dans #js-alert-container
     * @param {'success'|'error'|'warning'|'info'} type
     * @param {string} message
     * @param {number} duration - durée en ms avant disparition (défaut 6000)
     */
    window.showAlert = function(type, message, duration = 6000) {
        const config = {
            success: {
                bg: 'bg-emerald-50',
                border: 'border-emerald-600',
                text: 'text-emerald-800',
                label: 'Succès',
            },
            error: {
                bg: 'bg-red-50',
                border: 'border-red-600',
                text: 'text-red-800',
                label: 'Erreur',
            },
            warning: {
                bg: 'bg-yellow-50',
                border: 'border-yellow-600',
                text: 'text-yellow-800',
                label: 'Attention',
            },
            info: {
                bg: 'bg-blue-50',
                border: 'border-blue-600',
                text: 'text-blue-800',
                label: 'Info',
            },
        };

        const c = config[type] ?? config.info;

        const $alert = $(`
        <div class="global-alert w-full max-w-2xl mb-4 rounded-lg ${c.bg} border-l-4 ${c.border} ${c.text} p-4 flex items-start justify-between"
             role="alert" style="display:none;">
            <div>
                <strong class="font-semibold">${c.label}</strong>
                <div class="mt-1 text-sm">${message}</div>
            </div>
            <button type="button" class="alert-close ml-4 hover:opacity-80 font-bold text-lg leading-none" aria-label="Fermer">&times;</button>
        </div>
    `);

        const $container = $('#js-alert-container');
        $container.append($alert);
        $alert.slideDown(200);

        // Fermeture manuelle
        $alert.find('.alert-close').on('click', function() {
            $alert.slideUp(200, () => $alert.remove());
        });

        // Auto-hide
        setTimeout(() => {
            $alert.slideUp(300, () => $alert.remove());
        }, duration);
    };
</script>

{{-- <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script> --}}
