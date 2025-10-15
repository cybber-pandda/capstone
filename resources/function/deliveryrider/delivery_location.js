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
            url: "/deliveryrider/delivery/location",
            data: function (d) {
                d.status =
                    $("#statusTabs .nav-link.active").data("status") ||
                    "pending";
            },
        },
        autoWidth: false,
        columns: [
            { data: "order_number", name: "order_number", width: "10%" },
            { data: "customer_name", name: "customer_name", width: "10%" },
            //ito bagong lagay
            { 
                data: "contact_number", 
                name: "contact_number", 
                width: "10%",
                
            },
            //hanggang dito
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
                data: null,
                name: "address",
                width: "20%",
                render: function (data, type, row) {
                    const fullAddress = row.address?.full_address || row.address || "";
                    const notes = row.address_notes || "";
                    const combined = `${fullAddress} --- ${notes}`.trim();
                    const shortText =
                        combined.length > 20 ? combined.slice(0, 20) + "..." : combined;
                    return `
                        <span class="view-full-address text-primary" 
                            style="cursor:pointer" 
                            data-address="${_.escape(combined)}">
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

    $(document).on("click", ".mark-delivered-btn", function () {
        const deliveryId = $(this).data("id");
        $("#delivery_id").val(deliveryId);
        $("#proofImage").val("");
        $("#uploadProofModal").modal("show");
        $(".modal-title").text("Proof of Delivery");
    });

    $("#uploadProofBtn").on("click", function (e) {
        e.preventDefault();

        let form = $("#uploadProofForm")[0];
        let url = $(form).attr("action");
        let method = $(form).attr("method");

        let formData = new FormData(form);

        showLoader(".uploadProofBtn");
        $("#uploadProofBtn").prop("disabled", true);

        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                toast("success", response.message);

                hideLoader(".uploadProofBtn");
                $("#uploadProofBtn").prop("disabled", false);

                $("#uploadProofModal").modal("hide");
                setTimeout(function () {
                    window.location.href = "/deliveryrider/delivery/location?status=delivered";
                }, 3000);
            },
            error: function (xhr) {
                hideLoader(".uploadProofBtn");
                $("#uploadProofBtn").prop("disabled", false);
                toast("error", xhr.responseJSON?.message || "Upload failed.");
            },
        });
    });

    $(document).on("click", ".view-proof-btn", function () {
        const imageUrl = $(this).data("proof");
        $("#proofImagePreview").attr("src", imageUrl);
        $("#viewProofModal").modal("show");
    });

    $(document).on("click", ".cancel-delivery-btn", function () {
        const deliveryId = $(this).data("id");
        $("#cancel_delivery_id").val(deliveryId);
        $("#cancel_remarks").val("");
        $(".modal-title").text("Cancellation Reason");
        $("#cancelDeliveryModal").modal("show");
    });

    $("#submitCancelDeliveryBtn").on("click", function (e) {
        e.preventDefault();

        const deliveryId = $("#cancel_delivery_id").val();
        const remarks = $("#cancel_remarks").val();

        if (!remarks.trim()) {
            toast("warning", "Please provide a reason.");
            return;
        }

        showLoader(".submitCancelDeliveryBtn");
        $("#submitCancelDeliveryBtn").prop("disabled", true);

        $.ajax({
            url: `/deliveryrider/delivery/cancel/${deliveryId}`,
            type: "POST",
            data: {
                _token: $("meta[name='csrf-token']").attr("content"),
                remarks: remarks,
            },
            success: function (res) {
                toast("success", res.message);
                $("#cancelDeliveryModal").modal("hide");
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                toast("error", xhr.responseJSON?.message || "Cancel failed.");
            },
            complete: function () {
                hideLoader(".submitCancelDeliveryBtn");
                $("#submitCancelDeliveryBtn").prop("disabled", false);
            },
        });
    });

});
