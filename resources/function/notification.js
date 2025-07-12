$(document).ready(function () {
    const table = $("#notificationTable").DataTable({
        processing: true,
        serverSide: true,
        paginationType: "simple_numbers",
        responsive: true,
        layout: {
            topEnd: {
                search: {
                    placeholder: "Search Notifications",
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
        autoWidth: false,
        ajax: "/notifications",
        columns: [
            { data: "message", name: "message" },
            { data: "type", name: "type" },
            { data: "time", name: "time" },
            { data: "read_at", name: "read_at" },
            { data: "checkbox", orderable: false, searchable: false }
        ],
        drawCallback: function () {
            // Reset select all checkbox
            $('#selectAllCheckboxes').prop('checked', false);

            // Handle select all
            $('#selectAllCheckboxes').off().on('change', function () {
                $('.notification-checkbox').prop('checked', this.checked);
            });
        }
    });

    $('#markAllAsReadBtn').click(function () {
        const selected = $('.notification-checkbox:checked').map(function () {
            return $(this).val();
        }).get();

        if (selected.length === 0) {
            toast('warning', 'Please select at least one unread notification.');
            return;
        }

        $.ajax({
            url: '/notifications/mark-all-selected',
            method: 'POST',
            data: {
                ids: selected
            },
            success: function () {
                table.ajax.reload(null, false);
            },
            error: function () {
                toast('error', 'An error occurred while marking notifications as read.');
            }
        });
    });
});

