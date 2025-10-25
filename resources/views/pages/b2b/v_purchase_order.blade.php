@extends('layouts.shop')

@section('content')
<div class="section section-scrollable" style="margin-bottom: 20px;">
    <div class="container">

        <div class="section-title text-center">
            <h3 class="title">{{ $page }}</h3><br>
        </div>

        @if($purchaseRequests->count() > 0)
        <table class="table table-bordered table-hover table-2">
            <thead>
                <tr>
                    <th>PR #</th>
                    <th>Total Items</th>
                    <th>Grand Total</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseRequests as $pr)
                    @php
                        // Compute subtotal using actual unit_price from PurchaseRequestItem
                        $subtotal = $pr->items->sum(function ($item) {
                            $unitPrice = $item->unit_price ?? 0; // use the actual charged price
                            return $item->quantity * $unitPrice;
                        });

                        $vatRate = $pr->vat ?? 0; // VAT percentage
                        $vatAmount = $subtotal * ($vatRate / 100);
                        $deliveryFee = $pr->delivery_fee ?? 0;
                        $total = $subtotal + $vatAmount + $deliveryFee;
                    @endphp
                    <tr>
                        <td data-label="PR #:">{{ $pr->id }}-{{ date('Ymd', strtotime($pr->created_at)) }}</td>
                        <td data-label="QTY:">{{ $pr->items->sum('quantity') }}</td>
                        <td data-label="Grand Total:">₱{{ number_format($total, 2) }}</td>
                        <td data-label="Status:">
                            @php
                                $statusClass = match($pr->status) {
                                'delivered' => 'text-success',
                                'pending' => 'text-warning',
                                default => 'text-secondary',
                            };

                            $statusText = $pr->status === 'Delivery_in_progress' 
                                ? 'Ongoing Delivery' 
                                : ucfirst($pr->status);
                        @endphp

                        <span class="{{ $statusClass }}" style="font-weight:bold; font-size:10px;">
                            {{ $statusText }}
                        </span>
                    </td>
                    <td data-label="Date Created:">{{ $pr->created_at->format('Y-m-d H:i') }}</td>
                    <td style="text-align:center;" data-label="Action:">
                        <a href="{{ route('b2b.purchase.order.show', $pr->id) }}" class="btn btn-info btn-sm">
                            View
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="text-center" style="margin: 40px 0;">
            <p>No purchase order found.</p>
        </div>
        @endif

    </div>
</div>
@endsection


@push('scripts')
<script>
    $(document).ready(function() {

        const checkAddress = '<?php echo $hasAddress ? 'true' : 'false'; ?>';

        if (checkAddress === 'false') {
            Swal.fire({
                title: 'No Address Found',
                text: 'Please add a shipping address before proceeding.',
                icon: 'warning',
                confirmButtonText: 'Add Address',
                showCancelButton: false,
                cancelButtonText: 'Cancel',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/b2b/address';
                }
            });
        }

    });
</script>
@endpush
