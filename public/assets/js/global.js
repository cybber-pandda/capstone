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
    if (
        !window.purchaseRequestCart ||
        !Array.isArray(window.purchaseRequestCart.items)
    )
        return;

    let cartHtml = "";
    const items = window.purchaseRequestCart.items;

    if (items.length === 0) {
        cartHtml = `<p class="text-center p-2">Your purchase request is empty.</p>`;
    } else {
        items.forEach(function (item) {
            cartHtml += `
                <div class="product-widget">
                    <div class="product-img">
                        <img src="${item.product_image
                }" alt="" style="width: 50px; height: 50px; object-fit: cover;">
                    </div>
                    <div class="product-body">
                        <h3 class="product-name"><a href="#">${item.product_name
                }</a></h3>
                        <h4 class="product-price"><span class="qty">${item.quantity
                }x</span> ₱${parseFloat(item.price).toFixed(2)}</h4>
                    </div>
                    <button class="delete delete-purchase-request" style="display:none" data-id="${item.id
                }">
                        <i class="fa fa-close"></i>
                    </button>
                </div>
            `;
        });
    }

    $("#cart-list").html(cartHtml);
    $("#cart-total-quantity").text(
        `${window.purchaseRequestCart.total_quantity} Item(s) selected`
    );
    $("#cart-subtotal").text(
        `GRAND TOTAL: ₱${parseFloat(
            window.purchaseRequestCart.subtotal
        ).toFixed(2)}`
    );
    $("#purchase-request-count")
        .text(window.purchaseRequestCart.total_quantity)
        .toggleClass("d-none", window.purchaseRequestCart.total_quantity === 0);
}

$(document).on("click", ".view-full-address", function () {
    const fullAddress = $(this).data("address");

    $(".modal-title").text("Full Delivery Address");
    $("#addressDetails").text(`${fullAddress}`);

    $("#viewAddressModal").modal("show");
});

function getStatusFromURL() {
    const params = new URLSearchParams(window.location.search);
    return params.get("status") || "pending";
}

function activateStatusTab(status) {
    const $tab = $('#statusTabs .nav-link[data-status="' + status + '"]');

    if ($tab.length) {
        $("#statusTabs .nav-link").removeClass("active");
        $tab.addClass("active");
    }
}

const selectedStatus = getStatusFromURL();
activateStatusTab(selectedStatus); // Set tab active on page load

function refreshRecentMessages() {
    $.get("/recent-messages", function (response) {
        const messages = response.messages;
        const currentUserId = response.current_user_id;
        renderRecentMessagesDropdown(messages, currentUserId);
    });
}

function renderRecentMessagesDropdown(messages, currentUserId) {
    const $container = $("#recentMessagesList");
    const $count = $("#messageCount");
    const $indicator = $("#messageIndicator");

    $container.empty();

    if (!messages.length) {
        $container.append(
            `<div class="dropdown-item py-2 text-center text-muted">No new messages</div>`
        );
        $count.text("0 New Messages");
        $indicator.addClass("d-none");
        return;
    }

    let hasUnread = false;

    messages.forEach((msg) => {
        const isUnread = msg.recipient_id === currentUserId;
        if (isUnread) hasUnread = true;

        const profile = msg.sender?.profile
            ? msg.sender.profile
            : `/assets/avatars/${Math.floor(Math.random() * 17 + 1)}.avif`;

        const name = msg.sender?.name ?? "Unknown";
        const text = msg.text?.substring(0, 30) ?? "";
        const time = dayjs(msg.created_at).fromNow();

        const html = `
            <a href="/chat" class="dropdown-item d-flex align-items-center py-2">
                <div class="me-3">
                    <img class="w-30px h-30px rounded-circle" src="${profile}" alt="user">
                </div>
                <div class="d-flex justify-content-between flex-grow-1">
                    <div class="me-4">
                        <p class="mb-0">${name}</p>
                        <p class="fs-12px text-secondary mb-0">${text}</p>
                    </div>
                    <p class="fs-12px text-secondary mb-0">${time}</p>
                </div>
            </a>
        `;
        $container.append(html);
    });

    $count.text(
        `${messages.length} New Message${messages.length !== 1 ? "s" : ""}`
    );
    if (hasUnread) {
        $indicator.removeClass("d-none");
    } else {
        $indicator.addClass("d-none");
    }
}

function fetchNotifications() {
    $.get("/notifications/api", function (res) {
        const $list = $("#notificationItems");
        const $count = $("#notificationCount");
        const $indicator = $("#notificationIndicator");

        $list.empty();

        if (res.notifications.length === 0) {
            $list.append(
                `<div class="text-center py-2 text-muted">No notifications</div>`
            );
            $count.text("0 New Notifications");
            $indicator.hide();
        } else {
            res.notifications.forEach((noti) => {
                $list.append(`
                    <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2">
                        <div class="w-30px h-30px d-flex align-items-center justify-content-center bg-primary rounded-circle me-3">
                            <i class="icon-sm text-white" data-lucide="bell"></i>
                        </div>
                        <div class="flex-grow-1 me-2">
                            <p>${noti.message}</p>
                            <p class="fs-12px text-secondary">${noti.time}</p>
                        </div>
                    </a>
                `);
            });

            $count.text(
                `${res.count} New Notification${res.count > 1 ? "s" : ""}`
            );
            $indicator.show();
        }

        if (typeof lucide !== "undefined") lucide.createIcons();
    });
}

function markAllNotificationsRead() {
    $.post(
        "/notifications/mark-all-read",
        {
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        function () {
            fetchNotifications();
        }
    );
}

function getProfileDetails(userId) {
    $.ajax({
        url: "/user-profile/settings",
        type: "GET",
        data: { id: userId },
        success: function (data) {
            $(".profile-name").text(data.name);
            $(".profile-username").text(data.username);
            $(".profile-email").text(data.email);
            $(".profile-about").text(data.about);
            $(".profile-joined").text(data.joined);

            let profileSrc = 'assets/dashboard/images/noprofile.png'; // default

            if (data.profile_image) {
                // if the profile_image is already an absolute URL, use it as-is
                if (data.profile_image.startsWith('http://') || data.profile_image.startsWith('https://')) {
                    profileSrc = data.profile_image;
                } else {
                    // prepend assetUrl only if exists
                    profileSrc = (window.assetUrl ? window.assetUrl : '') + data.profile_image;
                }
            }

            $(".profile-image").attr("src", profileSrc);
        },
        error: function (xhr) {
            console.error("Profile not found");
        },
    });
}


$(document).on("click", "#bank_id", function () {
    const selected = $(this).find(":selected");
    const account = selected.data("account");
    const qrCode = selected.data("qr");

    if (account && qrCode) {
        $("#bankDetails").removeClass("d-none");
        $("#accountNumber").text(account);
        $("#qrCodeImage").attr("src", qrCode);
    } else {
        $("#bankDetails").addClass("d-none");
        $("#accountNumber").text("");
        $("#qrCodeImage").attr("src", "");
    }
});

$(document).on("click", ".toggle-password", function () {
    var $targetInput = $($(this).data("target"));
    var $icon = $(this).find("i");

    if ($targetInput.attr("type") === "password") {
        $targetInput.attr("type", "text");
        if ($(this).is("button")) {
            $(this).text("Hide");
        } else {
            $icon.removeClass("fa-eye").addClass("fa-eye-slash");
        }
    } else {
        $targetInput.attr("type", "password");
        if ($(this).is("button")) {
            $(this).text("Show");
        } else {
            $icon.removeClass("fa-eye-slash").addClass("fa-eye");
        }
    }
});
