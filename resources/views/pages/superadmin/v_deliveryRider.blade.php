@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    @include('layouts.dashboard.breadcrumb')

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
            'title' => 'Delivery Rider List',
            'cardtopAddButton' => true,
            'cardtopAddButtonTitle' => 'Add Delivery Rider',
            'cardtopAddButtonId' => 'add',
            'cardtopButtonMode' => 'add'
            ])

            @component('components.table', [
            'id' => 'deliveryRiderCreation',
            'thead' => '
            <tr>
                <th>Profile</th>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Date Created</th>
                <th>Action</th>
            </tr>
            '
            ])
            @endcomponent

            @endcomponent
        </div>
    </div>

    @component('components.modal', ['id' => 'deliveryRiderModal', 'size' => '', 'scrollable' => true])
    <form id="deliveryRiderForm" action="{{ route('deliveryrider-creation.store') }}" method="POST">

        @component('components.input', ['label' => 'First Name', 'type' => 'text', 'name' => 'firstname', 'attributes' => '' ]) @endcomponent
        @component('components.input', ['label' => 'Last Name', 'type' => 'text', 'name' => 'lastname', 'attributes' => '' ]) @endcomponent
        @component('components.input', ['label' => 'Username', 'type' => 'text', 'name' => 'username', 'attributes' => '' ]) @endcomponent
        @component('components.input', ['label' => 'Email Address', 'type' => 'email', 'name' => 'email', 'attributes' => '' ]) @endcomponent
        @component('components.input', [
            'label' => 'Password',
            'type' => 'password',
            'name' => 'password',
            'attributes' => 'id=password'
        ])
        @endcomponent

        <div class="mt-2">
            <button type="button" id="togglePassword" class="btn btn-sm btn-secondary">
                Show Password
            </button>
        </div>

    </form>

    @slot('footer')
    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary btn-sm" id="saveDeliveryRider">
        <span class="saveDeliveryRider_button_text">Save</span>
        <span class="saveDeliveryRider_load_data d-none">Loading <i class="loader"></i></span>
    </button>
    @endslot
    @endcomponent

</div>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'delivery_rider']) }}"></script>
@endpush