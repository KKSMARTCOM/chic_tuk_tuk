$(function (e) {
    "use strict";

    // DATATABLE 1
    $("#datatable1").DataTable({
        order: [],
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
    });

    // DATATABLE 2
    $("#datatable2").DataTable({
        order: [],
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
    });

    // SELECT2
    $(".dataTables_length select").select2({
        minimumResultsForSearch: Infinity,
    });
});
