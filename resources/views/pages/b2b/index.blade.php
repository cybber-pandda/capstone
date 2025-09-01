@extends('layouts.shop')

@section('content')

<!-- SECTION -->
<div class="section section-scrollable" style="display:none;">
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
            <h5 class="title" id="hideLimitForMobile">Credit Limit: {{ number_format(Auth::user()->credit_limit, 2) }}</h5>
            <div class="section-nav" style="display: none;">
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

        function fetchProducts(url = "{{ route('home') }}") {
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

        $(document).on('click', '.pending-requirements-btn', function(e) {
            e.preventDefault();

            $(document).ready(function() {
                Swal.fire({
                    title: "Pending Verification",
                    text: "Your B2B account is pending approval.",
                    icon: "info",
                    confirmButtonColor: "#3085d6",
                    confirmButtonText: "OK"
                });
            });

        });

        $(document).on('click', '#search-btn', function(e) {
            e.preventDefault();
            searchQuery = $('#search_value').val();
            fetchProducts();
        });

        // $(document).on('click', '.category-btn', function(e) {
        //     e.preventDefault();

        //     selectedCategory = $(this).data('id');

        //     // Remove .active from all <li>s
        //     $('.main-nav li').removeClass('active');

        //     // Add .active to the clicked link's parent <li>
        //     $(this).closest('li').addClass('active');

        //     fetchProducts();
        // }); 

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
                            <span class="text-danger">₱${parseFloat(product.discounted_price).toFixed(2)}</span>
                            <small class="text-success">(-${product.discount}%)</small>`
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
                        $('#inventory-list').html(`<li><strong>Available Stock:</strong> ${netStock}</li>`);
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

        $(document).on('click', '.purchase-request-btn', function(e) {
            e.preventDefault();

            const productId = $(this).data('id');
            const $quantityInput = $(`#qty-${productId}`);
            const quantity = $quantityInput.val();

            if (!quantity || isNaN(quantity) || parseInt(quantity) <= 0) {
                toast('warning','Please enter a valid quantity greater than 0.');
                return;
            }

            $.ajax({
                url: '/b2b/purchase-requests/store',
                method: 'POST',
                data: {
                    product_id: productId,
                    quantity: quantity
                },
                success: function(response) {
                    toast('success', response.message);
                    $quantityInput.val('');

                    
                    setTimeout(function(){
                       location.reload();
                    }, 3000)

                    // window.purchaseRequestCart = {
                    //     items: response.items,
                    //     total_quantity: response.total_quantity,
                    //     subtotal: response.subtotal
                    // };

                    // updateCartDropdown();

                    // const count = response.response.total_quantity;
                    // const $counter = $('#purchase-request-count');

                    // if (count > 0) {
                    //     $counter.text(count).removeClass('d-none');
                    // } else {
                    //     $counter.text('0').addClass('d-none');
                    // }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        const first = Object.values(errors)[0][0];
                        toast('error', first);
                    } else if (xhr.status === 400) {
                        let message = 'Invalid request';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            try {
                                const res = JSON.parse(xhr.responseText);
                                message = res.message || message;
                            } catch (e) {
                                message = xhr.responseText;
                            }
                        }
                        toast('error', message);
                    } else {
                        toast('error', 'Something went wrong.');
                    }
                }
            });

        });

        $(document).on('click', '.delete-purchase-request', function() {

            alert('test')
            const itemId = $(this).data('id');

            $.ajax({
                url: `/b2b/purchase-requests/items/${itemId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    toast('success', response.message);
                    updateCartDropdown();
                },
                error: function(xhr) {
                    toast('error', 'Failed to delete item.');
                }
            });
        });


    });
</script>
@endpush