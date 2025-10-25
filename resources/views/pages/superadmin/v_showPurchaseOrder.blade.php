@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    @include('layouts.dashboard.breadcrumb')

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
                <h4 class="mb-0"><strong>Purchase Order</strong></h4>
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
                <span><b>TIN:</b> {{ $b2bReqDetails->tin_number ?? 'No TIN provided' }}</span>
                <span><b>Business Style:</b> {{ $b2bReqDetails->business_name ?? 'No business style provided' }}</span>
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
                                $unitPrice = $item->unit_price ?? ($item->product->discount > 0 ? $item->product->discounted_price : $item->product->price);
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
                                <td class="text-end">
                                    ₱{{ number_format($itemSubtotal, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    @php
                        $vatRate = $quotation->vat ?? 0;
                        $vat = $subtotal * ($vatRate / 100);
                        $delivery_fee = $quotation->delivery_fee ?? 0;
                        $total = $subtotal + $vat + $delivery_fee;
                        $vatableSales = $subtotal;
                        $amountPaid = 0.00;

                        $b2bDate = $quotation->b2b_delivery_date;
                        $delivery_date = null;

                        if (!is_null($b2bDate)) {
                            $delivery_date = \Carbon\Carbon::parse($b2bDate)->format('F j, Y');
                        } elseif ($quotation->status !== 'pending') {
                            $start = now()->addDays(1)->format('F j, Y');
                            $end   = now()->addDays(3)->format('F j, Y');
                            $delivery_date = $start . ' to ' . $end;
                        }
                    @endphp

                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end">Subtotal:</td>
                            <td class="text-end">₱{{ number_format($subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end">VAT ({{ $vatRate }}%):</td>
                            <td class="text-end">₱{{ number_format($vat, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end">Vatable Sales:</td>
                            <td class="text-end">₱{{ number_format($vatableSales, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end">Delivery Fee:</td>
                            <td class="text-end">₱{{ number_format($delivery_fee, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end">Amount Paid:</td>
                            <td class="text-end">₱{{ number_format($amountPaid, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end">
                                <strong class="fs-5">Grand Total:</strong>
                            </td>
                            <td class="text-end fw-bold">₱{{ number_format($total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <div class="d-flex flex-column">
                    <span class="mb-2">
                        <b>Delivery Date:</b><br>
                        {{ $delivery_date }}
                    </span>
                    <span><b>Payment Terms:</b><br> {{ $quotation->credit == 1 ? '1 month' : 'Cash Payment' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        // Custom JS if needed
    });
</script>
@endpush
