@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    @include('layouts.dashboard.breadcrumb')

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
            'title' => 'Inventory Report List',
            'cardtopAddButton' => false,
            'cardtopAddButtonTitle' => '',
            'cardtopAddButtonId' => '',
            'cardtopButtonMode' => ''
            ])

            @component('components.table', [
            'id' => 'inventoryReport',
            'thead' => '
            <tr>
                <th>SKU</th>
                <th>Name</th>
                <th>Price</th>
                <th>Stock In</th>
                <th>Stock Out</th>
                <!-- <th>Current Stock</th> -->
                <th>Chart</th>
            </tr>
            '
            ])
            @endcomponent

            @endcomponent
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'inventory_report']) }}"></script>
@endpush