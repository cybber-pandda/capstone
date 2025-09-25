@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">
    @if(Auth::user()->role === 'superadmin')
    @include('layouts.dashboard.breadcrumb')
    @endif

    @php
    $isSuperadmin = Auth::user()->role === 'superadmin';
    @endphp

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @if(Auth::user()->role === 'superadmin')
            @component('components.card', [
            'title' => 'B2B List',
            'cardtopAddButton' => true,
            'cardtopAddButtonTitle' => 'Add B2B',
            'cardtopAddButtonId' => 'add',
            'cardtopButtonMode' => 'add'
            ])
            @elseif(Auth::user()->role === 'salesofficer')
            @component('components.card', [
            'title' => 'B2B List',
            'cardtopAddButton' => false
            ])
            @endif

          

            @component('components.table', [
            'id' => 'B2BCreation',
            'thead' => '
            <tr>
                <th>Profile</th>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Date Created</th>
                ' . ($isSuperadmin ? '<th>Action</th>' : '') . '
            </tr>
            '
            ])
            @endcomponent

            @endcomponent
        </div>
    </div>

    @component('components.modal', ['id' => 'B2BModal', 'size' => '', 'scrollable' => true])
    <form id="B2BForm" action="{{ route('b2b-creation.store') }}" method="POST">
        <!-- @component('components.input', ['label' => 'Credit Limit', 'type' => 'text', 'name' => 'creditlimit', 'attributes' => '' ]) @endcomponent -->
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
    <button type="button" class="btn btn-primary btn-sm" id="saveB2B">
        <span class="saveB2B_button_text">Save</span>
        <span class="saveB2B_load_data d-none">Loading <i class="loader"></i></span>
    </button>
    @endslot
    @endcomponent

</div>
@endsection

@push('scripts')
<script>
const superadmin = <?php echo $isSuperadmin ? 1 : 0 ; ?>
</script>
<script src="{{ route('secure.js', ['filename' => 'b2b']) }}"></script>
@endpush