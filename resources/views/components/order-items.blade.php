<table class="table table-striped table-2">
    <thead>
        <tr>
            <th>Image</th>
            <th>SKU</th>
            <th>Product</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $item)
        <tr>
            @php
                $finalPrice = ($item->product->discount ?? 0) == 0 
                    ? ($item->product->price ?? 0) 
                    : ($item->product->discounted_price ?? $item->product->price ?? 0);
                $subtotal = $item->quantity * $finalPrice;
            @endphp
            <td data-label="Image:">
                <img src="{{ asset(optional($item->product->productImages->first())->image_path ?? 'assets/shop/img/noimage.png') }}" width="50">
            </td>
            <td data-label="SKU:">{{ $item->product->sku ?? 'N/A' }}</td>
            <td data-label="Product:">{{ $item->product->name ?? 'N/A' }}</td>
            <td data-label="Qty:">{{ $item->quantity }}</td>
            <td data-label="Price:">₱{{ number_format($finalPrice, 2) }}</td>
            <td data-label="Subtotal:">₱{{ number_format($subtotal, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
