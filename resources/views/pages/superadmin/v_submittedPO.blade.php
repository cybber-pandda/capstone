@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    @include('layouts.dashboard.breadcrumb')

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
            'title' => 'Submitted PO List',
            'cardtopAddButton' => false,
            ])

            @component('components.table', [
            'id' => 'submittedPO',
            'thead' => '
            <tr>
                <th>Customer Name</th>
                <th>Is Credit</th>
                <th>Credit Amount</th>
                <th>Payment Method</th>
                <th>Is COD</th>
                <th>Total Items</th>
                <th>Grand Total</th>
                <!-- <th>Date Created</th> -->
                <th>Action</th>
            </tr>
            '
            ])
            @endcomponent

            @endcomponent
        </div>
    </div>

    @component('components.modal', ['id' => 'viewPRModal', 'size' => 'xl', 'scrollable' => true])
    <div id="prDetails"></div>
    @endcomponent


</div>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'submitted_po']) }}"></script>
@endpush