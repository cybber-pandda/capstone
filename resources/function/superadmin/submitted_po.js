$(document).ready(function () {
    const table = $("#submittedPO").DataTable({
        processing: true,
        serverSide: true,
        paginationType: "simple_numbers",
        responsive: true,
        layout: {
            topEnd: {
                search: {
                    placeholder: "Search Customer",
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
        ajax: "/submitted_po",
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

    $(document).on("click", ".view-pr", function () {
        const id = $(this).data("id");
        if (!id) return;

        $.get("/purchase-requests/" + id, function (response) {
            $(".modal-title").text("Purchase Items");
            $("#prDetails").html(response.html);
            $("#sendQuotationBtn").addClass("d-none");
            $("#viewPRModal").modal("show");
        }).fail(function () {
            toast("error", "Failed to fetch purchase request details.");
        });
    });

    $(document).on("click", ".process-so", function (e) {
        e.preventDefault();

        const id = $(this).data("id");
        if (!id) return;

        $.ajax({
            url: "/process-so/" + id,
            method: "PUT",
            success: function (response) {
                toast(response.type, response.message);
                $("#viewPRModal").modal("hide");
                table.ajax.reload();

                setTimeout(() => {
                    Swal.fire({
                        icon: "info",
                        title: "Order Ready for Delivery",
                        html: "This order is ready for delivery. Please assign a delivery personnel to deliver this item.",
                        confirmButtonText: "Assign Delivery Personnel",
                        showCancelButton: false,
                        cancelButtonText: "Cancel",
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "/delivery-personnel";
                        }
                    });
                }, 3000);
            },
            error: function (xhr) {
                console.error(xhr);

                let errorMessage = "Something went wrong. Please try again.";
                if (xhr.status === 400 || xhr.status === 500) {
                    errorMessage = xhr.responseJSON?.message || errorMessage;
                }

                toast("error", errorMessage);
            },
        });
    });
});
