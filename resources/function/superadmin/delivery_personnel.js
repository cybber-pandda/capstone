$(document).ready(function () {
    let deliveryId;
    let prId;

    const table = $("#deliveryPersonnelTable").DataTable({
        processing: true,
        serverSide: true,
        paginationType: "simple_numbers",
        responsive: true,
        ajax: "/delivery-personnel",
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
        autoWidth: false,
        columns: [
            { data: "customer_name", name: "customer_name", width: "15%" },
            {
                data: "customer_address",
                name: "customer_address",
                width: "15%",
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
            { data: "delivery_man", name: "delivery_man", width: "15%" },
            { data: "order_number", name: "order_number", width: "10%" },
            {
                data: "total_amount",
                name: "total_amount",
                className: "dt-left-int",
                width: "10%",
            },
            {
                data: "total_items",
                name: "total_items",
                className: "dt-left-int",
                orderable: false,
                searchable: false,
                width: "5%",
            },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
                width: "10%",
            },
        ],
        drawCallback: function () {
            if (typeof lucide !== "undefined") {
                lucide.createIcons();
            }
        },
    });

    $(document).on("click", ".assign-delivery", function () {
        const deliverydataId = $(this).data("id");
        const orderNumber = $(this).data("order-number");

        

        if (!deliverydataId || !orderNumber) return;

        const extractPRId = orderNumber.replace("REF ", "").split("-")[0];

        deliveryId = deliverydataId;
        prId = extractPRId;

        $(".modal-title").text("Assign Delivery");
        $("#assignDeliveryModal").modal("show");
    });

    $("#assignDeliverySubmit").on("click", function (e) {
        e.preventDefault();

        showLoader(".assignDeliverySubmit");

        let form = $("#assignDeliveryForm")[0];
        let url = $(form).attr("action");
        let method = $(form).attr("method");

        let formData = new FormData(form);

        let deliveryRiderId = $('select[name="delivery_rider_id"]').val();
        if (deliveryRiderId) {
            formData.append("delivery_rider_id", deliveryRiderId);
        }

        formData.append("delivery_id", deliveryId);
        formData.append("pr_id", prId);

        $("#assignDeliverySubmit").prop("disabled", true);

        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false, // Important!
            contentType: false, // Important!
            success: function (response) {
                hideLoader(".assignDeliverySubmit");
                $("#assignDeliveryForm")[0].reset();
                $("#assignDeliverySubmit").prop("disabled", false);
                toast(response.type, response.message);
                $("#assignDeliveryModal").modal("hide");
                table.ajax.reload();
            },
            error: function (response) {
                if (response.status === 422) {
                    hideLoader(".assignDeliverySubmit");
                    $("#assignDeliverySubmit").prop("disabled", false);

                    var errors = response.responseJSON.errors;
                    $.each(errors, function (key, value) {
                        $("#" + key).addClass("border-danger is-invalid");
                        $("#" + key + "_error").html(
                            "<strong>" + value[0] + "</strong>"
                        );
                    });
                } else if (response.status === 400) {
                    console.log(response.responseJSON.message);
                } else {
                    console.log(response);
                }
            },
        });
    });
});
