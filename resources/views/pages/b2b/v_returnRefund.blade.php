@extends('layouts.shop')

@section('content')
<div class="section section-scrollable" style="margin-bottom: 20px;">
    <div class="container">
        <div class="section-title" style="margin-bottom: 15px;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <h3 class="title">{{ $page }}</h3>
                <!-- <a href="{{ route('b2b.purchase.index') }}" class="btn btn-sm btn-primary">Back</a> -->
            </div>
        </div>

        <ul class="nav nav-tabs" id="requestTabs">
            <li class="active"><a href="#return" data-toggle="tab">Return</a></li>
            <li><a href="#refund" data-toggle="tab">Refund</a></li>
            <!-- <li><a href="#cancelled" data-toggle="tab">Cancelled</a></li> -->
        </ul>

        <div class="tab-content" style="padding-top:15px;">
            <div class="tab-pane fade in active" id="return">
                <table class="table-2" style="font-size: 11px;width:100%" id='returnTable'>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>SKU</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="tab-pane fade" id="refund">
                <table class="table-2" style="font-size: 11px;width:100%" id='refundTable'>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>SKU</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

            </div>

            <div class="tab-pane fade" id="cancelled">
                <table class="table table-bordered" id="cancelledTable">
                    <thead>
                        <tr>
                            <th>Message</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function loadTable(id, type) {
        let columns = [];
        let createdRowFn = null; // placeholder for row customization

        if (type === 'return') {
            columns = [{
                    data: 'image',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'sku'
                },
                {
                    data: 'name'
                },
                {
                    data: 'quantity'
                },
                {
                    data: 'reason'
                },
                {
                    data: 'status'
                },
                {
                    data: 'date'
                }
            ];

            // Add createdRow labels for return table
            createdRowFn = function(row, data, dataIndex) {
                $('td', row).eq(0).attr('data-label', 'Image:');
                $('td', row).eq(1).attr('data-label', 'SKU:');
                $('td', row).eq(2).attr('data-label', 'Name:');
                $('td', row).eq(3).attr('data-label', 'Quantity:');
                $('td', row).eq(4).attr('data-label', 'Reason:');
                $('td', row).eq(5).attr('data-label', 'Status:');
                $('td', row).eq(6).attr('data-label', 'Date:');
            };

        } else if (type === 'refund') {
            columns = [{
                    data: 'image',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'sku'
                },
                {
                    data: 'name'
                },
                {
                    data: 'quantity'
                },
                {
                    data: 'amount'
                },
                {
                    data: 'method'
                },
                {
                    data: 'reference'
                },
                {
                    data: 'status'
                },
                {
                    data: 'date'
                }
            ];

            // Add createdRow labels for refund table
            createdRowFn = function(row, data, dataIndex) {
                $('td', row).eq(0).attr('data-label', 'Image:');
                $('td', row).eq(1).attr('data-label', 'SKU:');
                $('td', row).eq(2).attr('data-label', 'Name:');
                $('td', row).eq(3).attr('data-label', 'Quantity:');
                $('td', row).eq(4).attr('data-label', 'Amount:');
                $('td', row).eq(5).attr('data-label', 'Method:');
                $('td', row).eq(6).attr('data-label', 'Reference:');
                $('td', row).eq(7).attr('data-label', 'Status:');
                $('td', row).eq(8).attr('data-label', 'Date:');
            };

        } else if (type === 'cancelled') {
            columns = [{
                data: 'message'
            }];
            createdRowFn = function(row, data, dataIndex) {
                $('td', row).eq(0).attr('data-label', 'Message:');
            };
        }

        $('#' + id).DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("b2b.purchase.rr") }}',
                data: {
                    type: type
                }
            },
            columns: columns
        });
    }

    // Load default tab
    loadTable('returnTable', 'return');

    // Bootstrap 3 tab event
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        const target = $(e.target).attr('href').replace('#', '');
        loadTable(target + 'Table', target);
    });
</script>
@endpush