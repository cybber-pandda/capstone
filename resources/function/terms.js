$(document).ready(function () {
    let termsId;

    const table = $("#termsTable").DataTable({
        processing: true,
        serverSide: true,
        paginationType: "simple_numbers",
        responsive: true,
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
        ajax: "/terms",
        autoWidth: false,
        columns: [
            { data: "content_type", name: "content_type", width: "20%" },
            {
                data: "content",
                name: "content",
                width: "20%",
                render: function (data, type, row) {
                    const div = document.createElement("div");
                    div.innerHTML = data;
                    const plainText = div.innerText || div.textContent || "";

                    const shortText =
                        plainText.length > 30
                            ? plainText.slice(0, 30) + "..."
                            : plainText;

                    return `
                        <span class="view-full-content text-primary" style="cursor:pointer" 
                            data-content='${_.escape(data)}' 
                            title="Click to view full content">
                            ${_.escape(shortText)} <i class="link-icon" data-lucide="eye"></i>
                        </span>
                    `;
                },
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
            if (typeof lucide !== "undefined") {
                lucide.createIcons();
            }
        },
 // REMOVE SEARCH BAR
    dom: 'lrtip' // l = length, r = processing, t = table, i = info, p = pagination
    });

    $(document).on("click", "#add", function () {
        $(".modal-title").text("Add Terms");
        $("#termsModal").modal("show");
        $("#termsForm")[0].reset();
        $("#termsForm").attr("action", `/terms`);
        $("#termsForm").attr("method", "POST");
        $("#termsForm input[name='_method']").remove();
    });

    $(document).on("click", ".edit", function () {
        $(".modal-title").text("Edit Terms & Condition");
        termsId = $(this).data("id");

        $("#termsModal").modal("show");
        $("#termsForm").attr("action", `/terms/${termsId}`);
        $("#termsForm").attr("method", "POST");
        $("#termsForm input[name='_method']").remove();
        $("#termsForm").append(
            '<input type="hidden" name="_method" value="PUT">'
        );

        $.get(`/terms/${termsId}/edit`, function (response) {
            $('#termsForm select[name="content_type"]')
                .val(response.data.content_type)
                .trigger("change");
            // $('#termsForm textarea[name="content"]').val(response.data.content);
            tinymce.get("content").setContent(response.data.content);
        });
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
                removeTerms(id);
            }
        });
    });

    $("#saveTerms").on("click", function (e) {
        e.preventDefault();
        let form = $("#termsForm")[0];
        let url = $(form).attr("action");
        let method = $(form).attr("method");
        let formData = new FormData(form);

        const content = tinymce.get("content").getContent();
        formData.append("content", content);

        $("#saveTerms").prop("disabled", true);

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                $("#termsForm")[0].reset();
                $("#saveTerms").prop("disabled", false);
                toast(response.type, response.message);
                $("#termsModal").modal("hide");
                table.ajax.reload();
            },
            error: function (response) {
                $("#saveTerms").prop("disabled", false);
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

    function removeTerms(id) {
        $.ajax({
            type: "DELETE",
            url: `/terms/${id}`,
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

    $(document).on("click", ".view-full-content", function () {
        const fullContent = _.unescape($(this).data("content"));

        $(".modal-title").text("Full Content");
        $("#termContentDetails").html(fullContent);
        $("#viewContentModal").modal("show");
    });
});
