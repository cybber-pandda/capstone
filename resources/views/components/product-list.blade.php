@if(!empty($data) && count($data) > 0)
<div class="row product-list">
    @foreach ($data as $product)

    @php
    $totalIn = $product->inventories->where('type', 'in')->sum('quantity');
    $totalOut = $product->inventories->where('type', 'out')->sum('quantity');
    $netStock = $totalIn - $totalOut;
    @endphp

    <div class="col-xs-6 col-sm-6 col-md-3">
        <div class="product h-100">
            <div class="product-img-wrapper">
                @if($product->productImages->first())
                <img src="{{ asset($product->productImages->first()->image_path) }}" alt="{{ $product->name }}" style="height:100px;">
                @else
                <img src="{{ asset('assets/dashboard/images/noimage.png') }}" alt="{{ $product->name }}">
                @endif

                @if($product->created_at && \Carbon\Carbon::parse($product->created_at)->gt(now()->subDays(14)))
                <div class="product-label">
                    <span class="new" style="font-size:12px;">NEW</span>
                </div>
                @endif
            </div>

            <div class="product-body">
                <p class="product-category" style="font-size:12px;">{{ $product->category->name ?? 'Uncategorized' }}</p>
                <h6 class="product-name" style="font-size:12px;"><a href="#">{{ $product->name }}</a></h6>
                @if($product->discount == 0)
                <h6 class="product-price" style="margin-bottom:3px;font-size:12px;">₱{{ number_format($product->price, 2) }}</h6>
                @else
                <h6 class="product-price" style="margin-bottom:3px;font-size:12px;">₱{{ number_format($product->discounted_price, 2) }}</h6>
                @endif
                <div class="product-btns">
                    <button class="quick-view" style="margin-bottom:0px;" data-toggle="modal" data-target="#productModal" data-id="{{ $product->id }}">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>

                <input type="text" class="form-control form-control-sm text-center item-qty" id="qty-{{ $product->id }}" placeholder="Enter purchase qty." />
            </div>

            <div class="add-to-cart">
                @if($netStock > 0)
                @auth
                @if($showPendingRequirements)
                <button class="add-to-cart-btn pending-requirements-btn" style="font-size:12px;" data-id="{{ $product->id }}">
                    <i class="fa fa-shopping-cart"></i> Purchase Request
                </button>
                @else
                <button class="add-to-cart-btn purchase-request-btn" style="font-size:12px;" data-id="{{ $product->id }}">
                    <i class="fa fa-shopping-cart"></i> Purchase Request
                </button>
                @endif
                @else
                <button class="add-to-cart-btn guest-purchase-request-btn" style="font-size:12px;" data-id="{{ $product->id }}">
                    <i class="fa fa-shopping-cart"></i> Purchase Request
                </button>
                @endauth
                @else
                <button class="add-to-cart-btn btn-secondary" style="font-size:12px;" disabled>
                    <i class="fa fa-ban"></i> Out of Stock
                </button>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Pagination -->
<div class="col-md-12" id="hidePaginateMobile">
    <div class="text-center">
        <div class="pagination-wrapper">
            {{ $data->links() }}
        </div>
    </div>
</div>

<div class="col-md-12" id="showPaginateMobile">
    <div class="text-center" style="padding: 40px;">
        <div style="position: relative;top:-40px;">{{ $data->links() }}</div>
    </div>
</div>

@else
<div class="col-12 text-center">
    <p>No products available.</p>
</div>
@endif
<script>
    document.querySelectorAll('.item-qty').forEach(input => {
        input.addEventListener('input', function() {
            // Remove all non-digit characters
            this.value = this.value.replace(/\D/g, '');

            // Limit to 5 digits
            if (this.value.length > 6) {
                this.value = this.value.substring(0, 6);
            }
        });
    });
</script>