@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">
 
     <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
                'title' => 'Manual Order List',
                'cardtopAddButton' => true,
                'cardtopAddButtonTitle' => 'Send Email Order',
                'cardtopAddButtonId' => 'add',
                'cardtopButtonMode' => 'add'
            ])
                @component('components.table', [
                    'id' => 'manualEmailOrderTable',
                    'thead' => '
                        <tr>
                            <th>Customer Name</th>
                            <th>Customer Type</th>
                            <th>Customer Address</th>
                            <th>Customer Phone #</th>
                            <th>Total Items</th>
                            <th>Grand Total</th>
                            <th>Date Created</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    '
                ])
                @endcomponent
            @endcomponent
        </div>
    </div>

    @component('components.modal', ['id' => 'viewProductModal', 'size' => 'lg', 'scrollable' => true])
    <div id="productDetails"></div>
    @endcomponent

    @component('components.modal', ['id' => 'sendEMailOrderModal', 'size' => 'md', 'scrollable' => true])
    <form id="sendEMailOrderModalForm"  action="{{ route('salesofficer.submit.email-manual.order') }}" method="POST">
         @component('components.input', [
            'label' => 'Customer Email Address',
            'type' => 'email',
            'name' => 'customer_email',
            'attributes' => 'placeholder=\'Enter email address \''
        ]) @endcomponent   
    </form>
    @slot('footer')
    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary btn-sm" id="manulEmailOrderFormbtn">
        <span class="manulEmailOrderFormbtn_button_text">Send Email to Customer</span>
        <span class="manulEmailOrderFormbtn_load_data d-none">Loading <i class="loader"></i></span>
    </button>
    @endslot
    @endcomponent

</div>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'salesofficer_manualemail']) }}"></script>
@endpush