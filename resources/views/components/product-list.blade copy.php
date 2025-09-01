@if(!empty($data) && count($data) > 0)
@foreach ($data as $product)
<div class="col-xs-6 col-sm-6 col-md-3">
    <div class="product">
        <div class="product-img" style="width: 100%; height: 100px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
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
            <p class="product-category" style="font-size:12px;">{{ $product->category->name ?? 'Uncategorized' }}</p>
            <h6 class="product-name" style="font-size:12px;"><a href="#">{{ $product->name }}</a></h6>
            <h6 class="product-price">₱{{ number_format($product->price, 2) }}</h6>
            <div class="product-btns">
                <button class="quick-view" data-toggle="modal" data-target="#productModal" data-id="{{ $product->id }}"><i class="fa fa-eye"></i></button>
            </div>

            <input type="number" id="qty-{{ $product->id }}" class="qty-input form-control" placeholder="Enter purchase qty.">
        </div>

        

        <div class="add-to-cart">
            @auth
                @if($showPendingRequirements)
                    <button class="add-to-cart-btn pending-requirements-btn" data-id="{{ $product->id }}" style="font-size:12px;"><i class="fa fa-shopping-cart"></i> <span style="font-size:13px;">Purchase Request</span></button>
                @else
                    <button class="add-to-cart-btn purchase-request-btn" data-id="{{ $product->id }}" style="font-size:12px;"><i class="fa fa-shopping-cart"></i> <span style="font-size:13px;">Purchase Request</span></button>
                @endif
            @else
                <button class="add-to-cart-btn guest-purchase-request-btn" data-id="{{ $product->id }}" style="font-size:12px;"><i class="fa fa-shopping-cart"></i> <span style="font-size:13px;">Purchase Request</span></button>
            @endauth
        </div>
    </div>
</div>
@endforeach



@else
<div class="col-12 text-center">
    <p>No products available.</p>
</div>
@endif