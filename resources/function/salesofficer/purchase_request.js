$(document).ready(function () {
    const table = $("#purchaseRequestTable").DataTable({
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
        ajax: "/salesofficer/purchase-requests/all",
        autoWidth: false,
        columns: [
            { data: "id", name: "id", className: "dt-left-int", width: "5%" },
            { data: "customer_name", name: "customer_name", width: "15%" },
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
        // const id = $(this).data("transaction-uuid");
        const id = $(this).data("id");
        if (!id) return;
        showB2BPR(id);
    });

    function showB2BPR(id) {
        $.get("/salesofficer/purchase-requests/" + id, function (response) {
            $(".modal-title").text("(B2B) Purchase Request Order");
            $("#prDetails").html(response.html);

            $("#sendQuotationBtn").val(id);
            $("#rejectQuotationBtn").val(id);
            $("#viewPRModal").modal("show");
            
            setTimeout(() => {
                const feeData = $("#totalFooter").attr("data-has-fee");
                const hasFee = feeData === "true";

                if (!hasFee) {
                    $("#prActions").removeClass("d-none");
                    $("#totalFooter").addClass("d-none");
                } else {
                    $("#prActions").addClass("d-none");
                    $("#totalFooter").removeClass("d-none");
                }
            }, 50);


             // ✅ Call lucide after new HTML is added
            if (typeof lucide !== "undefined") {
                lucide.createIcons();
            }
        }).fail(function () {
            toast("error", "Failed to fetch purchase request details.");
        });
    }

    let id = null;
    $(document).on("click", "#sendQuotationBtn", function (e) {
        e.preventDefault();

        id = $(this).val();
        if (!id) return;

        $(".modal-title").text("B2B Purchase Request Fee");
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
                delivery_fee: $('#feeForm input[name="delivery_fee"]').val(),
            },
            success: function (response) {
                hideLoader(".saveFee");
                toast(response.type, response.message);
                $('#feeForm input[name="delivery_fee"]').val('')
                $("#feeModal").modal("hide");
                $("#viewPRModal").modal("show");
                showB2BPR(response.prId);
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

    $(document).on("click", "#rejectQuotationBtn", function (e) {
        e.preventDefault();

        id = $(this).val();
        if (!id) return;

        $(".modal-title").text("B2B Purchase Request Rejection");
        $("#rejectModal").modal("show");

        $("#viewPRModal").modal("hide");
    });

    $(document).on("click", "#rejectFormbtn", function (e) {
        e.preventDefault();

        if (!id) {
            toast("error", "Missing purchase request ID.");
            return;
        }

        showLoader(".rejectFormbtn");
        $("#rejectFormbtn").prop("disabled", true);

        $.ajax({
            url: "/salesofficer/purchase-requests/r-q/" + id,
            method: "PUT",
            data: {
                type: $('#rejectForm [name="type"]').val(),
                rejection_reason: $('#rejectForm [name="rejection_reason"]').val(),
            },
            success: function (response) {
                hideLoader(".rejectFormbtn");
                toast(response.type, response.message);
                $('#rejectForm input[name="delivery_fee"]').val('')
                $("#rejectModal").modal("hide");
                $("#viewPRModal").modal("show");
                showB2BPR(response.prId);
                $("#rejectFormbtn").prop("disabled", false);
                table.ajax.reload();
            },
            error: function (xhr) {
                hideLoader(".rejectFormbtn");
                $("#rejectFormbtn").prop("disabled", false);
                console.error(xhr);
                toast("error", "Failed to reject quotation. Please try again.");
            },
        });
    });

    
});
