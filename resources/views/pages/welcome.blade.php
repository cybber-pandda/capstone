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
    <div class="container mt-mobile">
    
        <!-- Product List -->
        <div class="row" id="product-list">
            @include('components.product-list', ['data' => $data])
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document"> <!-- Enlarged modal -->
        <div class="modal-content">

            <div class="modal-header" style="border:0px;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="modal-name">Product Name</h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <!-- Image Gallery -->
                    <div class="col-md-6">
                        <div id="product-images" class="text-center" style="margin-bottom: 15px;">
                            <!-- Main Image -->
                            <img id="modal-image" src="{{ asset('assets/dashboard/images/noimage.png') }}" 
                                 class="img-responsive center-block main-product-image" style="max-height: 300px;" alt="Product Image">
                        </div>
                        <div id="image-thumbnails" class="text-center clearfix" style="margin-bottom: 15px;">
                            <!-- Thumbnails will be appended here by JS -->
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="col-md-6">
                        <p><strong>Category:</strong> <span id="modal-category" class="text-muted"></span></p>
                        <p class="h4" style="margin-top: 15px;" id="modal-price">₱0.00</p>

                        <!-- Ratings -->
                        <div id="modal-rating" style="margin-bottom: 15px;">
                            <!-- Stars & avg rating will be inserted here -->
                        </div>

                        <p><strong>Description:</strong></p>
                        <p id="modal-description" class="text-justify"></p>

                        <!-- Inventory -->
                        <div id="modal-inventory" style="margin-top: 20px;margin-bottom: 15px;">
                            <ul id="inventory-list" class="list-unstyled"></ul>
                        </div>

                        <!-- Reviews -->
                        <div id="modal-reviews" style="margin-top: 20px;">
                            <!-- Reviews will be inserted here -->
                        </div>
                    </div>

                </div>
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

        // SEARCH button click (explicit search)
        $(document).on('click', '#search-btn', function(e) {
            e.preventDefault();
            searchQuery = $('#search_value').val().trim();
            fetchProducts();
        });

        // Live search with debounce (400ms delay)
        //nag add ako ng search mobile
        $('#search_value, #search_value_mobile').on('input', debounce(function() {
            searchQuery = $(this).val().trim();

            if (searchQuery !== '') {
                fetchProducts();
            } else {
                // If the input is cleared, reset to all products
                searchQuery = '';
                fetchProducts();
            }
        }, 400));

        // Debounce helper: waits until user stops typing
        function debounce(fn, delay) {
            let timer;
            return function() {
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, arguments), delay);
            };
        }


        // Category button click — ALWAYS recalculates searchQuery from input before fetching.
        // This ensures clicking "All" (category id = '') will show all items when the search box is empty.
        $(document).on('click', '.category-btn', function(e) {
            e.preventDefault();
            selectedCategory = $(this).data('id');

            $('.category-btn').removeClass('active');
            $(this).addClass('active');

            // read the current search box value (so category respects whether user cleared it)
            searchQuery = $('#search_value').val().trim();

            fetchProducts();
        });
        
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            fetchProducts(url);
        });

       $(document).on('click', '.quick-view', function () {
            var productId = $(this).data('id');

            $.ajax({
                url: '/product/details/' + productId,
                type: 'GET',
                success: function (response) {
                    var product = response.product;

                    // Basic Info
                    $('#modal-title').text(product.name);
                    $('#modal-name').text(product.name);
                    $('#modal-description').text(product.description);
                    $('#modal-category').text(product.category ? product.category.name : 'Uncategorized');

                    // Price + Discount
                    if (product.discount > 0 && product.discounted_price) {
                        $('#modal-price').html(
                            `<span class="text-muted" style="text-decoration:line-through;">₱${parseFloat(product.price).toFixed(2)}</span> 
                            <span style="color:#6571ff ; font-weight:bold;">₱${parseFloat(product.discounted_price).toFixed(2)}</span>
                            <small class="text-success">(-${parseInt(product.discount, 10)}%)</small>`
                        );
                    } else {
                        $('#modal-price').html(`₱${parseFloat(product.price).toFixed(2)}`);
                    }


                    // Average Rating
                    var stars = '';
                    for (var i = 1; i <= 5; i++) {
                        stars += `<span class="fa fa-star${i <= response.average_rating ? '' : '-empty'}"></span>`;
                    }
                    $('#modal-rating').html(`${stars} <small>(${response.average_rating} / 5 from ${response.total_ratings} reviews)</small>`);

                    // Reviews
                    var reviewsContainer = $('#modal-reviews');
                    reviewsContainer.empty();
                    if (product.ratings.length > 0) {
                        product.ratings.forEach(function (review) {
                            reviewsContainer.append(`
                                <div class="review-box" style="border-bottom:1px solid #eee; padding:8px 0;">
                                    <strong>${review.user.name}</strong><br>
                                    <span class="text-warning">${'★'.repeat(review.rating)}${'☆'.repeat(5 - review.rating)}</span>
                                    <p>${review.review ? review.review : ''}</p>
                                </div>
                            `);
                        });
                    } else {
                        reviewsContainer.html('<p class="text-muted">No reviews yet.</p>');
                    }

                    // Show main image if available
                    const mainImage = product.product_images.find(img => img.is_main == 1);
                    if (mainImage) {
                        const imagePath = '/' + mainImage.image_path;
                        $('#modal-image').attr('src', imagePath);
                    } else {
                        $('#modal-image').attr('src', '/assets/dashboard/images/noimage.png');
                    }

                    // Render thumbnails
                    const thumbnailsContainer = $('#image-thumbnails');
                    thumbnailsContainer.empty();
                    product.product_images.forEach(img => {
                        const thumbPath = '/' + img.image_path;
                        const thumbnail = $(`<img src="${thumbPath}" class="img-thumbnail m-1" style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">`);
                        thumbnail.on('click', function () {
                            $('#modal-image').attr('src', thumbPath);
                        });
                        thumbnailsContainer.append(thumbnail);
                    });

                    // Inventory
                    let totalIn = 0, totalOut = 0;
                    if (product.inventories && product.inventories.length > 0) {
                        product.inventories.forEach(function (inv) {
                            if (inv.type === 'in') totalIn += parseInt(inv.quantity);
                            else if (inv.type === 'out') totalOut += parseInt(inv.quantity);
                        });
                        const netStock = totalIn - totalOut;
                        const reserveStock = response.reserve_stock ?? 0;

                        $('#inventory-list').html(`
                            <li><strong>Available Stock:</strong> ${netStock}</li>
                            <li><strong>Reserve Stock:</strong> ${reserveStock}</li>
                        `);
                    } else {
                        $('#inventory-list').html('<li>No inventory info</li>');
                    }

                    // Show modal
                    $('#productModal').modal('show');
                },
                error: function () {
                    toast('error', 'Error loading product info');
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