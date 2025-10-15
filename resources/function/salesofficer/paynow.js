$(document).ready(function () {
    let table = $("#paynowTable").DataTable({
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
        ajax: "/salesofficer/paynow/all",
        autoWidth: false,
        columns: [
            { data: "customer_name", name: "customer_name", width: "5%" },
            { data: "bank_name", name: "bank_name", className: "dt-left-int", width: "5%" },
            { data: "paid_amount", name: "paid_amount", className: "dt-left-int", width: "5%" },
            { data: "paid_date", name: "paid_date", className: "dt-left-int", width: "5%" },
            { data: "proof_payment", name: "proof_payment", width: "2%" },
            { data: "reference_number", name: "reference_number", className: "dt-left-int", width: "2%" },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
                width: "30%",
            },
        ],
        drawCallback: function () {
            if (typeof lucide !== "undefined") {
                lucide.createIcons();
            }
        },
    });

    $(document).on("click", "#add", function (e) {
        e.preventDefault();

        $('#manualPaymentForm')[0].reset();

        $(".modal-title").text("Cash on Delivery Manual Payment");
        $("#manualPaymentModal").modal("show");
    });

     $("#manualPayment").on("click", function (e) {
        e.preventDefault();

        showLoader(".manualPayment");

        let form = $("#manualPaymentForm")[0];
        let url = $(form).attr("action");
        let method = $(form).attr("method");

        let formData = new FormData(form);

        $("#manualPayment").prop("disabled", true);

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                hideLoader(".manualPayment");
                $("#manualPaymentForm")[0].reset();
                $("#manualPayment").prop("disabled", false);
                toast(response.type, response.message);
                $("#manualPaymentModal").modal("hide");
                table.ajax.reload();
            },
            error: function (response) {
                if (response.status === 422) {
                    hideLoader(".manualPayment");
                    $("#manualPayment").prop("disabled", false);

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


    $(document).on("click", ".approve-payment", function (e) {
        e.preventDefault();

        let id = $(this).data("id");

        if (!id) {
            toast("error", "Missing payment ID.");
            return;
        }

        Swal.fire({
            title: "Did you check the proof of payment?",
            text: "The sales officer will create a sales order after you approve this. Please review the payment carefully.",
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
            url: `/salesofficer/paynow/approve/${id}`,
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content")
            },
            beforeSend: function () {
                Swal.fire({
                    title: "Processing...",
                    text: "Please wait while we update the payment status.",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            },
            success: function (response) {
                Swal.fire({
                    icon: "success",
                    title: "Approved!",
                    text: response.message,
                });
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: xhr.responseJSON?.message || "Something went wrong.",
                });
            }
        });
    }
});
