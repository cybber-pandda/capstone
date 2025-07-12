@extends('layouts.shop')

@section('content')
<div class="section section-scrollable" style="margin-bottom: 20px;">
    <div class="container">


        <div class="section-title">
            <h3 class="title">{{ $page }}</h3>
        </div>


        @component('components.table', [
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
        </tr>'
        ])
        @endcomponent


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
            autoWidth: false,
            responsive: true,
            ajax: {
                url: "/b2b/purchase-requests",
                data: function(d) {
                    d.status = $('.filter-btn.active').data('status') || '';
                }
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
            ]
        });

        $(document).on('click', '.filter-btn', function() {
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            table.ajax.reload();
        });
    });
</script>
@endpush