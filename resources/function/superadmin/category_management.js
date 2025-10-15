$(document).ready(function () {
    let categoryId;

    const table = $("#categoryTable").DataTable({
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
        ajax: "/category-management",
        autoWidth: false,
        columns: [
            //{ data: "image", name: "image", width: "15%" }, tanggalin ari
            { data: "name", name: "name", width: "20%" },
            { data: "description", name: "description", width: "20%" },
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
    });

    $(document).on("click", "#add", function () {
        $(".modal-title").text("Add Category");
        $("#categoryModal").modal("show");
        $("#categoryForm")[0].reset();
        $("#categoryForm").attr("action", `/category-management`);
        $("#categoryForm").attr("method", "POST");
        $("#categoryForm input[name='_method']").remove();
        $("#image-preview").remove();
    });

    $(document).on("click", ".edit", function () {
        $(".modal-title").text("Edit Category");
        categoryId = $(this).data("id");
        $("#categoryModal").modal("show");
        $("#categoryForm").attr("action", `/category-management/${categoryId}`);
        $("#categoryForm").attr("method", "POST");
        $("#categoryForm input[name='_method']").remove();
        $("#categoryForm").append('<input type="hidden" name="_method" value="PUT">');

        $.get(`/category-management/${categoryId}/edit`, function (response) {
            $('#categoryForm input[name="name"]').val(response.data.name);
            $('#categoryForm textarea[name="description"]').val(response.data.description);

            const imagePath = response.data.image
                ? `/${response.data.image}`
                : '/assets/dashboard/images/noimage.png';

            $('#categoryForm input[name="image"]').before(
                `<div id="image-preview"><img src="${imagePath}" class="img-thumbnail mb-2" width="80"></div>`
            );
        });
    });

    $('#categoryModal').on('hidden.bs.modal', function () {
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
                removeCategory(id);
            }
        });
    });

    $("#saveCategory").on("click", function (e) {
        e.preventDefault();
        let form = $("#categoryForm")[0];
        let url = $(form).attr("action");
        let method = $(form).attr("method");
        let formData = new FormData(form);

        $("#saveCategory").prop("disabled", true);

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                $("#categoryForm")[0].reset();
                $("#saveCategory").prop("disabled", false);
                toast(response.type, response.message);
                $("#categoryModal").modal("hide");
                table.ajax.reload();
            },
            error: function (response) {
                $("#saveCategory").prop("disabled", false);
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

    function removeCategory(id) {
        $.ajax({
            type: "DELETE",
            url: `/category-management/${id}`,
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
