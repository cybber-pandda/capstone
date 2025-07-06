@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
            'title' => 'Pending Purchase Request List',
            'cardtopAddButton' => false,
            ])

            @component('components.table', [
            'id' => 'purchaseRequestTable',
            'thead' => '
            <tr>
                <th>ID</th>
                <th>Customer Name</th>
                <th>Total Items</th>
                <th>Grand Total</th>
                <th>Date Created</th>
                <th>Action</th>
            </tr>
            '
            ])
            @endcomponent

            @endcomponent
        </div>
    </div>


    @component('components.modal', ['id' => 'viewPRModal', 'size' => 'lg', 'scrollable' => true])
    <div id="prDetails"></div>
    @slot('footer')
    <button type="button" class="btn btn-inverse-secondary" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-inverse-primary" id="sendQuotationBtn">
        <span class="sendQuotationBtn_button_text">Send Quotation</span>
        <span class="sendQuotationBtn_load_data d-none">Loading <i class="loader"></i></span>
    </button>
    @endslot
    @endcomponent

</div>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'salesofficer_pr']) }}"></script>
@endpush