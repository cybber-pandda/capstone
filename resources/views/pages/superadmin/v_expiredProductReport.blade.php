@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    @include('layouts.dashboard.breadcrumb')

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
                'title' => 'Expired Product Report',
                'cardtopAddButton' => false,
            ])

            @component('components.table', [
                'id' => 'expiredProductReport',
                'thead' => '
                <tr>
                    <th>SKU</th>
                    <th>Product Name</th>
                    <th>Expiry Date</th>
                    <th>Price</th>
                    <th>Stock In</th>
                    <th>Stock Out</th>
                    <th>Current Stock</th>
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
<script src="{{ route('secure.js', ['filename' => 'expired_product_report']) }}"></script>
@endpush
