@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">
    @include('layouts.dashboard.breadcrumb')

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
            'title' => 'User List',
            'cardtopAddButton' => false,
            ])

            <ul class="nav nav-tabs nav-tabs-line mb-3" id="statusTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="active-tab" data-bs-toggle="tab" href="#userTableContainer" role="tab"
                        aria-controls="userTableContainer" aria-selected="true" data-status="1">Active</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="inactive-tab" data-bs-toggle="tab" href="#userTableContainer" role="tab"
                        aria-controls="userTableContainer" aria-selected="false" data-status="0">Inactive</a>
                </li>
            </ul>

            <div class="tab-content" id="statusTabsContent">
                <div class="tab-pane fade show active" id="userTableContainer" role="tabpanel" aria-labelledby="active-tab">
                    @component('components.table', [
                    'id' => 'userManagementTable',
                    'thead' => '
                    <tr>
                        <th>Profile</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
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

    @component('components.modal', ['id' => 'userDetailsModal', 'size' => 'lg', 'scrollable' => true])
    <div id="modalContent"></div>
    @slot('footer')
    <button type="button" class="btn btn-inverse-secondary" data-bs-dismiss="modal">Close</button>
    @endslot
    @endcomponent


</div>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'user_management']) }}"></script>
@endpush