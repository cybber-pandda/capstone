@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    @include('layouts.dashboard.breadcrumb')

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
            'title' => 'Inventory Management List',
            'cardtopAddButton' => true,
            'cardtopAddButtonTitle' => 'Log Inventory Movement',
            'cardtopAddButtonId' => 'add',
            'cardtopButtonMode' => 'add'
            ])

            @component('components.table', [
            'id' => 'inventoryManagement',
            'thead' => '
            <tr>
                <th>SKU</th>
                <th>Name</th>
                <th>Price</th>
                <th>Stock In</th>
                <th>Stock Out</th>
                <th>Current Stock</th>
                <th>Inventory Breakdown</th>
            </tr>
            '
            ])
            @endcomponent

            @endcomponent
        </div>
    </div>


    @component('components.modal', ['id' => 'inventoryManagementModal', 'size' => '', 'scrollable' => true])
    <form id="inventoryMangementForm" action="{{ route('inventory.store') }}" method="POST">

        @component('components.select', [
            'label' => 'Product',
            'name' => 'product_id',
            'selected' => old('product_id', ''),
            'options' => $product_select->pluck('name', 'id')->toArray(),
            'attributes' => 'required'
        ])
        @endcomponent

        @component('components.input', ['label' => 'Quantity', 'type' => 'number', 'name' => 'quantity', 'attributes' => '' ]) @endcomponent
        
        @component('components.select', [
        'label' => 'Stock Type',
        'name' => 'type',
        'selected' => '',
        'options' => ['in', 'out'],
        'attributes' => ''
        ]) @endcomponent

        @component('components.select', [
        'label' => 'Stock Reason',
        'name' => 'reason',
        'selected' => '',
        'options' => ['restock', 'sold', 'returned', 'damaged', 'stock update', 'other'],
        'attributes' => ''
        ]) @endcomponent

    </form>

    @slot('footer')
    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary btn-sm" id="saveInventory">
        <span class="saveInventory_button_text">Save</span>
        <span class="saveInventory_load_data d-none">Loading <i class="loader"></i></span>
    </button>
    @endslot
    @endcomponent

</div>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'inventory_management']) }}"></script>
@endpush