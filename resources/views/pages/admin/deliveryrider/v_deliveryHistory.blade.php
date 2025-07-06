@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">

            @component('components.card', ['title' => 'Delivery History List', 'cardtopAddButton' => false])
                @component('components.table', [
                    'id' => 'deliveryHistoryTable',
                    'thead' => '
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Total Items</th>
                            <th>Grand Total</th>
                            <th>Tracking #</th>
                            <th>Action</th>
                        </tr>
                    '
                ])
                @endcomponent
            @endcomponent

        </div>
    </div>
</div>

@component('components.modal', ['id' => 'orderDetailsModal', 'size' => 'lg', 'scrollable' => true])
    <div id="modalContent">
        <div class="text-center my-4">Loading...</div>
    </div>
    @slot('footer')
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    @endslot
@endcomponent
@endsection


@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'delivery_history']) }}"></script>
@endpush