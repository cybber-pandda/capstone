@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">


    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">
            ← Back
        </a>
    </div>

    <div class="row mb-4">
        <!-- Customer Info Column -->
        <div class="col-sm-4 col-xs-12 mb-4 p-3 border border-2 rounded">
            <h3 class="fw-bold text-uppercase fs-5">
                <i>Tanctuco Construction & Trading Corporation</i>
            </h3>
            <div class="d-flex flex-column mb-2">
                <strong>Balubal, Sariaya, Quezon</strong>
                <span>VAT Reg TIN: {{ $companySettings->company_vat_reg ?? 'No VAT Reg TIN provided' }}</span>
                <span>Tel: {{ $companySettings->company_tel ?? 'No Tel provided' }}</span>
                <span>Telefax: {{ $companySettings->company_telefax ?? 'No Telefax provided' }}</span>
            </div>

            <div class="d-flex flex-column mb-3">
                <h4 class="mb-0"><strong>Sales Invoice</strong></h4>
                <span><b>No:</b> {{ $quotation->id ?? 'No PO provided' }}-{{ date('Ymd', strtotime($quotation->created_at)) }}</span>
                <span><b>Date Issued:</b> {{ $quotation->date_issued ?? 'No date issued provided' }}</span>
                <span>
                    <strong>Disclaimer:</strong>
                    <i>
                        This document is system-generated and provided for internal/business reference only.
                        It is not BIR-accredited and shall not be considered as an official receipt or invoice
                        for tax or accounting purposes.
                    </i>
                </span>
            </div>

            <div class="d-flex flex-column mb-3">
                <h4 class="mb-0"><strong>Billed To</strong></h4>
                <span><b>Name:</b> {{ $quotation->customer->name ?? 'No customer name provided' }}</span>
                <span><b>Address:</b> {{ $b2bAddress->full_address ?? 'No full address provided' }}</span>
                <span><b>TIN:</b> {{ $b2bReq->tin_number ?? 'No TIN provided' }}</span>
                <span><b>Business Style:</b> {{ $b2bReq->business_name ?? 'No business style provided' }}</span>
            </div>

            <div class="d-flex flex-column">
                <span class="mb-3"><b>Prepared By:</b><br>{{ $superadmin->name ?? 'No superadmin name provided' }}</span>
                <span><b>Authorized Representative:</b><br>{{ $salesOfficer->name ?? 'No sales officer name provided' }}</span>
            </div>
        </div>

        <!-- Table Column -->
        <div class="col-sm-8 col-xs-12">
            <div class="table-responsive">
                <table class="table table-bordered mt-3 mb-3" style="min-width: 600px;">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Product Name</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                        <tbody>
                            @php
                                $subtotal = 0;
                            @endphp
                            @foreach ($quotation->items as $item)
                                @php
                                    $unitPrice = $item->unit_price ?? ($item->product->discounted_price ?? $item->product->price);
                                    $itemSubtotal = $unitPrice * $item->quantity;
                                    $subtotal += $itemSubtotal;
                                @endphp
                                <tr>
                                    <td>{{ $item->product->sku }}</td>
                                    <td>{{ $item->product->name }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">
                                        ₱{{ number_format($unitPrice, 2) }}
                                    </td>
                                    <td class="text-end">₱{{ number_format($itemSubtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>

                    @php
                    $subtotal = $quotation->items->sum('subtotal');
                    $vatRate = $quotation->vat ?? 0;
                    $vat = $subtotal * ($vatRate / 100);
                    $delivery_fee = $quotation->delivery_fee ?? 0;
                    $total = $subtotal + $vat + $delivery_fee;
                    $vatableSales = $subtotal;
                    $amountPaid = 0.00;

                    $isLargeOrder = collect($quotation->items)->sum(fn($item) => $item['quantity']) > 100;
                    $b2bDate = $quotation->b2b_delivery_date;
                    $delivery_date = null;
                    $show_note = false;

                    if (!is_null($b2bDate)) {
                    $delivery_date = \Carbon\Carbon::parse($b2bDate)->format('F j, Y');
                    } elseif ($quotation->status !== 'pending') {
                    if ($isLargeOrder) {
                    $delivery_date = now()->addDays(2)->format('F j, Y') . ' to ' . now()->addDays(3)->format('F j, Y');
                    $show_note = true;
                    } else {
                    $delivery_date = now()->format('F j, Y');
                    }
                    }
                    @endphp

                    <tfoot>
                        <tr>
                            <td colspan="5" class="">Subtotal: ₱{{ number_format($subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="">VAT ({{ $vatRate }}%): ₱{{ number_format($vat, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="">Vatable Sales: ₱{{ number_format($vatableSales, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="">Delivery Fee: ₱{{ number_format($delivery_fee, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="">Amount Paid: ₱{{ number_format($amountPaid, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="">
                                <strong class="fs-5">Grand Total: ₱{{ number_format($total, 2) }}</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <div class="d-flex flex-column">
                    <span class="mb-2">
                        <b>Delivery Date:</b><br>
                        {{ $delivery_date }}
                        @if($show_note)
                        <br><small><i>Note: Expect delay if too many orders since we are preparing it.</i></small>
                        @endif
                    </span>
                    <span><b>Payment Terms:</b><br> {{ $quotation->credit == 1 ? '1 month' : 'Cash Payment' }}</span>
                    <form id="invoiceForm" class="w-100">
                        @csrf
                        <input type="hidden" name="quotation_id" value="{{ $quotation->id ?? '' }}">
                        <button class="mt-4 btn btn-dark w-100" type="button" id="submit_invoice">
                            Submit Invoice
                        </button>
                    </form>
                </div>


            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#submit_invoice').on('click', function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('salesofficer.sales.invoice.submit') }}",
                type: "POST",
                data: $('#invoiceForm').serialize(),
                success: function(response) {
                    if (response.success) {
                        toast('success', response.message);

                        setTimeout(function() {
                            window.location.href = "/salesofficer/sales-invoice/all";
                        }, 3000);
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    if(xhr.status === 404) {
                        alert("Order not found for this quotation ID.");
                    } else if(xhr.responseJSON && xhr.responseJSON.message) {
                        alert("Error: " + xhr.responseJSON.message);
                    } else {
                        alert("Something went wrong. Please try again.");
                    }
                }
            });
        });
    });
</script>
@endpush