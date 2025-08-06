@php
    $status = $pr->status;
    $badgeClass = 'bg-secondary';
    $badgeText = '';

    if ($status === 'pending') {
        $badgeClass = 'bg-danger';
        $badgeText = 'Pending';
    } elseif ($status === 'quotation_sent') {
        $badgeClass = 'bg-success';
        $badgeText = '<i class="link-icon" data-lucide="check-line" style="font-size: 0.800rem;"></i> Quotation Sent';
    } elseif ($status === 'reject_quotation') {
        $badgeClass = 'bg-secondary';
        $badgeText = '<i class="link-icon" data-lucide="x" style="font-size: 0.800rem;"></i> Quotation Rejected';
    }
@endphp

<h5 class="font-weight-bolder">
    PR ID: {{ $pr->id }}-{{ date('Ymd', strtotime($pr->created_at)) }}
    <span class="badge {{ $badgeClass }} text-white" style="font-size: 0.800rem;">
        {!! $badgeText !!}
    </span>
</h5>


<div class="d-flex justify-content-between mb-3">
    <div>
        <h6 class="lead text-xs mt-2 text-wrap" style="font-size: 0.875rem;">
            <i class="link-icon mb-1" data-lucide="calendar-days"></i>
            {{ date('M j, Y', strtotime($pr->created_at)) }}
        </h6>
    </div>

    <div id="prActions" class="d-flex gap-2">
        <button type="button" class="btn btn-primary" id="sendQuotationBtn">
            <span class="sendQuotationBtn_button_text">Approve</span>
            <span class="sendQuotationBtn_load_data d-none">Loading <i class="loader"></i></span>
        </button>
        <button type="button" class="btn btn-inverse-danger" id="rejectQuotationBtn">
            <span class="rejectQuotationBtn_button_text">Reject</span>
            <span class="rejectQuotationBtn_load_data d-none">Loading <i class="loader"></i></span>
        </button>
    </div>
</div>

<div class="row p-3">
    <div class="col-md-4 bg-light border border-dark p-3" style="border-radius: 0.375rem;">
        <h6 class="lead text-xs mt-2 text-wrap" style="font-size: 0.875rem;">
            <b>Customer:</b> {{ $pr->customer->name }}
        </h6>

        @if($b2bAddress)
            <h6 class="lead text-xs mt-2 text-wrap" style="font-size: 0.875rem;">
                <b>Address:</b> {{ $b2bAddress->full_address ?? 'No address provided' }}
            </h6>
        @endif

        @if($b2bDetails)
            <h6 class="lead text-xs mt-2 text-wrap" style="font-size: 0.875rem;">
                <b>Tin Number:</b> {{ $b2bDetails->tin_number ?? 'No Tin Number provided' }}
            </h6>

            <h6 class="lead text-xs mt-2 text-wrap" style="font-size: 0.875rem;">
                <b>Contact Number:</b> {{ $b2bDetails->contact_number ?? 'No Contact Number provided' }}
            </h6>

            <h6 class="lead text-xs mt-2 text-wrap" style="font-size: 0.875rem;">
                <b>Contact Person:</b> {{ $b2bDetails->contact_person ?? 'No Contact Person provided' }}
            </h6>

            <h6 class="lead text-xs mt-2 text-wrap" style="font-size: 0.875rem;">
                <b>Contact Person Phone #:</b> {{ $b2bDetails->contact_person_number ?? 'No Contact Person Phone Number provided' }}
            </h6>
        @endif
    </div>

    <div class="col-md-8 p-3">

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
                @php
                    $subtotal = 0;
                @endphp
                @foreach($pr->items as $item)
                    @php
                        $itemSubtotal = $item->subtotal;
                        $subtotal += $itemSubtotal;
                    @endphp
                    <tr>
                        <td>
                            <img src="{{ asset(optional($item->product->productImages->first())->image_path ?? 'assets/shop/img/noimage.png') }}"
                                width="50">
                        </td>
                        <td>{{ $item->product->sku }}</td>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>₱{{ number_format($item->product->price, 2) }}</td>
                        <td>₱{{ number_format($itemSubtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>

            @php
                $vatRate = $pr->vat ?? 0;
                $vat = $subtotal * ($vatRate / 100);
                $delivery_fee = $pr->delivery_fee ?? 0;
                $total = $subtotal + $vat + $delivery_fee;
            @endphp

            <tfoot id="totalFooter"
                data-has-fee="{{ !is_null($pr->delivery_fee) && $pr->delivery_fee > 0 ? 'true' : 'false' }}">
                <tr>
                    <td colspan="5" class="text-end"><span class="h6">Subtotal:</span></td>
                    <td class="text-end">₱{{ number_format($subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="text-end"><span class="h6">VAT ({{ $vatRate }}%):</span></td>
                    <td class="text-end">₱{{ number_format($vat, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="text-end"><span class="h6">Delivery Fee:</span></td>
                    <td class="text-end">₱{{ number_format($delivery_fee, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="text-end"><strong class="h4 text-uppercase">Grand Total:</strong></td>
                    <td class="text-end">₱{{ number_format($total, 2) }}</td>
                </tr>
            </tfoot>
        </table>

    </div>

</div>