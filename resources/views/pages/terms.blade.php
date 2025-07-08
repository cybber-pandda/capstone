@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    @include('layouts.dashboard.breadcrumb')

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
            'title' => 'Terms & Condition List',
            'cardtopAddButton' => true,
            'cardtopAddButtonTitle' => 'Add Terms & Condition',
            'cardtopAddButtonId' => 'add',
            'cardtopButtonMode' => 'add'
            ])

            @component('components.table', [
            'id' => 'termsTable',
            'thead' => '
            <tr>
                <th>Page</th>
                <th>Type</th>
                <th>Content</th>
                <th>Date Created</th>
                <th>Action</th>
            </tr>
            '
            ])
            @endcomponent

            @endcomponent
        </div>
    </div>

    @component('components.modal', ['id' => 'termsModal', 'size' => '', 'scrollable' => true])
    <form id="termsForm" action="{{ route('terms.store') }}" method="POST">

        @component('components.input', ['label' => 'Name', 'type' => 'text', 'name' => 'name', 'attributes' => '' ]) @endcomponent
        @component('components.input', ['label' => 'Image', 'type' => 'file', 'name' => 'image', 'attributes' => '' ]) @endcomponent
        @component('components.textarea', ['label' => 'Description', 'rows' => 7, 'name' => 'description', 'attributes' => '']) @endcomponent

    </form>

    @slot('footer')
    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary btn-sm" id="saveTerms">
        <span class="saveTerms_button_text">Save</span>
        <span class="saveTerms_load_data d-none">Loading <i class="loader"></i></span>
    </button>
    @endslot
    @endcomponent

</div>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'terms']) }}"></script>
@endpush