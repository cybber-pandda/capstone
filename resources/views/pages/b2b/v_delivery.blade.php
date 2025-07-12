@extends('layouts.shop')

@section('content')
<div class="section section-scrollable" style="margin-bottom: 20px;">
    <div class="container">


        <div class="section-title">
            <h3 class="title">{{ $page }}</h3>
        </div>

        @component('components.table', [
        'id' => 'deliveryLocationTable',
        'thead' => '
        <tr>
            <th>Order #</th>
            <th>Delivery Rider</th>
            <th>Total Items</th>
            <th>Grand Total</th>
            <th>Status</th>
            <th>Rating</th>
            <th></th>
        </tr>'
        ])
        @endcomponent

    </div>

    <div class="modal fade" id="viewProofModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="border:0px">
                    <h5 class="modal-title" id="modalTitle">Proof of Delivery</h5>
                </div>
                <div class="modal-body">
                    <img id="proofImagePreview" src="" class="img-fluid" alt="Proof of Delivery" style="width:100%">
                </div>
                <div class="modal-footer" style="border:0px">
                    <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let table = $('#deliveryLocationTable').DataTable({
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
                url: "{{ route('b2b.delivery.index') }}"
            },
            columns: [{
                    data: "order_number",
                    name: "order_number"
                },
                {
                    data: "delivery_name",
                    name: "delivery_name"
                },
                {
                    data: "total_items",
                    name: "total_items"
                },
                {
                    data: "grand_total",
                    name: "grand_total"
                },
                {
                    data: "status",
                    name: "status",
                    orderable: false,
                    searchable: false
                },
                {
                    data: "rating",
                    name: "rating",
                    orderable: false,
                    searchable: false
                },
                {
                    data: "action",
                    name: "action",
                    orderable: false,
                    searchable: false
                },
            ]
        });

        $(document).on("click", ".view-proof-btn", function() {
            const imageUrl = $(this).data("proof");
            $("#proofImagePreview").attr("src", imageUrl);
            $("#viewProofModal").modal("show");
        });
    });
</script>
@endpush