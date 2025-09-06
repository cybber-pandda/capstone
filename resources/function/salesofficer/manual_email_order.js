$(document).ready(function () {
    const table = $("#manualEmailOrderTable").DataTable({
        processing: true,
        serverSide: true,
        paginationType: "simple_numbers",
        responsive: true,
        layout: {
            topEnd: {
                search: { placeholder: "Search here" },
            },
        },
        aLengthMenu: [
            [5, 10, 30, 50, -1],
            [5, 10, 30, 50, "All"],
        ],
        iDisplayLength: 10,
        language: { search: "" },
        fixedHeader: { header: true },
        scrollCollapse: true,
        scrollX: true,
        scrollY: 600,
        ajax: "/salesofficer/email-manual-order",
        autoWidth: false,
        columns: [
            { data: "customer_name", name: "customer_name", width: "10%" },
            { data: "customer_type", name: "customer_type", width: "5%" },
            {
                data: "customer_address",
                name: "customer_address",
                width: "15%",
            },
            {
                data: "phone_number",
                name: "phone_number",
                className: "dt-left-int",
                responsivePriority: 1,
                width: "10%",
            },
            {
                data: "total_items",
                name: "total_items",
                className: "dt-left-int",
                responsivePriority: 1,
                width: "5%",
            },
            { data: "grand_total", name: "grand_total", width: "5%" },
            // { data: "created_at", name: "created_at",   className: "dt-left-int", responsivePriority: 1, width: "15%" },
            { data: "status", name: "status", width: "10%" },
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

    $(document).on("click", ".approve-order", function () {
        let id = $(this).data("id");

        Swal.fire({
            title: "Approve this order?",
            text: "Once approved, the customer will receive their receipt via email.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, approve it!",
        }).then((result) => {
            if (result.isConfirmed) {
                // Show waiting/processing message
                Swal.fire({
                    title: "Processing...",
                    text: "Sending receipt email to customer. Please wait.",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.post(
                    "/salesofficer/manual-email-order/approve",
                    {
                        id: id,
                        type: 'approve'
                    },
                    function (response) {
                        Swal.fire("Approved!", response.message, "success");
                        $("#manualEmailOrderTable").DataTable().ajax.reload();
                    }
                ).fail(function () {
                    Swal.fire("Error", "Something went wrong.", "error");
                });
            }
        });
    });

    $(document).on("click", ".reject-order", function () {
        let id = $(this).data("id");

        Swal.fire({
            title: "Reject this order?",
            text: "Once rejected, the customer will receive notification via email.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, reject it!",
        }).then((result) => {
            if (result.isConfirmed) {
                // Show waiting/processing message
                Swal.fire({
                    title: "Processing...",
                    text: "Sending reject email to customer. Please wait.",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.post(
                    "/salesofficer/manual-email-order/approve",
                    {
                        id: id,
                        type: 'reject'
                    },
                    function (response) {
                        Swal.fire("Rejected!", response.message, "success");
                        $("#manualEmailOrderTable").DataTable().ajax.reload();
                    }
                ).fail(function () {
                    Swal.fire("Error", "Something went wrong.", "error");
                });
            }
        });
    });

    // Open View Products Modal
    $(document).on("click", ".view-products", function () {
        $(".modal-title").text("Purchase Request Items");

        let products = JSON.parse($(this).attr("data-products"));

        let totalQty = 0;
        let grandTotal = 0;

        const VAT_RATE = 0.12; // 12%
        const DELIVERY_FEE = 200; // Default for Quezon Province

        let html = `
        <table class="table table-bordered table-hover mb-5">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
    `;

        products.forEach((p) => {
            const qty = parseInt(p.qty, 10) || 0;
            const price = parseFloat(p.price) || 0;
            const subtotal = qty * price;

            totalQty += qty;
            grandTotal += subtotal;

            html += `
            <tr>
                <td>${p.category}</td>
                <td>${p.product}</td>
                <td class="text-end">${qty}</td>
                <td class="text-end">₱${subtotal.toFixed(2)}</td>
            </tr>
        `;
        });

        // Compute VAT and Delivery Fee
        const vatAmount = grandTotal * VAT_RATE;
        const totalWithVat = grandTotal + vatAmount;
        const finalTotal = totalWithVat + DELIVERY_FEE;

        html += `
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2" class="text-end">Total Qty</th>
                    <th class="text-end">${totalQty}</th>
                    <th class="text-end">₱${grandTotal.toFixed(2)}</th>
                </tr>
                <tr>
                    <th colspan="3" class="text-end">VAT (12%)</th>
                    <th class="text-end">₱${vatAmount.toFixed(2)}</th>
                </tr>
                <tr>
                    <th colspan="3" class="text-end">Delivery Fee <br> <i style="font-size:12px;">Quezon Province<i></th>
                    <th class="text-end">₱${DELIVERY_FEE.toFixed(2)}</th>
                </tr>
                <tr>
                    <th colspan="3" class="text-end">Grand Total (Incl. VAT + Delivery)</th>
                    <th class="text-end">₱${finalTotal.toFixed(2)}</th>
                </tr>
            </tfoot>
        </table>
    `;

        $("#productDetails").html(html);
        $("#viewProductModal").modal("show");
    });

    $(document).on("click", "#add", function () {
        $(".modal-title").text("Manual Email Order");
        $("#sendEMailOrderModal").modal("show");
        $("#sendEMailOrderModalForm")[0].reset();
    });

    $("#manulEmailOrderFormbtn").on("click", function (e) {
        e.preventDefault();

        showLoader(".manulEmailOrderFormbtn");

        let form = $("#sendEMailOrderModalForm")[0];
        let url = $(form).attr("action");
        let method = $(form).attr("method");
        let formData = new FormData(form);

        $("#manulEmailOrderFormbtn").prop("disabled", true);

        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                hideLoader(".manulEmailOrderFormbtn");
                $("#sendEMailOrderModalForm")[0].reset();
                $("#manulEmailOrderFormbtn").prop("disabled", false);
                toast("success", response.message);
                $("#sendEMailOrderModal").modal("hide");
                table.ajax.reload();
            },
            error: function (response) {
                hideLoader(".manulEmailOrderFormbtn");
                $("#manulEmailOrderFormbtn").prop("disabled", false);
                if (response.status === 422) {
                    let errors = response.responseJSON.errors;
                    $.each(errors, function (key, value) {
                        $("#" + key).addClass("border-danger is-invalid");
                        $("#" + key + "_error").html(
                            "<strong>" + value[0] + "</strong>"
                        );
                    });
                }
            },
        });
    });
});
