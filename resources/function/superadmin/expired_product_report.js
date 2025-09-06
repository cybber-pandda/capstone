$(document).ready(function () {
    const table = $("#expiredProductReport").DataTable({
        processing: true,
        serverSide: true,
        paginationType: "simple_numbers",
        responsive: true,
        layout: {
            topEnd: {
                search: {
                    placeholder: "Search product",
                },
            },
        },
        aLengthMenu: [
            [5, 10, 30, 50, -1],
            [5, 10, 30, 50, "All"],
        ],
        iDisplayLength: 10,
        language: {
            search: "",
        },
        fixedHeader: { header: true },
        scrollCollapse: true,
        scrollX: true,
        scrollY: 600,
        ajax: "/expired-product-report",
        autoWidth: false,
        columns: [
            { data: "sku", name: "sku", width: "10%" },
            { data: "name", name: "name", width: "20%" },
            {
                data: "expiry_date",
                name: "expiry_date",
                width: "15%",
                render: function (data) {
                    if (!data) return "-";
                    const d = new Date(data);
                    return d.toLocaleDateString();
                },
            },
            {
                data: "price",
                name: "price",
                className: "dt-left-int",
                responsivePriority: 1,
                orderable: false,
                width: "10%",
            },
            { data: "stockIn", name: "stockIn", width: "10%" },
            { data: "stockOut", name: "stockOut", width: "10%" },
            { data: "current_stock", name: "current_stock", width: "10%" },
        ],
        drawCallback: function () {
            if (typeof lucide !== "undefined") {
                lucide.createIcons();
            }
        },
    });
});
