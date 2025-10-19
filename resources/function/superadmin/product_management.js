$(document).ready(function () {
    let productId;
    let imagePreview = $("#imagePreviewContainer");

    const table = $("#productManagement").DataTable({
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
        ajax: "/product-management",
        autoWidth: false,
        columns: [
            // { data: "sku", name: "sku", width: "10%" },
            { data: "name", name: "name", width: "5%" },
            { data: "category", name: "category", width: "5%" },
            // {
            //     data: "created_at",
            //     name: "created_at",
            //     width: "15%",
            //     render: function (data) {
            //         return new Date(data).toLocaleString();
            //     },
            // },
            {
                data: "price",
                name: "price",
                className: "dt-left-int",
                responsivePriority: 1,
                orderable: false,
                width: "5%",
            },
            {
                data: "discount",
                name: "discount",
                className: "dt-left-int",
                responsivePriority: 1,
                orderable: false,
                width: "5%",
            },
            {
                data: "maximum_stock",
                name: "maximum_stock",
                className: "dt-left-int",
                responsivePriority: 1,
                orderable: false,
                width: "5%",
            },
            {
                data: "critical_stock_level",
                name: "critical_stock_level",
                className: "dt-left-int",
                responsivePriority: 1,
                orderable: false,
                width: "5%",
            },
            { data: "current_stock", name: "current_stock",  className: "dt-left-int", responsivePriority: 1, width: "10%", orderable: false, },
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

    $(document).on("click", "#clear-expiry", function () {
        $('#productForm input[name="expiry_date"]').val("");
    });
     

    $(document).on("click", "#add", function () {
        $(".modal-title").text("Add Product");
        $("#productModal").modal("show");
        $("#productForm")[0].reset();
        imagePreview.empty();
    });

    $(document).on("click", ".edit", function () {
        $(".modal-title").text("Edit Product");
        productId = $(this).data("id");

        $("#productModal").modal("show");
        $("#productForm").attr("action", `/product-management/${productId}`);
        $("#productForm").attr("method", "POST");
        $("#productForm").find('input[name="_method"]').remove();
        $("#productForm").append(
            '<input type="hidden" name="_method" value="PUT">'
        );

        $.get(`/product-management/${productId}/edit`, function (response) {
            $('#productForm input[name="name"]').val(response.product.name);
            $('#productForm input[name="price"]').val(response.product.price);
            $('#productForm input[name="discount"]').val(response.product.discount);
            // $('#productForm input[name="expiry_date"]').val(response.product.expiry_date);
            $('#productForm input[name="maximum_stock"]').val(response.product.maximum_stock);
            $('#productForm input[name="critical_stock_level"]').val(response.product.critical_stock_level);
            $('#productForm select[name="category_id"]').val(response.product.category_id).trigger('change');
            $('#productForm textarea[name="description"]').val(response.product.description);

            imagePreview.empty();
            response.images.forEach((img) => {
            imagePreview.append(`
                    <div class="col-md-3 text-center">
                        <img src="/${
                            img.image_path
                        }" class="img-thumbnail mb-1" style="max-width: 100%;">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="main_image_radio" value="${
                                img.id
                            }" ${img.is_main ? "checked" : ""}>
                            <label class="form-check-label">Main</label>
                        </div>
                    </div>
                `);
            });
        });
    });

    $(document).on("change", 'input[name="images[]"]', function (e) {
        const files = e.target.files;
        const previewContainer = $("#imagePreviewContainer");
        previewContainer.empty();

        Array.from(files).forEach((file, index) => {
            const reader = new FileReader();

            reader.onload = function (e) {
                const imageBlock = `
                <div class="col-md-3 text-center">
                    <img src="${
                        e.target.result
                    }" class="img-thumbnail mb-1" style="max-width: 100%;">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="main_image_radio" value="${index}" ${
                    index === 0 ? "checked" : ""
                }>
                        <label class="form-check-label">Main</label>
                    </div>
                </div>
            `;
                previewContainer.append(imageBlock);
            };

            reader.readAsDataURL(file);
        });
    });

    // Update hidden input based on selection
    $(document).on("change", 'input[name="main_image_radio"]', function () {
        $("#main_image_index").val($(this).val());
    });

    $(document).on("click", ".view-details", function () {
        $(".modal-title").text("Product Details");
        const productId = $(this).data("id");

        $.get(`/product-management/${productId}`, function (res) {
            let html = `
            <h5 class="mb-3">${res.product.name}</h5>
            <table class="table table-bordered table-sm">
                <tbody>
                    <tr>
                        <th class="w-25">SKU</th>
                        <td>${res.product.sku}</td>
                    </tr>
                    <tr>
                        <th>Price</th>
                        <td>₱${res.product.price}</td>
                    </tr>
                    <tr>
                        <th>Discounted Price</th>
                        <td>₱${res.product.discount > 0 ? res.product.discounted_price : 0}</td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>${res.product.description ?? "-"}</td>
                    </tr>
                    <tr>
                        <th>Expiry Date</th>
                        <td>${res.product.expiry_date ?? "-"}</td>
                    </tr>
                    <tr>
                        <th>Maximum Stock</th>
                        <td>${res.product.maximum_stock ?? "-"}</td>
                    </tr>
                    <tr>
                        <th>Critical Stock Level %</th>
                        <td>${res.product.critical_stock_level ?? "-"}</td>
                    </tr>
                    <tr>
                        <th>Total Stock</th>
                        <td>${res.stock}</td>
                    </tr>
                </tbody>
            </table>

            <h6 class="mt-3 mb-3">Product Images:</h6>
            <div class="row mb-3">`;

            res.images.forEach((img) => {
                html += `
                <div class="col-md-3 mb-2 text-center">
                    <img src="/${
                        img.image_path
                    }" class="img-fluid rounded border" />
                    ${
                        img.is_main
                            ? '<div class="badge bg-primary mt-1">Main</div>'
                            : ""
                    }
                </div>`;
            });

            html += `
            </div>

            <h6 class="mt-2 mb-3">Inventory Logs:</h6>
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Reason</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>`;

            res.inventories.forEach((inv) => {
                html += `
                    <tr>
                        <td>${inv.type.toUpperCase()}</td>
                        <td>${inv.quantity}</td>
                        <td>${inv.reason}</td>
                        <td>${new Date(inv.created_at).toLocaleString()}</td>
                    </tr>`;
            });

            html += `
                </tbody>
            </table>
        `;

            $("#productDetails").html(html);
            $("#viewProductModal").modal("show");
        });
    });

    $(document).on("click", ".delete", function () {
        let id = $(this).data("id");

        Swal.fire({
            title: "Are you sure?",
            text: "This product will be removed from the list. You can restore it later if needed.",
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
                    removeProduct(id);
                }, 100);
            }
        });
    });

    $("#saveProduct").on("click", function (e) {
        e.preventDefault();

        showLoader(".saveProduct");

        let form = $("#productForm")[0];
        let url = $(form).attr("action");
        let method = $(form).attr("method");

        let formData = new FormData(form);

        let mainImageId = $('input[name="main_image_radio"]:checked').val();
        if (mainImageId) {
            formData.append("main_image_id", mainImageId);
        }

        $("#saveProduct").prop("disabled", true);

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                hideLoader(".saveProduct");
                $("#productForm")[0].reset();
                $("#saveProduct").prop("disabled", false);
                toast(response.type, response.message);
                $("#productModal").modal("hide");
                table.ajax.reload();
            },
            error: function (response) {
                if (response.status === 422) {
                    hideLoader(".saveProduct");
                    $("#saveProduct").prop("disabled", false);

                    var errors = response.responseJSON.errors;
                    $.each(errors, function (key, value) {
                        $("#" + key).addClass("border-danger is-invalid");
                        $("#" + key + "_error").html(
                            "<strong>" + value[0] + "</strong>"
                        );
                    });
                } else if (response.status === 400) {
                    toast('warning', response.responseJSON.message);
                } else {
                    console.log(response);
                }
            },
        });
    });

    function removeProduct(id) {
        $.ajax({
            type: "DELETE",
            url: `/product-management/${id}`,
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
