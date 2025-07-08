@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    @include('layouts.dashboard.breadcrumb')

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
            'title' => 'Bank List',
            'cardtopAddButton' => true,
            'cardtopAddButtonTitle' => 'Add Bank',
            'cardtopAddButtonId' => 'add',
            'cardtopButtonMode' => 'add'
            ])

            @component('components.table', [
            'id' => 'bankTable',
            'thead' => '
            <tr>
                <th>QR</th>
                <th>Name</th>
                <th>Account Number</th>
                <th>Date Created</th>
                <th>Action</th>
            </tr>
            '
            ])
            @endcomponent

            @endcomponent
        </div>
    </div>

    @component('components.modal', ['id' => 'bankModal', 'size' => '', 'scrollable' => true])
    <form id="bankForm" action="{{ route('bank-management.store') }}" method="POST">

        @component('components.input', ['label' => 'Name', 'type' => 'text', 'name' => 'name', 'attributes' => '' ]) @endcomponent
        @component('components.input', ['label' => 'Image QR', 'type' => 'file', 'name' => 'image', 'attributes' => '' ]) @endcomponent
        @component('components.input', ['label' => 'Account Number', 'type' => 'text', 'name' => 'account_number', 'attributes' => '' ]) @endcomponent

    </form>

    @slot('footer')
    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary btn-sm" id="saveBank">
        <span class="saveBank_button_text">Save</span>
        <span class="saveBank_load_data d-none">Loading <i class="loader"></i></span>
    </button>
    @endslot
    @endcomponent

</div>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'bank_management']) }}"></script>
@endpush