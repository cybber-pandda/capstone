@extends('layouts.shop')

@section('content')

<!-- SECTION -->
<div class="section" style="display:none;">
    <!-- container -->
    <div class="container">
        <!-- Dynamic Categories Row -->
        <div class="row">
            @foreach($categories as $category)
            <div class="col-md-4 col-xs-6">
                <div class="shop">
                    <div class="shop-img">
                        <img src="{{ asset($category->image ?? 'assets/shop/img/default-category.png') }}" alt="{{ $category->name }}">
                    </div>
                    <div class="shop-body">
                        <h3>{{ $category->name }}<br>Collection</h3>
                        <a href="#" class="cta-btn category-btn" data-id="{{ $category->id }}">Shop now <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

    </div>
    <!-- /container -->
</div>
<!-- /SECTION -->

<!-- SECTION -->
<div class="section section-scrollable">
    <div class="container">
        <div class="section-title">
            <h3 class="title">Tantuco CTC</h3>
            <div class="section-nav">
                <ul class="section-tab-nav tab-nav">
                    <li class="active"><a href="#" class="category-btn" data-id="">All</a></li>
                    @foreach($categories as $category)
                    <li><a href="#" class="category-btn" data-id="{{ $category->id }}">{{ $category->name }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Product List -->
        <div class="row" id="product-list">
            @include('components.product-list', ['data' => $data])
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header" style="border: 0px;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="modal-title">Product Details</h4>
            </div>

            <div class="modal-body" id="modal-body">
                <!-- Filled by AJAX -->
                <div class="text-center">
                    <img id="modal-image" src="" alt="" class="img-responsive" style="margin: 0 auto; max-height: 200px;">
                </div>
                <p><strong>Category:</strong> <span id="modal-category"></span></p>
                <p><strong>Name:</strong> <span id="modal-name"></span></p>
                <p><strong>Price:</strong> ₱<span id="modal-price"></span></p>
                <p><strong>Description:</strong></p>
                <p id="modal-description"></p>
            </div>

            <div class="modal-footer" style="border: 0px;">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let selectedCategory = '';
        let searchQuery = '';

        function fetchProducts(url = "{{ route('welcome') }}") {
            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    search: searchQuery,
                    category_id: selectedCategory
                },
                success: function(response) {
                    $('#product-list').html(response.html);
                },
                error: function(xhr) {
                    console.error('Error fetching products:', xhr);
                }
            });
        }

        $(document).on('click', '#search-btn', function(e) {
            e.preventDefault();
            searchQuery = $('#search_value').val();
            fetchProducts();
        });

        $(document).on('click', '.category-btn', function() {
            selectedCategory = $(this).data('id');

            $('.category-btn').removeClass('active');
            $(this).addClass('active');

            fetchProducts();
        });

        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            fetchProducts(url);
        });

        $(document).on('click', '.quick-view', function() {
            var productId = $(this).data('id');

            $.ajax({
                url: '/products/' + productId, // your endpoint (see controller below)
                type: 'GET',
                success: function(product) {
                    $('#modal-title').text(product.name);
                    $('#modal-image').attr('src', product.image || "{{ asset('assets/dashboard/images/noimage.png') }}");
                    $('#modal-category').text(product.category_name || 'Uncategorized');
                    $('#modal-name').text(product.name);
                    $('#modal-price').text(parseFloat(product.price).toFixed(2));
                    $('#modal-description').text(product.description);
                },
                error: function(xhr) {
                    alert('Error loading product info');
                }
            });
        });

        $(document).on('click', '.guest-purchase-request-btn', function(e) {
            e.preventDefault();
            const productId = $(this).data('id');

            sessionStorage.setItem('pending_cart_product', productId);

            setTimeout(function() {
                window.location.href = "{{ route('login') }}";
            }, 100);
        });
    });
</script>
@endpush