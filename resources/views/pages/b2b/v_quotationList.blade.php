@extends('layouts.shop')

@section('content')
<div class="section">
    <div class="container">
        <div class="section-title">
            <h3 class="title">{{ $page }}</h3>
        </div>

        @component('components.table', [
            'id' => 'sentQuotationsTable',
            'thead' => '
                <tr>
                    <th>ID</th>
                    <th>Total Items</th>
                    <th>Grand Total</th>
                    <th>Date Created</th>
                    <th></th>
                </tr>'
        ])
        @endcomponent
    </div>

    @component('components.modal', ['id' => 'cancelPRModal', 'title' => 'Cancel Quotation', 'size' => 'md'])
    <form id="cancelPRForm">
        @csrf
        <input type="hidden" name="quotation_id" id="cancelQuotationId">
        <div class="mb-3">
            <label for="cancelRemarks" class="form-label">Remarks (optional)</label>
            <textarea name="remarks" id="cancelRemarks" class="form-control" rows="4" placeholder="Reason for cancellation..."></textarea>
        </div>
    </form>
    @slot('footer')
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-danger" id="confirmCancelPRBtn">
            <i class="link-icon" data-lucide="x-circle"></i> Confirm Cancel
        </button>
    @endslot
    @endcomponent

</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let table = $('#sentQuotationsTable').DataTable({
            processing: true,
            serverSide: true,
            fixedHeader: {
                header: true
            },
            scrollCollapse: true,
            scrollX: true,
            scrollY: 600,
            autoWidth: false,
            responsive: true,
            ajax: {
                url: "/b2b/quotations/review",
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'total_items', name: 'total_items' },
                { data: 'grand_total', name: 'grand_total' },
                { data: 'created_at', name: 'created_at' },
                { 
                    data: 'action', 
                    name: 'action', 
                    width: "20%", 
                    orderable: false, 
                    searchable: false 
                }
            ],
            drawCallback: function() {
                if (typeof lucide !== "undefined") {
                    lucide.createIcons();
                }
            }
        });
    });

    $(function() {
        const params = new URLSearchParams(window.location.search);
        const trackId = params.get('track_id');

        if (trackId) {
            // Optional: clean the URL after getting the param
            window.history.replaceState({}, document.title, window.location.pathname);

            Swal.fire({
                title: 'Processing...',
                html: 'Waiting for Sales Officer to process your order.<br><small>This may take a few moments...</small>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Poll every 3 seconds
            const interval = setInterval(() => {
                $.ajax({
                    url: `/b2b/quotations/status/${trackId}`,
                    method: 'GET',
                    success: function(res) {
                        if (res.status === 'so_created' || res.status === 'delivery_in_progress') {
                            clearInterval(interval);
                            Swal.fire({
                                icon: 'info',
                                title: 'Your Order is on the Way!',
                                text: 'You can now track your delivery.',
                                confirmButtonText: 'Track Delivery',
                                showCancelButton: true,
                                cancelButtonText: 'Close'
                            }).then(result => {
                                if (result.isConfirmed) {
                                    window.location.href = `/b2b/delivery/track/${trackId}`;
                                } else {
                                    location.reload();
                                }
                            });
                        }
                    },
                    error: function() {
                        clearInterval(interval);
                        Swal.fire('Error', 'Failed to check order status.', 'error');
                    }
                });
            }, 3000);
        }
    });

    $(document).on('click', '.cancel-pr-modal-btn', function () {
        const id = $(this).data('id');
        $('#cancelQuotationId').val(id);
        $('#cancelRemarks').val('');
        $('#cancelPRModal').modal('show');
    });

    $(document).on('click', '#confirmCancelPRBtn', function () {
        const prId = $('#cancelQuotationId').val();
        const remarks = $('#cancelRemarks').val();

        $.ajax({
            url: `/b2b/quotations/cancel/${prId}`,
            method: 'POST',
            data: {
                remarks: remarks,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (res) {
                $('#cancelPRModal').modal('hide');
                toast('success', res.message);
                $('#sentQuotationsTable').DataTable().ajax.reload();
            },
            error: function (xhr) {
                toast('error', xhr.responseJSON?.message || 'Failed to cancel.');
            }
        });
    });

</script>
@endpush
