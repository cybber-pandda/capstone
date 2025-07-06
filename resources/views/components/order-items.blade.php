<table class="table table-striped">
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
            <td>
                <img src="{{ asset(optional($item->product->productImages->first())->image_path ?? 'assets/shop/img/noimage.png') }}" width="50">
            </td>
            <td>{{ $item->product->sku ?? 'N/A' }}</td>
            <td>{{ $item->product->name ?? 'N/A' }}</td>
            <td>{{ $item->quantity }}</td>
            <td>₱{{ number_format($item->product->price ?? 0, 2) }}</td>
            <td>₱{{ number_format($item->quantity * ($item->product->price ?? 0), 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
