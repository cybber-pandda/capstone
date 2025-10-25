@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">
    <div class="row g-4">

        <!-- Customer Info Column -->
        <div class="col-md-4">
            <div class="p-4 border border-2 border-dark rounded">
                <h3 class="fw-bold text-uppercase mb-3">
                    <i>Tanctuco Construction & Trading Corporation</i>
                </h3>
                <div class="mb-3">
                    <strong>Balubal, Sariaya, Quezon</strong>
                    <div>VAT Reg TIN: {{ $companySettings->company_vat_reg ?? 'No VAT Reg TIN provided' }}</div>
                    <div>Tel: {{ $companySettings->company_tel ?? 'No Tel provided' }}</div>
                    <div>Telefax: {{ $companySettings->company_telefax ?? 'No Telefax provided' }}</div>
                </div>

                <div class="mb-3">
                    <h4 class="mb-1"><strong>Sales Invoice</strong></h4>
                    <div><b>No:</b> {{ $quotation->id ?? 'No PO provided' }}-{{ date('Ymd', strtotime($quotation->created_at)) }}</div>
                    <div><b>Date Issued:</b> {{ $quotation->date_issued ?? 'No date issued provided' }}</div>
                    <div><strong>Disclaimer:</strong>
                        <i>
                            This document is system-generated and provided for internal/business reference only. 
                            It is not BIR-accredited and shall not be considered as an official receipt or invoice 
                            for tax or accounting purposes.
                        </i>
                    </div>
                </div>

            <div style="display: flex; flex-direction: column;margin-bottom:20px;">
                <h4 style="margin-bottom: 0px;"><strong>Billed To</strong></h4>
                <span><b>Name:</b> {{ $quotation->customer->name ?? 'No customer name provided' }}</span>
                <span><b>Address:</b> {{ $b2bAddress->full_address ?? 'No full address provided' }}</span>
                @if(!empty($b2bAddress->address_notes))
                    <span><b>Address Note:</b> {{ $b2bAddress->address_notes }}</span>
                @endif
                <span><b>TIN:</b> {{ $b2bReqDetails->tin_number ?? 'No TIN provided' }}</span>
                <span><b>Business Style:</b> {{ $b2bReqDetails->business_name ?? 'No business style provided' }}</span>
            </div>

                <div>
                    <div class="mb-3"><b>Prepared By:</b><br>{{ $superadmin->name ?? 'No superadmin name provided' }}</div>
                    <div><b>Authorized Representative:</b><br>{{ $salesOfficer->name ?? 'No sales officer name provided' }}</div>
                </div>
            </div>
        </div>

        <!-- Table Column -->
        <div class="col-md-8">
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
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
                        $amountPaid = !empty($paidPR->paid_amount) ? $paidPR->paid_amount : 0.00;

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
                        @if($quotation->payment_method == 'pay_now' &&  $quotation->cod_flg == 1)
                        <tr>
                            <td colspan="4" class="text-right"><span>Amount Type:</span></td>
                            <td class="text-right">Cash on Delivery</td>
                        </tr>
                        @elseif($quotation->payment_method == 'pay_now' &&  $quotation->cod_flg == 0)
                        <tr>
                            <td colspan="4" class="text-right"><span>Amount Paid:</span></td>
                            <td class="text-right">{{ number_format($amountPaid, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="fw-bold">
                            <td colspan="4" class="text-end fs-5">Grand Total:</td>
                            <td class="text-end fs-5">₱{{ number_format($total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div>
                <div class="mb-2">
                    <b>Delivery Date:</b><br>
                    {{ $delivery_date }}
                    @if($show_note)
                        <br><small class="text-muted fst-italic">Note: Expect delay if too many orders since we are preparing it.</small>
                    @endif
                </div>
                <div>
                    <b>Payment Terms:</b><br>
                    {{ $quotation->credit == 1 ? '1 month' : 'Cash Payment' }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
