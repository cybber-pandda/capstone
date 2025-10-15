$(document).ready(function () {
    const table = $("#userManagementTable").DataTable({
        processing: true,
        serverSide: true,
        paginationType: "simple_numbers",
        responsive: true,
        scrollCollapse: true,
        scrollX: true,
        scrollY: 600,
        autoWidth: false,
        ajax: {
            url: "/user-management",
            data: function (d) {
                d.status = $("#statusTabs .nav-link.active").data("status");
            },
        },
        columns: [
            {
                data: "profile",
                name: "profile",
                orderable: false,
                searchable: false,
                width: "5%",
            },
            { data: "name", name: "name", width: "10%" },
            { data: "username", name: "username", width: "10%" },
            { data: "email", name: "email", width: "10%" },
            { data: "role", name: "role", width: "10%" },
            {
                data: "created_at",
                name: "created_at",
                width: "10%",
                render: function (data) {
                    return new Date(data).toLocaleString();
                },
            },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
                width: "10%",
            },
        ],
        // ✅ Add this block for the search placeholder
        language: {
            search: "Search: ", // removes the "Search:" label
            searchPlaceholder: "Search here" // sets placeholder text
        },
        drawCallback: function () {
            if (typeof lucide !== "undefined") {
                lucide.createIcons();
            }
        },
    });
    
    $("#statusTabs .nav-link").on("click", function (e) {
        e.preventDefault();
        $("#statusTabs .nav-link").removeClass("active");
        $(this).addClass("active");

        const newStatus = $(this).data("status");
        const newUrl = new URL(window.location.href);
        newUrl.searchParams.set("status", newStatus);
        history.replaceState(null, "", newUrl.toString());

        table.ajax.reload();
    });

    $(document).on("click", ".view-details-btn", function () {
        const userId = $(this).data("id");
        $("#modalContent").html(
            '<div class="text-center my-4">Loading...</div>'
        );
        $("#userDetailsModal").modal("show");
        $(".modal-title").text("User Details");

        $.get(`/user-management/${userId}`, function (res) {
            $("#modalContent").html(res.html);
        }).fail(function () {
            $("#modalContent").html(
                '<div class="text-danger">Failed to load user details.</div>'
            );
        });
    });

    $(document).on("click", ".toggle-status-btn", function () {
        const userId = $(this).data("id");
        const action = $(this).data("action");

        Swal.fire({
            title: `Are you sure?`,
            text: `Do you want to ${action} this user?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: `Yes, ${action}`,
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/user-management/${userId}`,
                    method: "PUT",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                        action: action,
                    },
                    success: function (res) {
                        Swal.fire({
                            title: "Success!",
                            text: `User has been ${
                                action === "activate"
                                    ? "activated"
                                    : "deactivated"
                            } successfully.`,
                            icon: "success",
                            timer: 1500,
                            showConfirmButton: false,
                        });
                        table.ajax.reload();
                    },
                    error: function () {
                        Swal.fire("Error", "Something went wrong.", "error");
                    },
                });
            }
        });
    });
});
