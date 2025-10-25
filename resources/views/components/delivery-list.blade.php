@if(!empty($deliveries) && $deliveries->count() > 0)
<!-- <div class="table-responsive"> -->
    <table class="table-2">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Address</th>
                <th>Status</th>
                <th>Total Items</th>
                <th>Total Amount</th>
                <th>Items</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deliveries as $delivery)

            @php
            $fullAddress = $delivery->b2bAddress->full_address ?? 'N/A';
            $addressNotes = $delivery->b2bAddress->address_notes ?? '';
            $shortAddress = strlen($fullAddress) > 20 ? substr($fullAddress, 0, 20) . '...' : $fullAddress;

            // ✅ Correct: get subtotal from PurchaseRequest items, not current product prices
            $subtotal = 0;
            $vatRate = 0;
            $deliveryFee = 0;

            if (preg_match('/REF (\d+)-/', $delivery->order_number, $matches)) {
                $purchaseRequestId = $matches[1];
                $purchaseRequest = \App\Models\PurchaseRequest::find($purchaseRequestId);

                if ($purchaseRequest) {
                    // Sum the stored subtotal from purchase_request_items
                    $subtotal = \DB::table('purchase_request_items')
                        ->where('purchase_request_id', $purchaseRequestId)
                        ->sum('subtotal');

                    $vatRate = $purchaseRequest->vat ?? 0;
                    $deliveryFee = $purchaseRequest->delivery_fee ?? 0;
                }
            }

            $vat = $subtotal * ($vatRate / 100);
            $grandTotal = $subtotal + $vat + $deliveryFee;
            @endphp

            <tr>
                <td data-label="Order #:">{{ $delivery->order_number }}</td>
                <td data-label="Customer:">{{ $delivery->user->name ?? 'N/A' }}</td>
                <td data-label="Address:">
                    <span class="view-full-address text-primary" style="cursor:pointer;" data-address="{{ e($fullAddress) }} {{ e($addressNotes) }}" title="Click to view full address">
                        {{ e($shortAddress) }}
                    </span>
                </td>
                <td data-label="Status:">
                    <span class="badge bg-warning">
                        {{ $delivery->delivery->status ?? 'N/A' }}
                    </span>
                </td>
                <td data-label="Total Items:">{{ $delivery->items->sum('quantity') }}</td>
                <td data-label="Total Amount:">₱{{ number_format($grandTotal, 2) }}</td>
                <td data-label="Items:">
                    <ul class="mb-0 delivery-list">
                        @foreach($delivery->items as $item)
                        <li>{{ $item->product->name ?? 'Unknown Product' }} x{{ $item->quantity }}</li>
                        @endforeach
                    </ul>
                </td>
                <td data-label="Action:">
                    <button
                        type="button"
                        class="btn btn-sm btn-inverse-primary pickup-btn"
                        data-delivery-id="{{ $delivery->delivery->id ?? '' }}">
                        Pick Up
                    </button>
                </td>
            </tr>
            @endforeach

        </tbody>
    </table>
<!-- </div> -->
@else
<div class="text-center mb-3">No deliveries assigned to you.</div>
@endif