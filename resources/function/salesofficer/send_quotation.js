$(document).ready(function () {
    const table = $("#sentQuotationTable").DataTable({
        processing: true,
        serverSide: true,
        paginationType: "simple_numbers",
        responsive: true,
        layout: {
            topEnd: {
                search: {
                    placeholder: "Search here",
                },
            },
        },
        aLengthMenu: [
            [5, 10, 30, 50, -1],
            [5, 10, 30, 50, "All"],
        ],
        iDisplayLength: 10,
        language: {
            search: "Search:",
        },
        fixedHeader: { header: true },
        scrollCollapse: true,
        scrollX: true,
        scrollY: 600,
        ajax: "/salesofficer/send-quotations/all",
        autoWidth: false,
        columns: [
            { data: "id", name: "id", className: "dt-left-int", width: "5%" },
            { data: "customer_name", name: "customer_name", width: "20%" },
            {
                data: "total_items",
                name: "total_items",
                className: "dt-left-int",
                orderable: false,
                searchable: false,
                width: "10%",
            },
            {
                data: "grand_total",
                name: "grand_total",
                className: "dt-left-int",
                orderable: false,
                searchable: false,
                width: "10%",
            },
            {
                data: "created_at",
                name: "created_at",
                className: "dt-left-int",
                width: "15%",
            },
            {
                data: "status",
                name: "status",
                className: "dt-left-int",
                orderable: false,
                searchable: false,
                width: "20%",
            },
        ],

        drawCallback: function () {
            if (typeof lucide !== "undefined") {
                lucide.createIcons();
            }
        },
    });
});
