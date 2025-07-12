@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    @include('layouts.dashboard.breadcrumb')

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
            'title' => 'Terms & Condition List',
            'cardtopAddButton' => false,
            ])

            @component('components.table', [
            'id' => 'termsTable',
            'thead' => '
            <tr>
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

    @component('components.modal', ['id' => 'termsModal', 'size' => 'lg', 'scrollable' => true])
    <form id="termsForm" action="{{ route('terms.store') }}" method="POST">

        <!-- @component('components.select', [
        'label' => 'Content Type',
        'name' => 'content_type',
        'selected' => '',
        'options' => ['Terms', 'Condition', 'Policy'],
        'attributes' => ''
        ]) @endcomponent -->

        @component('components.textarea', ['label' => 'Content', 'rows' => 10, 'name' => 'content', 'attributes' => 'id=content']) @endcomponent

    </form>

    @slot('footer')
    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary btn-sm" id="saveTerms">
        <span class="saveTerms_button_text">Save</span>
        <span class="saveTerms_load_data d-none">Loading <i class="loader"></i></span>
    </button>
    @endslot
    @endcomponent

    @component('components.modal', ['id' => 'viewContentModal', 'size' => 'lg', 'scrollable' => true])
    <div id="termContentDetails"></div>
    @endcomponent

</div>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'terms']) }}"></script>
@endpush