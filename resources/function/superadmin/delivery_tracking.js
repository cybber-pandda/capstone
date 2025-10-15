$(document).ready(function () {
    const table = $("#deliveryLocationTable").DataTable({
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
            url: "/delivery/location",
            data: function (d) {
                d.status =
                    $("#statusTabs .nav-link.active").data("status") ||
                    "pending";
            },
        },
        autoWidth: false,
        columns: [
            { data: "order_number", name: "order_number", width: "15%" },
            { data: "customer_name", name: "customer_name", width: "10%" },
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
                data: "address",
                name: "address",
                width: "10%",
                render: function (data, type, row) {
                    const shortText =
                        data.length > 20 ? data.slice(0, 20) + "..." : data;
                    return `
                        <span class="view-full-address text-primary" style="cursor:pointer" data-address="${_.escape(
                            data
                        )}" title="Click to view full address">
                            ${_.escape(shortText)}
                        </span>
                    `;
                },
            },
            {
                data: "action",
                name: "action",
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

    $(document).on("click", ".view-proof-btn", function () {
        const imageUrl = $(this).data("proof");
        $("#proofImagePreview").attr("src", imageUrl);
        $("#viewProofModal").modal("show");
    });
});
