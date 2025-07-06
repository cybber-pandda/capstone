<h5>Order #{{ $order->order_number }}</h5>
<p class="mb-2"><strong>Customer:</strong> {{ $order->user->name ?? 'N/A' }}</p>

<table class="table table-sm">
    <thead>
        <tr>
            <th>Product</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $item)
        <tr>
            <td>{{ $item->product->name ?? 'N/A' }}</td>
            <td>{{ $item->quantity }}</td>
            <td>₱{{ number_format($item->product->price ?? 0, 2) }}</td>
            <td>₱{{ number_format($item->quantity * ($item->product->price ?? 0), 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<h6 class="mt-4 mb-2">Delivery History</h6>
<div class="accordion" id="deliveryHistoryAccordion">
    @forelse($order->delivery->histories as $history)
    <div class="accordion-item">
        <h2 class="accordion-header" id="heading{{ $history->id }}">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapse{{ $history->id }}">
                {{ ucfirst($history->status) }} — {{ $history->logged_at->format('Y-m-d H:i:s') }}
            </button>
        </h2>
        <div id="collapse{{ $history->id }}" class="accordion-collapse collapse"
            data-bs-parent="#deliveryHistoryAccordion">
            <div class="accordion-body">
                <p><strong>Status:</strong> {{ $history->status }}</p>
                <p><strong>Remarks:</strong> {{ $history->remarks ?? 'None' }}</p>
                <p><strong>Logged At:</strong> {{ $history->logged_at }}</p>
            </div>
        </div>
    </div>
    @empty
    <p>No delivery history available.</p>
    @endforelse
</div>