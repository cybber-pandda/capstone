$(document).ready(function () {
    function initDataTable(tableId, paymentType, columns) {
        return $("#" + tableId).DataTable({
            processing: true,
            serverSide: true,
            paginationType: "simple_numbers",
            responsive: true,
            aLengthMenu: [
                [5, 10, 30, 50, -1],
                [5, 10, 30, 50, "All"],
            ],
            iDisplayLength: 10,
            fixedHeader: { header: true },
            scrollCollapse: true,
            scrollX: true,
            scrollY: 600,
            ajax: {
                url: "/salesofficer/paylater/all",
                data: { payment_type: paymentType },
            },
            autoWidth: false,
            columns: columns,
            drawCallback: function () {
                if (typeof lucide !== "undefined") {
                    lucide.createIcons();
                }
            },
        });
    }

    // Init Straight
    let straightTable = initDataTable(
        "paylaterStraightPaymentTable",
        "straight",
        [
            { data: "customer_name", width: "5%" },
            { data: "bank_name", width: "5%" },
            { data: "paid_amount", width: "5%" },
            { data: "paid_date", width: "5%" },
            { data: "status", width: "5%" },
            { data: "proof_payment", width: "5%" },
            { data: "reference_number", width: "5%" },
            {
                data: "action",
                orderable: false,
                searchable: false,
                width: "20%",
            },
        ]
    );

    let partialTable = null;

    // On tab change
    $('button[data-bs-toggle="tab"]').on("shown.bs.tab", function (e) {
        let targetId = $(e.target).attr("data-bs-target");

        if (targetId === "#partial" && !partialTable) {
            partialTable = initDataTable(
                "paylaterPartialPaymentTable",
                "partial",
                [
                    { data: "customer_name", width: "20%" },
                    { data: "total_amount", width: "20%" },
                    { data: "due_date", width: "20%" },
                    {
                        data: "action",
                        orderable: false,
                        searchable: false,
                        width: "20%",
                    },
                ]
            );
        }
    });

    $(document).on("click", ".approve-payment", function (e) {
        e.preventDefault();

        let id = $(this).data("id");

        if (!id) {
            toast("error", "Missing payment ID.");
            return;
        }

        Swal.fire({
            title: "Did you check the proof of payment?",
            text: "The payment will be approved after you check it.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#000",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, approve it!",
            cancelButtonText: "Cancel",
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then((result) => {
            if (result.isConfirmed) {
                approvePayment(id);
            }
        });
    });

    function approvePayment(id) {
        $.ajax({
            url: `/salesofficer/paylater/approve/${id}`,
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                Swal.fire({
                    title: "Processing...",
                    text: "Please wait while we update the payment status.",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });
            },
            success: function (response) {
                

                Swal.fire({
                    icon: "success",
                    title: "Approved!",
                    timer: 3000,
                    showConfirmButton: false,
                    text: response.message,
                });

                straightTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: xhr.responseJSON?.message || "Something went wrong.",
                });
            },
        });
    }

    $(document).on("click", ".partial-payment-list", function (e) {
        e.preventDefault();

        let id = $(this).data("id");
        $(".modal-title").text("Partial Payment List");

        partialPaymentListTable(id)

        $("#viewPartialPaymentModal").modal("show");
    });

    function partialPaymentListTable(id){
          $.ajax({
            url: "/salesofficer/paylater/partial/all/" + id,
            method: "GET",
            success: function (response) {
                // Build HTML table dynamically
                let tableHtml = `
                <table class="table table-striped table-2">
                    <thead>
                        <tr>
                            <th>Amount to Pay</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Date Paid</th>
                            <th>Paid Amount</th>
                            <th>Proof of Delivery</th>
                            <th>Reference</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
            `;

                if (response.length > 0) {
                    response.forEach((item, index) => {
                        tableHtml += `
                        <tr>
                            <td data-label="Amount to Pay:">₱${parseFloat(
                                item.amount_to_pay
                            ).toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                            })}</td>
                            <td data-label="Due Date:">${item.due_date_formatted ?? "--"}</td>
                            <td data-label="Status:">
                                <span class="badge ${
                                    item.status === "pending"
                                        ? "bg-warning text-dark"
                                        : "bg-success"
                                }">
                                    ${
                                        item.status.charAt(0).toUpperCase() +
                                        item.status.slice(1)
                                    }
                                </span>
                            </td>
                            <td data-label="Date Paid:">${item.date_paid ?? "--"}</td>
                            <td data-label="Paid Amount:">${item.paid_amount ?? "--"}</td>
                            <td data-label="Proof:">
                                ${
                                    item.proof_payment
                                        ? `<a href="${item.proof_payment}" target="_blank">
                                            <img src="${item.proof_payment}" alt="Proof" style="max-height:40px;cursor:pointer;" />
                                        </a>`
                                        : "--"
                                }
                            </td>
                            <td data-label="Reference:">${item.reference_number ?? "--"}</td>
                            <td>
                                ${
                                    item.proof_payment && item.reference_number &&  item.status === 'pending'
                                        ? `<button class="btn btn-sm btn-inverse-dark approve-partial-payment p-1" data-id="${item.id}" style="font-size:11px;">Approve
                                        </button>
                                        <button class="btn btn-sm btn-inverse-dark reject-payment p-1" data-id="${item.id}" data-paymenttype="partial" style="font-size:11px;">Reject
                                        </button>`
                                        : ( item.status === 'paid' ? '' : `<span class="badge bg-danger">Waiting for B2B Payment</span>`)
                                        
                                }
                            </td>
                        </tr>
                    `;
                    });
                } else {
                    tableHtml += `<tr><td colspan="4" class="text-center">No records found</td></tr>`;
                }

                tableHtml += `</tbody></table>`;

                $("#partialPaymentTable").html(tableHtml);
            },
            error: function (xhr) {
                console.error(xhr);
                $("#partialPaymentTable").html(
                    "<p class='text-danger'>Failed to load partial payments.</p>"
                );
            },
        });
    }

    $(document).on("click", ".approve-partial-payment", function (e) {
        e.preventDefault();

        let id = $(this).data("id");

        if (!id) {
            toast("error", "Missing payment ID.");
            return;
        }

        Swal.fire({
            title: "Did you check the proof of payment?",
            text: "The payment will be approved after you check it.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#000",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, approve it!",
            cancelButtonText: "Cancel",
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then((result) => {
            if (result.isConfirmed) {
                approvePartialPayment(id);
            }
        });
    });

    function approvePartialPayment(id) {
        $.ajax({
            url: `/salesofficer/paylater/partial-payment/approve/${id}`,
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                Swal.fire({
                    title: "Processing...",
                    text: "Please wait while we update the payment status.",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });
            },
            success: function (response) {
                $('#viewPartialPaymentModal').modal('hide')
                Swal.fire({
                    icon: "success",
                    title: "Approved!",
                    timer: 3000,
                    showConfirmButton: false,
                    text: response.message,
                });
                partialTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: xhr.responseJSON?.message || "Something went wrong.",
                });
            },
        });
    }


    $(document).on("click", ".reject-payment", function (e) {
        e.preventDefault();

        let id = $(this).data("id");
        let paymentType = $(this).data("paymenttype");

        if (!id) {
            toast("error", "Missing payment ID.");
            return;
        }

        if( paymentType === 'partial') {
            $("#viewPartialPaymentModal").modal("hide");
        }

        Swal.fire({
            title: "Are you sure?",
            text: "Do you want to reject this payment?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#000",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, reject it!",
            cancelButtonText: "Cancel",
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then((result) => {
            if (result.isConfirmed) {
                $('.modal-title').text('Reject Payment');
                $('#rejectModal').modal('show');

                // attach event to Save button
                $("#rejectFormbtn").off("click").on("click", function () {
                    let reason = $('#rejectForm textarea[name="rejection_reason"]').val().trim();

                    if (!reason) {
                        toast("error", "Please enter a reason for rejection.");
                        return;
                    }

                    rejectPayment(id, paymentType, reason);
                });
            }
        });
    });

    function rejectPayment(id, paymentType, reason) {
        $.ajax({
            url: `/salesofficer/paylater/reject/payment/${id}`,
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
                reason: reason,
                paymentType: paymentType
            },
            beforeSend: function () {
                 $('#rejectModal').modal('hide');
            },
            success: function (response) {
                $('#rejectModal').modal('hide');
                Swal.fire({
                    icon: "success",
                    title: "Rejected!",
                    timer: 3000,
                    showConfirmButton: false,
                    text: response.message,
                });

                if (paymentType === 'straight') {
                    straightTable.ajax.reload(null, false);
                } else {
                    partialTable.ajax.reload(null, false);
                }
            },
            error: function (xhr) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: xhr.responseJSON?.message || "Something went wrong.",
                });
            },
        });
    }


});
