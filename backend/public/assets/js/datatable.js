$(function (e) {
    "use strict";

    // SELECT2
    if (typeof $.fn.select2 !== "undefined") {
        $(".dataTables_length select").select2({
            minimumResultsForSearch: Infinity,
        });
    }

    const dtOptions = {
        order: [[1, "desc"]],
        columnDefs: [
            { targets: 1, visible: false, searchable: false, type: "num" },
        ],
        language: {
            processing: "Traitement en cours...",
            search: "Rechercher : ",
            lengthMenu: "Afficher _MENU_ éléments",
            info: "Affichage de _START_ à _END_ sur _TOTAL_ ",
            infoEmpty: "Affichage de 0 à 0 sur 0",
            infoFiltered: "(filtré de _MAX_ éléments au total)",
            loadingRecords: "Chargement en cours...",
            zeroRecords: "Aucun élément à afficher",
            emptyTable: "Aucune donnée disponible dans le tableau",
        },
        // Callback pour appliquer select2 après init
        initComplete: function () {
            if (typeof $.fn.select2 !== "undefined") {
                $(".dataTables_length select").select2({
                    minimumResultsForSearch: Infinity,
                });
            }
        },
    };

    if ($("#datatable1").length) $("#datatable1").DataTable(dtOptions);
    if ($("#datatable2").length) $("#datatable2").DataTable(dtOptions);
});
