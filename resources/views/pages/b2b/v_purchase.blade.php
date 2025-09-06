@extends('layouts.shop')

@section('content')
<div class="section section-scrollable" style="margin-bottom: 20px;">
    <div class="container">

        <div class="section-title">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h3 class="title">{{ $page }}</h3>
                </div>
                <!-- <div>
                    <a href="{{ route('b2b.purchase.rr') }}" class="btn btn-sm btn-primary">Show Return/Refund</a>
                </div> -->
            </div>
        </div>

        <!-- @component('components.table', [
        'id' => 'purchaseRequestTable',
        'thead' => '
        <tr>
            <th>Image</th>
            <th>SKU</th>
            <th>Name</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
            <th>Date</th>
            <th></th>
        </tr>'
        ])
        @endcomponent -->

        <table id="purchaseRequestTable" class="table-2">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>SKU</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>


    </div>
</div>

{{-- Return Modal --}}
<div class="modal fade" id="returnModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="returnForm" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Return Product</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="item_id" id="returnItemId">
                    <div class="form-group">
                        <label>Reason for return</label>
                        <textarea class="form-control" name="reason" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Upload Photo (optional)</label>
                        <input type="file" name="photo" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning">Submit Return</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Refund Modal --}}
<div class="modal fade" id="refundModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="refundForm" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Request Refund</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="item_id" id="refundItemId">
                    <div class="form-group">
                        <label>Reason for refund</label>
                        <textarea class="form-control" name="reason" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Method</label>
                        <select name="method" class="form-control" required>
                            <option value="bank">Bank Transfer</option>
                            <option value="gcash">GCash</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Transaction Reference</label>
                        <input type="text" name="reference" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Upload Proof</label>
                        <input type="file" name="proof" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Submit Refund</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let table = $('#purchaseRequestTable').DataTable({
            processing: true,
            serverSide: true,
            fixedHeader: {
                header: true
            },
            scrollCollapse: true,
            scrollX: true,
            scrollY: 600,
            paging: false,
            autoWidth: false,
            responsive: false,
            ajax: {
                url: "/b2b/purchase"
            },
            columns: [{
                    data: 'image',
                    name: 'image',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'sku',
                    name: 'sku'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'price',
                    name: 'price'
                },
                {
                    data: 'quantity',
                    name: 'quantity'
                },
                {
                    data: 'subtotal',
                    name: 'subtotal'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                },
            ]
        });


        // Show return modal
        $(document).on('click', '.btn-return', function() {
            const itemId = $(this).data('id');
            $('#returnItemId').val(itemId);
            $('#returnModal').modal('show');
        });

        // Handle Return Form with file upload
        $('#returnForm').submit(function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                url: '/b2b/purchase/return',
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#returnModal').modal('hide');
                    $('#purchaseRequestTable').DataTable().ajax.reload();
                    toast('success', response.message);
                },
                error: function(xhr) {
                    let msg = 'Return request failed.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    toast('error', msg);
                }
            });
        });

        // Show refund modal
        $(document).on('click', '.btn-refund', function() {
            const itemId = $(this).data('id');
            $('#refundItemId').val(itemId);
            $('#refundModal').modal('show');
        });

        // Handle Refund Form with file upload
        $('#refundForm').submit(function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                url: '/b2b/purchase/refund',
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#refundModal').modal('hide');
                    $('#purchaseRequestTable').DataTable().ajax.reload();
                    toast('success', response.message);
                },
                error: function(xhr) {
                    let msg = 'Refund request failed.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    toast('error', msg);
                }
            });
        });

    });
</script>
@endpush