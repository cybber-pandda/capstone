$(document).ready(function () {
    // Returns Table
    const returnsTable = $("#returnsTable").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        language: {
            search: "Search:", // remove "Search:" label
            searchPlaceholder: "Search here" // add placeholder
        },
        ajax: {
            url: "/salesofficer/return-refund/data",
            data: { type: "return" }
        },
        columns: [
            { data: "customer_name", name: "customer_name" },
            { data: "product_name", name: "product_name" },
            { data: "reason", name: "reason" },
            { data: "status", name: "status" },
            { data: "photo", name: "photo" },
            { data: "created_at", name: "created_at" },
            { data: "action", name: "action", orderable: false, searchable: false }
        ]
    });

    // Refunds Table
    const refundsTable = $("#refundsTable").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        language: {
            search: "Search:", // remove "Search:" label
            searchPlaceholder: "Search here" // add placeholder
        },
        ajax: {
            url: "/salesofficer/return-refund/data",
            data: { type: "refund" }
        },
        columns: [
            { data: "customer_name", name: "customer_name" },
            { data: "product_name", name: "product_name" },
            { data: "amount", name: "amount" },
            { data: "method", name: "method" },
            { data: "status", name: "status" },
            { data: "photo", name: "photo" },
            { data: "created_at", name: "created_at" },
            { data: "action", name: "action", orderable: false, searchable: false }
        ]
    });

     // Open Return modal
    $(document).on('click', '.review-return', function() {
        const id = $(this).data('id');
        $.get(`/salesofficer/return-details/${id}`, function(res){
            $('#returnDetails').html(res.html);
            $('.modal-title').text('Purchase Return');
            $('#viewReturnModal').modal('show');
        });
    });

    // Open Refund modal
    $(document).on('click', '.process-refund', function() {
        const id = $(this).data('id');
        $.get(`/salesofficer/refund-details/${id}`, function(res){
            $('#refundDetails').html(res.html);
            $('.modal-title').text('Purchase Refund');
            $('#viewRefundModal').modal('show');
        });
    });

    // Approve Return
    $(document).on('click', '.approve-return', function() {
        const id = $(this).data('id');
        $.post(`/salesofficer/return/${id}/approve`, {_token: $('meta[name="csrf-token"]').attr("content")}, function(res){
            toast('success', res.message);
            $('#viewReturnModal').modal('hide');
            returnsTable.ajax.reload();
        });
    });

    // Reject Return
    $(document).on('click', '.reject-return', function() {
        const id = $(this).data('id');
        $.post(`/salesofficer/return/${id}/reject`, {_token: $('meta[name="csrf-token"]').attr("content")}, function(res){
           toast('success', res.message);
            $('#viewReturnModal').modal('hide');
            returnsTable.ajax.reload();
        });
    });

     // Process Refund
    $(document).on('click', '.process-refund-confirm', function() {
        const id = $(this).data('id');
        $.post(`/salesofficer/refund/${id}/approve`, {_token: $('meta[name="csrf-token"]').attr("content")}, function(res){
            toast('success', res.message);
            $('#viewRefundModal').modal('hide');
            refundsTable.ajax.reload();
        });
    });

    // Reject Refund
    $(document).on('click', '.reject-refund', function() {
        const id = $(this).data('id');
        $.post(`/salesofficer/refund/${id}/reject`, {_token: $('meta[name="csrf-token"]').attr("content")}, function(res){
           toast('success', res.message);
            $('#viewRefundModal').modal('hide');
            refundsTable.ajax.reload();
        });
    });

    // Refresh table when tab is clicked
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().draw();
    });
});
