const table = $("#bankTable").DataTable({
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
    ajax: "/bank-management",
    autoWidth: false,
    columns: [
        { data: "image", name: "image", width: "15%" },
        { data: "name", name: "name", width: "20%" },
        { data: "account_number", name: "account_number", width: "20%", className: "dt-left-int", responsivePriority: 1, orderable: false },
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
        if (typeof lucide !== "undefined") {
            lucide.createIcons();
        }
    },
    initComplete: function () {
        // Add placeholder
        $('#bankTable_filter input').attr('placeholder', 'Search Bank');
    },
});


    $(document).on("click", "#add", function () {
        $(".modal-title").text("Add bank");
        $("#bankModal").modal("show");
        $("#bankForm")[0].reset();
        $("#bankForm").attr("action", `/bank-management`);
        $("#bankForm").attr("method", "POST");
        $("#bankForm input[name='_method']").remove();
        $("#image-preview").remove();
    });

    $(document).on("click", ".edit", function () {
        $(".modal-title").text("Edit bank");
        bankId = $(this).data("id");
        $("#bankModal").modal("show");
        $("#bankForm").attr("action", `/bank-management/${bankId}`);
        $("#bankForm").attr("method", "POST");
        $("#bankForm input[name='_method']").remove();
        $("#bankForm").append('<input type="hidden" name="_method" value="PUT">');

        $.get(`/bank-management/${bankId}/edit`, function (response) {
            $('#bankForm input[name="name"]').val(response.data.name);
            $('#bankForm input[name="account_number"]').val(response.data.account_number);

            const imagePath = response.data.image
                ? `/${response.data.image}`
                : '/assets/dashboard/images/noimage.png';

            $('#bankForm input[name="image"]').before(
                `<div id="image-preview"><img src="${imagePath}" class="img-thumbnail mb-2" width="80"></div>`
            );
        });
    });

    $('#bankModal').on('hidden.bs.modal', function () {
        $('#image-preview').remove();
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
        }).then((result) => {
            if (result.isConfirmed) {
                removebank(id);
            }
        });
    });

    $("#saveBank").on("click", function (e) {
        e.preventDefault();
        let form = $("#bankForm")[0];
        let url = $(form).attr("action");
        let method = $(form).attr("method");
        let formData = new FormData(form);

        $("#saveBank").prop("disabled", true);

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                $("#bankForm")[0].reset();
                $("#saveBank").prop("disabled", false);
                toast(response.type, response.message);
                $("#bankModal").modal("hide");
                table.ajax.reload();
            },
            error: function (response) {
                $("#saveBank").prop("disabled", false);
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

    function removebank(id) {
        $.ajax({
            type: "DELETE",
            url: `/bank-management/${id}`,
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

