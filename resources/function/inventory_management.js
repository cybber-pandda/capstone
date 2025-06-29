$(document).ready(function () {
    const table = $("#inventoryManagement").DataTable({
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
            search: "",
        },
        fixedHeader: { header: true },
        scrollCollapse: true,
        scrollX: true,
        scrollY: 600,
        ajax: "inventory-management",
        autoWidth: false,
        columns: [
            { data: "sku", name: "sku", width: "5%" },
            { data: "name", name: "name", width: "10%" },
            {
                data: "price",
                name: "price",
                className: "dt-left-int",
                responsivePriority: 1,
                orderable: false,
                width: "5%",
            },
            { data: "stockIn", name: "stockIn", width: "10%" },
            { data: "stockOut", name: "stockOut", width: "10%" },
            { data: "current_stock", name: "current_stock", width: "10%" },
            {
                data: "inventory_breakdown",
                name: "inventory_breakdown",
                orderable: false,
                searchable: false,
                width: "25%",
                render: function (data) {
                    return data || "<em>No inventory data</em>";
                },
            },
        ],
        drawCallback: function () {
            if (typeof lucide !== "undefined") {
                lucide.createIcons();
            }
        },
    });

    $(document).on("click", "#add", function () {
        $(".modal-title").text("Log Inventory Movement");
        $("#inventoryManagementModal").modal("show");
        $("#inventoryMangementForm")[0].reset();
    });

    $("#saveInventory").on("click", function (e) {
        e.preventDefault();

        showLoader(".saveInventory");

        let form = $("#inventoryMangementForm")[0];
        let url = $(form).attr("action");
        let method = $(form).attr("method");

        let formData = new FormData(form);

        $("#saveInventory").prop("disabled", true);

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                hideLoader(".saveInventory");
                $("#inventoryMangementForm")[0].reset();
                $("#saveInventory").prop("disabled", false);
                toast(response.type, response.message);
                $("#inventoryManagementModal").modal("hide");
                table.ajax.reload();
            },
            error: function (response) {
                if (response.status === 422) {
                    hideLoader(".saveInventory");
                    $("#saveInventory").prop("disabled", false);

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
