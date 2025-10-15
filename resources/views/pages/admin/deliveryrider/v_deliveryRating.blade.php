@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', ['title' => 'My Rating List', 'cardtopAddButton' => false])
            @component('components.table', [
            'id' => 'deliveryRatingTable',
            'thead' => '
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Total Items</th>
                <th>Grand Total</th>
                <th>Rating</th>
            </tr>
            '
            ])
            @endcomponent
            @endcomponent
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        const table = $('#deliveryRatingTable').DataTable({
            processing: true,
            serverSide: true,
            paginationType: "simple_numbers",
            responsive: true,
            aLengthMenu: [
                [5, 10, 30, 50, -1],
                [5, 10, 30, 50, "All"]
            ],
            iDisplayLength: 10,
            language: { search: "Search: " , searchPlaceholder: "Search here"},
            fixedHeader: {
                header: true
            },
            scrollCollapse: true,
            scrollX: true,
            scrollY: 600,
            ajax: "{{ route('deliveryrider.delivery.ratings') }}",
            columns: [{
                    data: 'order_number',
                    name: 'order_number'
                },
                {
                    data: 'customer_name',
                    name: 'user.name'
                },
                {
                    data: 'total_items',
                    name: 'total_items',
                    className: "dt-left-int",
                    responsivePriority: 1,
                },
                {
                    data: 'grand_total',
                    name: 'grand_total',
                    className: "dt-left-int",
                    responsivePriority: 1,
                },
                {
                    data: 'rating',
                    name: 'rating',
                    orderable: false,
                    searchable: false
                }
            ],
            drawCallback: function() {
                if (window.lucide) {
                    lucide.createIcons();
                }
            }
        });
    });
</script>
@endpush