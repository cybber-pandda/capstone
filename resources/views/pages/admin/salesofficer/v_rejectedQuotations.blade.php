@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
                'title' => 'Rejected Quotation List',
                'cardtopAddButton' => false,
            ])

            @component('components.table', [
                'id' => 'rejectedQuotationTable',
                'thead' => '
                <tr>
                    <th>ID</th>
                    <th>Customer Name</th>
                    <th>Total Items</th>
                    <th>Grand Total</th>
                    <th>Date Created</th>
                    <th>Status</th>
                    <th>Reason for Rejection</th>
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
    $(document).ready(function () {
        const table = $("#rejectedQuotationTable").DataTable({
            processing: true,
            serverSide: true,
            paginationType: "simple_numbers",
            responsive: true,
            layout: {
                topEnd: {
                    search: {
                        placeholder: "Search Rejected Quotations",
                    },
                },
            },
            aLengthMenu: [
                [5, 10, 30, 50, -1],
                [5, 10, 30, 50, "All"],
            ],
            iDisplayLength: 10,
            language: { search: "Search:" },
            fixedHeader: { header: true },
            scrollCollapse: true,
            scrollX: true,
            scrollY: 600,
            ajax: "/salesofficer/rejected-quotations/all", // adjust route as needed
            autoWidth: false,
            columns: [
                { data: "id", name: "id", className: "dt-left-int", width: "5%" },
                { data: "customer_name", name: "customer_name", width: "20%" },
                { data: "total_items", name: "total_items", className: "dt-left-int", orderable: false, searchable: false, width: "10%" },
                { data: "grand_total", name: "grand_total", className: "dt-left-int", orderable: false, searchable: false, width: "10%" },
                { data: "created_at", name: "created_at", className: "dt-left-int", width: "15%" },
                { data: "status", name: "status", className: "dt-left-int", orderable: false, searchable: false, width: "15%" },
                { data: "rejection_reason", name: "rejection_reason", className: "dt-left-int", orderable: false, searchable: false, width: "25%" },
            ],
            drawCallback: function () {
                if (typeof lucide !== "undefined") {
                    lucide.createIcons();
                }
            },
        });
    });
</script>
@endpush
