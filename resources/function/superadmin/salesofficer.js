$(document).ready(function () {
    let salesOfficerId;

    const table = $("#salesOfficerCreation").DataTable({
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
            search: "Search: ",
        },
        fixedHeader: {
            header: true,
        },
        scrollCollapse: true,
        scrollX: true,
        scrollY: 600,
        ajax: "/salesofficer-creation",
        autoWidth: false,
        columns: [
            {
                data: "profile",
                name: "profile",
                orderable: false,
                searchable: false,
                width: "10%",
            },
            {
                data: "name",
                name: "name",
                width: "20%",
            },
            {
                data: "username",
                name: "username",
                width: "15%",
            },
            {
                data: "email",
                name: "email",
                width: "20%",
            },
            {
                data: "created_at",
                name: "created_at",
                width: "15%",
                render: function (data) {
                    return new Date(data).toLocaleString();
                },
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
            // Re-initialize Lucide icons
            if (typeof lucide !== "undefined") {
                lucide.createIcons();
            }
        },
    });

    $(document).on("click", "#add", function () {
        $(".modal-title").text("Add SalesOfficer");
        $("#salesOfficerModal").modal("show");
        $("#salesOfficerForm")[0].reset();
    });

    $(document).on("click", ".edit", function () {
        $(".modal-title").text("Edit SalesOfficer");
        salesOfficerId = $(this).data("id");

        $("#salesOfficerModal").modal("show");
        $("#salesOfficerForm").attr("action",`/salesofficer-creation/${salesOfficerId}`);
        $("#salesOfficerForm").attr("method", "POST");
        $("#salesOfficerForm").find('input[name="_method"]').remove();
        $("#salesOfficerForm").append(
            '<input type="hidden" name="_method" value="PUT">'
        );

        $.get(`/salesofficer-creation/${salesOfficerId}/edit`, function (response) {
            const fullName = response.data.name.split(" ");

            $('#salesOfficerForm input[name="firstname"]').val(fullName[0]);
            $('#salesOfficerForm input[name="lastname"]').val(fullName[1]);
            $('#salesOfficerForm input[name="username"]').val(response.data.username);
            $('#salesOfficerForm input[name="email"]').val(response.data.email);
            $('#salesOfficerForm input[name="password"]').val('');
        });
    });

    $('#togglePassword').on('click', function() {
        const passwordField = $('#password');
        const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
        passwordField.attr('type', type);
        $(this).text(type === 'password' ? 'Show Password' : 'Hide Password');
    });

    $(document).on("click", ".delete", function () {
        let id = $(this).data("id");

        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able revert this.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#000",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "Cancel",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showLoaderOnConfirm: false,
            didOpen: () => {
                $(".swal2-confirm").removeClass("swal2-loading");
            },
        }).then((result) => {
            if (result.isConfirmed) {
                // Delay to avoid loader flicker
                setTimeout(() => {
                    removeSalesOfficer(id);
                }, 100);
            }
        });
    });

    $("#saveSalesOfficer").on("click", function (e) {
        e.preventDefault();

        showLoader(".saveSalesOfficer");

        let form = $("#salesOfficerForm")[0];
        let url = $(form).attr("action");
        let method = $(form).attr("method");

        let formData = new FormData(form);

        $("#saveSalesOfficer").prop("disabled", true);

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                hideLoader(".saveAssistantSalesOfficer");
                $("#salesOfficerForm")[0].reset();
                $("#saveSalesOfficer").prop("disabled", false);
                toast(response.type, response.message);
                $("#salesOfficerModal").modal("hide");
                table.ajax.reload();
            },
            error: function (response) {
                if (response.status === 422) {
                    hideLoader(".saveSalesOfficer");
                    $("#saveSalesOfficer").prop("disabled", false);

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

    function removeSalesOfficer(id) {
        $.ajax({
            type: "DELETE",
            url: `/salesofficer-creation/${id}`,
            dataType: "json",
            beforeSend: function (xhr) {
                xhr.setRequestHeader(
                    "X-CSRF-TOKEN",
                    $('meta[name="csrf-token"]').attr("content")
                );
            },
        })
        .done(function (response) {
            toast(response.type, response.message);
            table.ajax.reload();
        })
        .fail(function (data) {
            console.log(data);
        });
    }
});
