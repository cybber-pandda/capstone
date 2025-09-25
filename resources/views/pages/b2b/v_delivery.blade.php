@extends('layouts.shop')
<style>
    #deliveryLocationTable {
        font-size: 12px;
    }
</style>
@section('content')
<div class="section section-scrollable">
    <div class="container">


        <div class="section-title">
            <h3 class="title">{{ $page }}</h3>
        </div>

        <div>
            <table id="deliveryLocationTable" class="table-2">
                <thead>
                    <tr style="font-size: 12px;">
                        <th>Order #</th>
                        <th>Driver</th>
                        <!-- <th>Items</th> -->
                        <th>Total</th>
                        <th>Status</th>
                        <th>Rating</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>





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
            paging: false,
            autoWidth: false,
            // responsive: true,
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
                // {
                //     data: "total_items",
                //     name: "total_items"
                // },
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
                    searchable: false,
                    width: "220px" // ✅ set fixed width
                },
            ],
            createdRow: function(row, data, dataIndex) {
                $('td', row).eq(0).attr('data-label', 'Order #');
                $('td', row).eq(1).attr('data-label', 'Driver');
                $('td', row).eq(2).attr('data-label', 'Total');
                $('td', row).eq(3).attr('data-label', 'Status');
                $('td', row).eq(4).attr('data-label', 'Rating');
                $('td', row).eq(5).attr('data-label', '');
            }
        });

        $(document).on("click", ".view-proof-btn", function() {
            const imageUrl = $(this).data("proof");
            $("#proofImagePreview").attr("src", imageUrl);
            $("#viewProofModal").modal("show");
        });
    });
</script>
@endpush