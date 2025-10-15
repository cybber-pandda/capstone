$(document).ready(function () {

    const table = $("#deliveryOrdersTable").DataTable({
        processing: true,
        serverSide: true,
        paginationType: "simple_numbers",
        responsive: true,
        aLengthMenu: [
            [5, 10, 30, 50, -1],
            [5, 10, 30, 50, "All"],
        ],
        iDisplayLength: 10,
        language: { search: "Search: " , searchPlaceholder: "Search here"},
        fixedHeader: { header: true },
        scrollCollapse: true,
        scrollX: true,
        scrollY: 600,
        ajax: {
            url: "/deliveryrider/delivery/orders",
            data: function (d) {
                d.status =
                    $("#statusTabs .nav-link.active").data("status") || "pending";
            },
        },
        autoWidth: false,
        columns: [
            { data: "order_number", name: "order_number", width: "15%" },
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
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
                width: "15%",
            },
        ],
        drawCallback: function () {
            if (typeof lucide !== "undefined") {
                lucide.createIcons();
            }
        },
    });

    $("#statusTabs .nav-link").on("click", function (e) {
        e.preventDefault();
        $("#statusTabs .nav-link").removeClass("active");
        $(this).addClass("active");

        const newStatus = $(this).data("status");
        const newUrl = new URL(window.location.href);
        newUrl.searchParams.set("status", newStatus);
        history.replaceState(null, "", newUrl.toString());

        table.ajax.reload();
    });

    $(document).on("click", ".view-items-btn", function () {
        const orderId = $(this).data("id");

        $(".modal-title").text("Order Items");
        $("#viewItemsList").html('<div class="text-center my-4">Loading...</div>');

        $.get(`/deliveryrider/delivery/orders/${orderId}/items`, function (response) {
            $("#viewItemsList").html(response.html);
            $("#viewOrderItemsModal").modal("show");
        }).fail(function () {
            $("#viewItemsList").html('<div class="text-danger">Failed to load items.</div>');
        });
    });
});
