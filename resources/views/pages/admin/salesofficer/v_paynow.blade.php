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
            'attributes' => 'id="paid_date" required onkeydown="return false" onpaste="return false"'
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    const paidDateInput = document.getElementById('paid_date');
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0'); // Months are 0-based
    const dd = String(today.getDate()).padStart(2, '0');
    const minDate = `${yyyy}-${mm}-${dd}`;

    // Set the minimum selectable date to today
    paidDateInput.setAttribute('min', minDate);
});
</script>

<script src="{{ route('secure.js', ['filename' => 'salesofficer_paynow']) }}"></script>
@endpush