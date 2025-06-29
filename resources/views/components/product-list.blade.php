@foreach ($data as $product)
<div class="col-md-3 col-sm-6 mb-4">
    <div class="product">
        <div class="product-img" style="width: 100%; height: 200px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
            @if($product->productImages->first())
            <img src="{{ asset($product->productImages->first()->image_path) }}" alt="{{ $product->name }}" style="max-height: 100%; max-width: 100%; object-fit: cover;">
            @else
            <img src="{{ asset('assets/dashboard/images/noimage.png') }}" alt="{{ $product->name }}" style="max-height: 100%; max-width: 100%; object-fit: cover;">
            @endif

            @if($product->created_at && \Carbon\Carbon::parse($product->created_at)->gt(now()->subDays(14)))
            <div class="product-label">
                <span class="new">NEW</span>
            </div>
            @endif
        </div>

        <div class="product-body">
            <p class="product-category">{{ $product->category->name ?? 'Uncategorized' }}</p>
            <h3 class="product-name"><a href="#">{{ $product->name }}</a></h3>
            <h4 class="product-price">₱{{ number_format($product->price, 2) }}</h4>
            <div class="product-btns">
                <!-- <button class="add-to-wishlist"><i class="fa fa-heart-o"></i></button>
                <button class="add-to-compare"><i class="fa fa-exchange"></i></button> -->
                <button class="quick-view" data-toggle="modal" data-target="#productModal" data-id="{{ $product->id }}"><i class="fa fa-eye"></i></button>
            </div>
        </div>
        <div class="add-to-cart">
            @auth
            <button class="add-to-cart-btn" data-id="{{ $product->id }}"><i class="fa fa-shopping-cart"></i> add to cart</button>
            @else
            <button class="add-to-cart-btn guest-cart-btn" data-id="{{ $product->id }}"><i class="fa fa-shopping-cart"></i> add to cart</button>
            @endauth
        </div>
    </div>
</div>
@endforeach

<!-- Pagination -->
<div class="col-md-12">
    <div class="text-center">
        <div class="pagination-wrapper">
            {{ $data->links() }}
        </div>
    </div>
</div>