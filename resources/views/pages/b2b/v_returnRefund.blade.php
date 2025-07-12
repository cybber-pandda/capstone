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

                @component('components.table', [
                'id' => 'returnTable',
                'thead' => '
                <tr>
                    <th>Image</th>
                    <th>SKU</th>
                    <th>Name</th>
                    <th>Quantity</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>'
                ])
                @endcomponent

            </div>

            <div class="tab-pane fade" id="refund">

                @component('components.table', [
                'id' => 'refundTable',
                'thead' => '
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
                </tr>'
                ])
                @endcomponent

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
        } else if (type === 'cancelled') {
            columns = [{
                data: 'message'
            }];
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