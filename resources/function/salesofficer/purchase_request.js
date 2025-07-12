$(document).ready(function () {
    const table = $("#purchaseRequestTable").DataTable({
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
        ajax: "/salesofficer/purchase-requests/all",
        autoWidth: false,
        columns: [
            { data: "id", name: "id", className: "dt-left-int", width: "5%" },
            { data: "customer_name", name: "customer_name", width: "20%" },
            {
                data: "total_items",
                name: "total_items",
                className: "dt-left-int",
                responsivePriority: 1,
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
                responsivePriority: 1,
                width: "15%",
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

    $(document).on("click", ".review-pr", function () {
        const id = $(this).data("id");
        if (!id) return;

        $.get("/salesofficer/purchase-requests/" + id, function (response) {
            $(".modal-title").text("Purchase Items");
            $("#prDetails").html(response.html);
            $("#sendQuotationBtn").val(id);
            $("#viewPRModal").modal("show");
        }).fail(function () {
            toast("error", "Failed to fetch purchase request details.");
        });
    });

    let id = null;
    $(document).on("click", "#sendQuotationBtn", function (e) {
        e.preventDefault();

        id = $(this).val();
        if (!id) return;

        $(".modal-title").text("Purchase Additional Fee");
        $("#feeModal").modal("show");

        $("#viewPRModal").modal("hide");
    });

    $(document).on("click", "#saveFee", function (e) {
        e.preventDefault();

        if (!id) {
            toast("error", "Missing purchase request ID.");
            return;
        }

        showLoader(".saveFee");
        $("#saveFee").prop("disabled", true);

        $.ajax({
            url: "/salesofficer/purchase-requests/s-q/" + id,
            method: "PUT",
            data: {
                vat: $('#feeForm input[name="vat"]').val(),
                delivery_fee: $('#feeForm input[name="delivery_fee"]').val(),
            },
            success: function (response) {
                hideLoader(".saveFee");
                toast(response.type, response.message);
                $("#feeModal").modal("hide");
                $("#saveFee").prop("disabled", false);
                table.ajax.reload();
            },
            error: function (xhr) {
                hideLoader(".saveFee");
                $("#saveFee").prop("disabled", false);
                console.error(xhr);
                toast("error", "Failed to send quotation. Please try again.");
            },
        });
    });
});
