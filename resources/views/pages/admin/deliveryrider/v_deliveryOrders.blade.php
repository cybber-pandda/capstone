@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">

            @component('components.card', [
            'title' => 'Delivery Order List',
            'cardtopAddButton' => false,
            ])

            <ul class="nav nav-tabs nav-tabs-line mb-3" id="statusTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link" id="pending-tab" data-bs-toggle="tab" href="#pending" role="tab"
                        aria-controls="pending" aria-selected="true" data-status="pending">Pending</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="assigned-tab" data-bs-toggle="tab" href="#pending" role="tab"
                        aria-controls="assigned" aria-selected="false" data-status="assigned">Assigned</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="on_the_way-tab" data-bs-toggle="tab" href="#pending" role="tab"
                        aria-controls="on_the_way" aria-selected="false" data-status="on_the_way">On the Way</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="delivered-tab" data-bs-toggle="tab" href="#pending" role="tab"
                        aria-controls="delivered" aria-selected="false" data-status="delivered">Delivered</a>
                </li>
            </ul>

            <div class="tab-content" id="statusTabsContent">
                <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">

                    @component('components.table', [
                    'id' => 'deliveryOrdersTable',
                    'thead' => '
                    <tr>
                        <th>Order #</th>
                        <th>Customer Name</th>
                        <th>Total Items</th>
                        <th>Grand Total</th>
                        <th>Date Created</th>
                        <th>Action</th>
                    </tr>
                    '
                    ])
                    @endcomponent

                </div>
            </div>

            @endcomponent


        </div>
    </div>

    @component('components.modal', ['id' => 'viewOrderItemsModal', 'size' => 'lg', 'scrollable' => true])
    <div id="viewItemsList"></div>
    @slot('footer')
    <button type="button" class="btn btn-inverse-secondary" data-bs-dismiss="modal">Close</button>
    @endslot
    @endcomponent

</div>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'delivery_order']) }}"></script>
@endpush