@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">
    @include('layouts.dashboard.breadcrumb')

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
            'title' => 'FIFO Breakdown for ' . $product->name,
            'cardtopAddButton' => false,
            'cardtopAddButtonTitle' => '',
            'cardtopAddButtonId' => '',
            'cardtopButtonMode' => ''
            ])

            {{-- Product Information --}}
            <div class="mb-4">
                <p><strong>Product Name:</strong> {{ $product->name }}</p>
                <p><strong>SKU:</strong> {{ $product->sku }}</p>
                <p><strong>Price:</strong> ₱{{ number_format($product->price, 2) }}</p>
            </div>

            {{-- FIFO Table --}}
            @component('components.table', [
            'id' => 'fifoTable',
            'thead' => '
            <tr>
                <th>Batch #</th>
                <th>Quantity</th>
                <th>Remaining</th>
                <!-- <th>Cost</th> -->
                <th>Received Date</th>
                <th>Expiry Date</th>
                <!--<th>Note</th> -->
                <th>Status</th>
            </tr>
            '
            ])
            @endcomponent

            {{-- Footer Row (Back button + Stock Available) --}}
            <div class="mt-4 d-flex justify-content-between align-items-center">
                <a href="{{ route('inventory') }}" class="btn btn-outline-light">
                    ← Back to Inventory List
                </a>

                <div class="text-end d-none">
                    <span class="fw-bold">Stock Available: </span>
                    <span class="badge 
                        {{ $product->current_stock <= 0 ? 'bg-danger' : ($product->current_stock < $product->critical_stock_level ? 'bg-warning text-dark' : 'bg-success') }}
                        fs-6">
                        {{ $product->current_stock }}
                    </span>
                </div>
            </div>

            @endcomponent
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        const table = $("#fifoTable").DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ url('inventory-management/fifo/' . $product->id) }}",
            columns: [{
                    data: "batch_no",
                    name: "batch_no",
                    className: "dt-left-int",
                    responsivePriority: 1,
                },
                {
                    data: "quantity",
                    name: "quantity",
                    className: "dt-left-int",
                    responsivePriority: 1,
                },
                {
                    data: "remaining",
                    name: "remaining",
                    className: "dt-left-int",
                    responsivePriority: 1,
                },
                // {
                //     data: "cost",
                //     name: "cost"
                // },
                {
                    data: "received_date",
                    name: "received_date",
                    className: "dt-left-int",
                    responsivePriority: 1,
                },
                {
                    data: "expiry_date",
                    name: "expiry_date",
                    className: "dt-left-int",
                    responsivePriority: 1,
                },
                //{
                //    data: "note",
                //    name: "note"
                //},
                {
                    data: "status",
                    name: "status"
                },
            ],
            order: [
                [0, 'asc']
            ],
            responsive: true,
            paging: false,
            searching: false,
            info: false,
            fixedHeader: {
                header: true
            },
            scrollCollapse: true,
            scrollX: true,
            scrollY: 600,
            autoWidth: false,
            language: {
                emptyTable: "No FIFO batch data available for this product.",
                processing: "Loading FIFO breakdown..."
            },
            drawCallback: function() {
                if (typeof lucide !== "undefined") {
                    lucide.createIcons();
                }
            }
        });
    });
</script>
@endpush