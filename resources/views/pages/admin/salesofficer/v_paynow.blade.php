@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
            'title' => 'Pay-Now Payment Method List',
            'cardtopAddButton' => true,
            'cardtopAddButtonTitle' => 'Manual Payment (COD)',
            'cardtopAddButtonId' => 'add',
            'cardtopButtonMode' => 'add'
            ])

            @component('components.table', [
            'id' => 'paynowTable',
            'thead' => '
            <tr>
                <th>Customer Name</th>
                <th>Bank Name</th>
                <th>Paid Amount</th>
                <th>Paid Date</th>
                <th>Proof Payment</th>
                <th>Reference Number</th>
                <th></th>
            </tr>
            '
            ])
            @endcomponent

            @endcomponent
        </div>
    </div>

    @component('components.modal', ['id' => 'manualPaymentModal', 'size' => 'md', 'scrollable' => true])
    <form id="manualPaymentForm"  action="{{ route('salesofficer.paynow.manual') }}" method="POST">
        
        @component('components.select', [
            'label' => 'COD Purchase Request Customer',
            'name' => 'purchase_request_id',
            'selected' => old('purchase_request_id', ''),
            'options' => $cashDeliveries->toArray(),
            'attributes' => 'required'
        ])
        @endcomponent

        @component('components.input', [
            'label' => 'Paid Amount',
            'type' => 'number',
            'name' => 'paid_amount',
            'attributes' => 'placeholder=\'Enter delivery fee\''
        ]) @endcomponent
        
        @component('components.input', [
            'label' => 'Paid Date',
            'type' => 'date',
            'name' => 'paid_date',
            'attributes' => 'placeholder=\'Enter delivery fee\''
        ]) @endcomponent 

    </form>
    @slot('footer')
    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary btn-sm" id="manualPayment">
        <span class="manualPayment_button_text">Save</span>
        <span class="manualPayment_load_data d-none">Loading <i class="loader"></i></span>
    </button>
    @endslot
    @endcomponent

</div>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'salesofficer_paynow']) }}"></script>
@endpush