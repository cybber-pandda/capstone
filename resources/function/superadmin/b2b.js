$(document).ready(function () {
    let B2BId;

    const table = $("#B2BCreation").DataTable({
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
        language: { search: "Search:    " },
        fixedHeader: { header: true },
        scrollCollapse: true,
        scrollX: true,
        scrollY: 600,
        ajax: "/b2b-creation",
        autoWidth: false,
        columns: [
            {
                data: "profile",
                name: "profile",
                orderable: false,
                searchable: false,
                width: "10%",
            },
            { data: "name", name: "name", width: "20%" },
            { data: "username", name: "username", width: "15%" },
            { data: "email", name: "email", width: "20%" },
            {
                data: "created_at",
                name: "created_at",
                width: "15%",
                render: (data) => new Date(data).toLocaleString(),
            },
            // Conditionally add the action column
            ...(superadmin === 1
                ? [
                      {
                          data: "action",
                          name: "action",
                          orderable: false,
                          searchable: false,
                          width: "15%",
                      },
                  ]
                : []),
        ],
        drawCallback: function () {
            if (typeof lucide !== "undefined") {
                lucide.createIcons();
            }
        },
    });

    $(document).on("click", "#add", function () {
        $(".modal-title").text("Add B2B");
        $("#B2BModal").modal("show");

        //$('#creditlimit-form').hide();

        $("#B2BForm")[0].reset();
    });

    $(document).on("click", ".edit", function () {
        $(".modal-title").text("Edit B2B");
        B2BId = $(this).data("id");

        //$('#creditlimit-form').show();

        $("#B2BModal").modal("show");
        $("#B2BForm").attr("action", `/b2b-creation/${B2BId}`);
        $("#B2BForm").attr("method", "POST");
        $("#B2BForm").find('input[name="_method"]').remove();
        $("#B2BForm").append(
            '<input type="hidden" name="_method" value="PUT">'
        );

        $.get(`/b2b-creation/${B2BId}/edit`, function (response) {
            const fullName = response.data.name.split(" ");

            // $('#B2BForm input[name="creditlimit"]').val(response.data.credit_limit);
            $('#B2BForm input[name="firstname"]').val(fullName[0]);
            $('#B2BForm input[name="lastname"]').val(fullName[1]);
            $('#B2BForm input[name="username"]').val(response.data.username);
            $('#B2BForm input[name="email"]').val(response.data.email);
            $('#B2BForm input[name="password"]').val("");
        });
    });

    $("#togglePassword").on("click", function () {
        const passwordField = $("#password");
        const type =
            passwordField.attr("type") === "password" ? "text" : "password";
        passwordField.attr("type", type);
        $(this).text(type === "password" ? "Show Password" : "Hide Password");
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
                    removeB2B(id);
                }, 100);
            }
        });
    });

    $("#saveB2B").on("click", function (e) {
        e.preventDefault();

        showLoader(".saveB2B");

        let form = $("#B2BForm")[0];
        let url = $(form).attr("action");
        let method = $(form).attr("method");

        let formData = new FormData(form);

        $("#saveB2B").prop("disabled", true);

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                hideLoader(".saveB2B");
                $("#B2BForm")[0].reset();
                $("#saveB2B").prop("disabled", false);
                toast(response.type, response.message);
                $("#B2BModal").modal("hide");
                table.ajax.reload();
            },
            error: function (response) {
                if (response.status === 422) {
                    hideLoader(".saveB2B");
                    $("#saveB2B").prop("disabled", false);

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

    function removeB2B(id) {
        $.ajax({
            type: "DELETE",
            url: `/b2b-creation/${id}`,
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
