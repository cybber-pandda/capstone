@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    @include('layouts.dashboard.breadcrumb')

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
            'title' => 'Product List',
            'cardtopAddButton' => true,
            'cardtopAddButtonTitle' => 'Add Product',
            'cardtopAddButtonId' => 'add',
            'cardtopButtonMode' => 'add'
            ])

            @component('components.table', [
            'id' => 'productManagement',
            'thead' => '
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Discount</th>
                <th>Max. Stock</th>
                <th>Crit. Stock(%)</th>
                <th>Stock</th>
                <th>Action</th>
            </tr>
            '
            ])
            @endcomponent

            @endcomponent
        </div>
    </div>

    @component('components.modal', ['id' => 'productModal', 'size' => 'lg', 'scrollable' => true])
    <form id="productForm" action="{{ route('product-management.store') }}" method="POST">
        <div class="row">
            <div class="col-md-6">
                @component('components.input', ['label' => 'Product Name', 'type' => 'text', 'name' => 'name', 'attributes' => '' ]) @endcomponent


                @component('components.select', [
                'label' => 'Category',
                'name' => 'category_id',
                'selected' => old('category_id', ''),
                'options' => $category_select->pluck('name', 'id')->toArray(),
                'attributes' => 'required'
                ])
                @endcomponent

                @component('components.input', ['label' => 'Maximum Stock', 'type' => 'number', 'name' => 'maximum_stock', 'attributes' => '' ]) @endcomponent
            </div>

            <div class="col-md-6 mb-2">
                @component('components.input', ['label' => 'Price', 'type' => 'number', 'name' => 'price', 'attributes' => '' ]) @endcomponent
                @component('components.input', ['label' => 'Discount(%)', 'type' => 'number', 'name' => 'discount', 'attributes' => '' ]) @endcomponent
                @component('components.input', ['label' => 'Critical Stock Level', 'type' => 'number', 'name' => 'critical_stock_level', 'attributes' => '' ]) @endcomponent
            </div>


            <div class="col-md-12">

                @component('components.textarea', ['label' => 'Description', 'rows' => 5, 'name' => 'description', 'attributes' => '']) @endcomponent

                @component('components.input', ['label' => 'Images', 'type' => 'file', 'name' => 'images[]', 'attributes' => 'multiple accept=.webp,.jpg,.png' ]) @endcomponent

                <div id="imagePreviewContainer" class="row mt-3"></div>
                <input type="hidden" name="main_image_index" id="main_image_index" value="0">
            </div>
        </div>
    </form>

    @slot('footer')
    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary btn-sm" id="saveProduct">
        <span class="saveProduct_button_text">Save</span>
        <span class="saveProduct_load_data d-none">Loading <i class="loader"></i></span>
    </button>
    @endslot
    @endcomponent


    @component('components.modal', ['id' => 'viewProductModal', 'size' => 'lg', 'scrollable' => true])
    <div id="productDetails"></div>
    @slot('footer')
    <button type="button" class="btn btn-inverse-secondary" data-bs-dismiss="modal">Close</button>
    @endslot
    @endcomponent

</div>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'product_management']) }}"></script>
@endpush