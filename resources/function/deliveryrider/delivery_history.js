$(document).ready(function () {
    const table = $("#deliveryHistoryTable").DataTable({
        processing: true,
        serverSide: true,
        paginationType: "simple_numbers",
        responsive: true,
        aLengthMenu: [[5, 10, 30, 50, -1], [5, 10, 30, 50, "All"]],
        iDisplayLength: 10,
        language: { search: "Search: " , searchPlaceholder: "Search here"},
        fixedHeader: { header: true },
        scrollCollapse: true,
        scrollX: true,
        scrollY: 600,
        ajax: {
            url: "/deliveryrider/delivery/histories",
        },
        autoWidth: false,
        columns: [
            { data: "order_number", name: "order_number", width: "15%" },
            { data: "customer_name", name: "customer_name", width: "20%" },
            { data: "total_items", name: "total_items", className: "dt-left-int", orderable: false, searchable: false, width: "10%" },
            { data: "grand_total", name: "grand_total", className: "dt-left-int", orderable: false, searchable: false, width: "10%" },
            { data: "tracking_number", name: "tracking_number", width: "15%" },
            { data: "action", name: "action", orderable: false, searchable: false, width: "10%" },
        ],
        drawCallback: function () {
            if (typeof lucide !== "undefined") {
                lucide.createIcons();
            }
        }
    });

    $(document).on('click', '.view-details-btn', function () {
        const orderId = $(this).data('id');
        $('#modalContent').html('<div class="text-center my-4">Loading...</div>');
        $('#orderDetailsModal').modal('show');
        $('.modal-title').text('History Logged');

        $.get(`/deliveryrider/delivery/history/${orderId}`, function (res) {
            $('#modalContent').html(res.html);
        }).fail(function () {
            $('#modalContent').html('<div class="text-danger">Failed to load details.</div>');
        });
    });
});
