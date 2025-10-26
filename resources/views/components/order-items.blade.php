<table class="table table-striped table-2">
    <thead>
        <tr>
            <th>Image</th>
            <th>SKU</th>
            <th>Product</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchaseRequestItems as $item)
        <tr>
            @php
                // Always use the stored unit_price from PR item
                $unitPrice = $item->unit_price ?? 0;
                $subtotal = $item->quantity * $unitPrice;
            @endphp
            <td data-label="Image:">
                <img src="{{ asset(optional($item->product->productImages->first())->image_path ?? 'assets/shop/img/noimage.png') }}" width="50">
            </td>
            <td data-label="SKU:">{{ $item->product->sku ?? 'N/A' }}</td>
            <td data-label="Product:">{{ $item->product->name ?? 'N/A' }}</td>
            <td data-label="Qty:">{{ $item->quantity }}</td>
            <td data-label="Unit Price:">₱{{ number_format($unitPrice, 2) }}</td>
            <td data-label="Subtotal:">₱{{ number_format($subtotal, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
