@extends('layouts.dashboard')

@section('content')
    <div class="page-content container-xxl">
        @include('layouts.dashboard.breadcrumb')

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                @component('components.card', [
                    'title' => 'Delivery Report',
                    'cardtopAddButton' => false
                ])
                @component('components.table', [
                    'id' => 'deliveryReportTable',
                    'thead' => '
                                        <tr>
                                            <th>Order #</th>
                                            <th>Delivery Rider</th>
                                            <th>Quantity</th>
                                            <th>Status</th>
                                            <th>Delivery Date</th>
                                            <th>Tracking #</th>
                                            <th>Proof of Delivery</th>
                                            <th>Latest Location</th>
                                            <th>Latest Remark</th>
                                            <th>Logged At</th>
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
        $(function () {
            $('#deliveryReportTable').DataTable({
                processing: true,
                serverSide: true,
                paginationType: "simple_numbers",
                responsive: true,
                aLengthMenu: [
                    [5, 10, 30, 50, -1],
                    [5, 10, 30, 50, "All"],
                ],
                iDisplayLength: 10,
                language: { search: "Search: " , searchPlaceholder: "Search here"},
                fixedHeader: { header: true },
                scrollCollapse: true,
                scrollX: true,
                scrollY: 600,
                autoWidth: false,
                ajax: "{{ route('delivery.report') }}",
                columns: [
                    { data: 'order_number' },
                    { data: 'rider' },
                    { data: 'quantity' },
                    { data: 'status' },
                    { data: 'delivery_date' },
                    { data: 'tracking_number' },
                    { data: 'proof_delivery', orderable: false, searchable: false },
                    { data: 'location', orderable: false, searchable: false },
                    { data: 'remarks' },
                    { data: 'logged_at' },
                ]
            });
        });
    </script>
@endpush