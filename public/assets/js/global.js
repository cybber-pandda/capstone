function toast(type, message) {
    const Toast = Swal.mixin({
        toast: true,
        position: "top-right",
        showConfirmButton: false,
        timer: 3000,
        animation: false,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener("mouseenter", Swal.stopTimer);
            toast.addEventListener("mouseleave", Swal.resumeTimer);
        },
    });

    Toast.fire({
        icon: "" + type + "",
        title: "" + message + "",
    });
}

function showLoader(loaderClass) {
    $(loaderClass + "_button_text").addClass("d-none");
    $(loaderClass + "_load_data").removeClass("d-none");
}

function hideLoader(loaderClass) {
    setTimeout(function () {
        $(loaderClass + "_button_text").removeClass("d-none");
        $(loaderClass + "_load_data").addClass("d-none");

        clearValidation();
    }, 2000);
}

function clearValidation() {
    $(".form-control").removeClass("is-invalid border-danger");
    $(".invalid-feedback").text("");
}

function clearInput() {
    $(".form-control").val("");
}

function addRow(tableID) {
    var table = document.getElementById(tableID);
    var rowCount = table.rows.length;
    var row = table.insertRow(rowCount);
    var colCount = table.rows[0].cells.length;

    for (var i = 0; i < colCount; i++) {
        var newRow = row.insertCell(i);
        newRow.innerHTML = table.rows[0].cells[i].innerHTML;
        newRow.childNodes[0].value = "";

        newRow.style.textAlign = "center";
        newRow.style.verticalAlign = "middle";
    }
}

function deleteRow(row) {
    var table = document.getElementById("data");
    var rowCount = table.rows.length;
    if (rowCount > 1) {
        var rowIndex = row.parentNode.parentNode.rowIndex;
        document.getElementById("data").deleteRow(rowIndex);
    } else {
        toast("warning", "Please specify at least one value.");
    }
}

function updateCartDropdown() {
    if (!window.purchaseRequestCart || !Array.isArray(window.purchaseRequestCart.items)) return;

    let cartHtml = "";
    const items = window.purchaseRequestCart.items;

    if (items.length === 0) {
        cartHtml = `<p class="text-center p-2">Your purchase request is empty.</p>`;
    } else {
        items.forEach(function (item) {
            cartHtml += `
                <div class="product-widget">
                    <div class="product-img">
                        <img src="${item.product_image}" alt="" style="width: 50px; height: 50px; object-fit: cover;">
                    </div>
                    <div class="product-body">
                        <h3 class="product-name"><a href="#">${item.product_name}</a></h3>
                        <h4 class="product-price"><span class="qty">${item.quantity}x</span> ₱${parseFloat(item.price).toFixed(2)}</h4>
                    </div>
                    <button class="delete delete-purchase-request" style="display:none" data-id="${item.id}">
                        <i class="fa fa-close"></i>
                    </button>
                </div>
            `;
        });
    }

    $("#cart-list").html(cartHtml);
    $("#cart-total-quantity").text(`${window.purchaseRequestCart.total_quantity} Item(s) selected`);
    $("#cart-subtotal").text(`GRAND TOTAL: ₱${parseFloat(window.purchaseRequestCart.subtotal).toFixed(2)}`);
    $("#purchase-request-count")
        .text(window.purchaseRequestCart.total_quantity)
        .toggleClass("d-none", window.purchaseRequestCart.total_quantity === 0);
}

