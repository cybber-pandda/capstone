@extends('layouts.shop')

@section('content')
<div class="section section-scrollable" style="margin-bottom: 20px;">
    <div class="container">
        <div class="section-title">
            <h3 class="title">{{ $page }}</h3>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li class="active"><a href="#processingTab" role="tab" data-toggle="tab">Processing</a></li>
            <li><a href="#rejectedTab" role="tab" data-toggle="tab">Rejected</a></li>
            <li><a href="#cancelledTab" role="tab" data-toggle="tab">Cancelled</a></li>
        </ul>

        <!-- Tab content -->
        <div class="tab-content" style="margin-top: 20px;">
            <!-- Processing Tab -->
            <div class="tab-pane active" id="processingTab">
                <!-- @component('components.table', [
                'id' => 'processingTable',
                'thead' => '
                <tr>
                    <th>ID</th>
                    <th>Total Items</th>
                    <th>Grand Total</th>
                    <th>Date Created</th>
                    <th></th>
                </tr>'
                ])
                @endcomponent -->

                <table id="processingTable" class="table-2">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Total Items</th>
                            <th>Grand Total</th>
                            <th>Date Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>

            </div>

            <!-- Rejected Tab -->
            <div class="tab-pane" id="rejectedTab">
                <!-- @component('components.table', [
                'id' => 'rejectedTable',
                'thead' => '
                <tr>
                    <th>ID</th>
                    <th>Total Items</th>
                    <th>Grand Total</th>
                    <th>Date Created</th>
                    <th></th>
                </tr>'
                ])
                @endcomponent -->

                <table id="rejectedTable" class="table-2">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Total Items</th>
                            <th>Grand Total</th>
                            <th>Date Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>

            </div>

            <!-- Cancelled Tab -->
            <div class="tab-pane" id="cancelledTab">
                <!-- @component('components.table', [
                'id' => 'cancelledTable',
                'thead' => '
                <tr>
                    <th>ID</th>
                    <th>Total Items</th>
                    <th>Grand Total</th>
                    <th>Date Created</th>
                    <th></th>
                </tr>'
                ])
                @endcomponent -->

                <table id="cancelledTable" class="table-2">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Total Items</th>
                            <th>Grand Total</th>
                            <th>Date Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div class="modal fade" id="cancelPRModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="border:0px">
                    <h5 class="modal-title" id="modalTitle">Cancel Quotation</h5>
                </div>
                <div class="modal-body">
                    <form id="cancelPRForm">
                        @csrf
                        <input type="hidden" name="quotation_id" id="cancelQuotationId">
                        <div class="mb-3">
                            <label for="cancelRemarks" class="form-label">Remarks (optional)</label>
                            <textarea name="remarks" id="cancelRemarks" class="form-control" rows="4" placeholder="Reason for cancellation..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border:0px">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger" id="confirmCancelPRBtn">
                        Confirm Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const checkAddress = '<?php echo $hasAddress ? 'true' : 'false'; ?>';

        if (checkAddress === 'false') {
            Swal.fire({
                title: 'No Address Found',
                text: 'Please add a shipping address before proceeding.',
                icon: 'warning',
                confirmButtonText: 'Add Address',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/b2b/address';
                }
            });
        }

        const commonOptions = {
            processing: true,
            serverSide: true,
            fixedHeader: true,
            scrollCollapse: true,
            scrollX: true,
            scrollY: 600,
            paging: false,
            autoWidth: false,
            responsive: false,
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'total_items',
                    name: 'total_items'
                },
                {
                    data: 'grand_total',
                    name: 'grand_total'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'action',
                    name: 'action',
                    width: "20%",
                    orderable: false,
                    searchable: false
                }
            ],

            createdRow: function(row, data, dataIndex) {
                $('td', row).eq(0).attr('data-label', 'ID#:');
                $('td', row).eq(1).attr('data-label', 'Total Items:');
                $('td', row).eq(2).attr('data-label', 'Grand Total:');
                $('td', row).eq(3).attr('data-label', 'Date Created:');
                $('td', row).eq(4).attr('data-label', 'Action:');

            },
            drawCallback: function() {
                if (typeof lucide !== "undefined") {
                    lucide.createIcons();
                }
            }
        };

        // Processing Table
        $('#processingTable').DataTable($.extend({}, commonOptions, {
            ajax: {
                url: "/b2b/quotations/review",
                data: {
                    type: 'processing'
                }
            }
        }));

        // Rejected Table
        $('#rejectedTable').DataTable($.extend({}, commonOptions, {
            ajax: {
                url: "/b2b/quotations/review",
                data: {
                    type: 'rejected'
                }
            }
        }));

        // Cancelled Table
        $('#cancelledTable').DataTable($.extend({}, commonOptions, {
            ajax: {
                url: "/b2b/quotations/review",
                data: {
                    type: 'cancelled'
                }
            }
        }));

        // Tab memory using hash
        if (location.hash) {
            window.scrollTo(0, 0);
            setTimeout(() => window.scrollTo(0, 0), 1);
        }

        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            const target = $(e.target).attr('href');
            history.replaceState(null, null, target);

            // Adjust DataTables
            $.fn.dataTable
                .tables({
                    visible: true,
                    api: true
                })
                .columns.adjust();
        });

        // Handle order tracking (if track_id is in URL)
        const params = new URLSearchParams(window.location.search);
        const trackId = params.get('track_id');
        if (trackId) {
            window.history.replaceState({}, document.title, window.location.pathname);

            // Swal.fire({
            //     title: 'Processing...',
            //     html: 'Waiting for Sales Officer to process your order.<br><small>This may take a few moments...</small>',
            //     allowOutsideClick: false,
            //     allowEscapeKey: false,
            //     showConfirmButton: false,
            //     didOpen: () => {
            //         Swal.showLoading();
            //     }
            // });

        /*    const interval = setInterval(() => {
                $.ajax({
                    url: `/b2b/quotations/status/${trackId}`,
                    method: 'GET',
                    success: function(res) {
                        if (res.status === 'delivery_in_progress') {
                            clearInterval(interval);

                            Swal.fire({
                                icon: 'info',
                                title: 'Your Order is on the Way!',
                                text: 'You can now track your delivery.',
                                confirmButtonText: 'Track Delivery',
                                timer: 4000,
                                showCancelButton: false,
                                showConfirmButton: false,
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
            }, 3000); */
        }
    });
</script>
@endpush