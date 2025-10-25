@extends($isPdf ? 'layouts.blank' : 'layouts.shop')

@if($isPdf)
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
    }

    .table-bordered {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 10px;
    }

    .table-bordered td,
    .table-bordered th {
        border: 1px solid #000;
        padding: 5px;
    }

    .text-end {
        text-align: right;
    }
</style>
@else
<style>
    body {
        font-family: Arial, sans-serif;
    }
</style>
@endif

@section('content')
<div class="section section-scrollable" style="margin-bottom: 20px;">
    <div class="container">
        <div class="row" id="downloadtoPDF">

            <!-- Customer Info Column -->
            <div class="col-sm-4 col-xs-12"
                style="margin-bottom: 20px; padding:20px; border:2px solid black; border-radius:10px;">
                <h3 style="font-weight:bold; text-transform:uppercase; font-size:21px;">
                    <i>Tantuco Construction & Trading Corporation</i>
                </h3>

                <div style="margin-bottom:10px;">
                    <strong>Balubal, Sariaya, Quezon</strong><br>
                    <span>VAT Reg TIN: {{ $companySettings->company_vat_reg ?? 'N/A' }}</span><br>
                    <span>Tel: {{ $companySettings->company_tel ?? 'N/A' }}</span><br>
                    <span>Telefax: {{ $companySettings->company_telefax ?? 'N/A' }}</span>
                </div>

                <div style="margin-bottom:20px;">
                    <h4 style="margin-bottom: 0;"><strong>Delivery Receipt</strong></h4>
                    <span><b>No:</b> {{ $quotation->id ?? 'N/A' }}-{{ date('Ymd', strtotime($quotation->created_at)) }}</span><br>
                    <span><b>Date Issued:</b> {{ $quotation->date_issued ?? 'N/A' }}</span><br>
                    <strong>Disclaimer:</strong>
                    <i>
                        This document is system-generated and provided for internal/business reference only. 
                        It is not BIR-accredited and shall not be considered as an official receipt or invoice 
                        for tax or accounting purposes.
                    </i>
                </div>

                <div style="margin-bottom:20px;">
                    <h4 style="margin-bottom: 0;"><strong>Billed To</strong></h4>
                    <span><b>Name:</b> {{ $quotation->customer->name ?? 'N/A' }}</span><br>
                    <span><b>Address:</b> {{ $b2bAddress->full_address ?? 'N/A' }}</span><br>
                    @if(!empty($b2bAddress->address_notes))
                        <span><b>Address Note:</b> {{ $b2bAddress->address_notes }}</span><br>
                    @endif
                    <span><b>TIN:</b> {{ $b2bReqDetails->tin_number ?? 'N/A' }}</span><br>
                    <span><b>Business Style:</b> {{ $b2bReqDetails->business_name ?? 'N/A' }}</span>
                </div>

                <div>
                    <span style="margin-bottom:20px; display:block;">
                        <b>Prepared By:</b><br>{{ $superadmin->name ?? 'N/A' }}
                    </span>
                    <span>
                        <b>Authorized Representative:</b><br>{{ $salesOfficer->name ?? 'N/A' }}
                    </span>
                </div>
            </div>

            <!-- Table Column -->
            <div class="col-sm-8 col-xs-12">
                <div style="overflow-x: auto; width: 100%;">
                    <table class="table table-bordered" style="min-width: 600px; margin: 20px 0;">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Product Name</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Unit Price</th>
                                <th class="text-right">Subtotal</th>
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
                        @endphp

                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right">Subtotal:</td>
                                <td class="text-right">{{ number_format($subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right">VAT ({{ $vatRate }}%):</td>
                                <td class="text-right">{{ number_format($vat, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right">Vatable Sales:</td>
                                <td class="text-right">{{ number_format($vatableSales, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right">Delivery Fee:</td>
                                <td class="text-right">{{ number_format($delivery_fee, 2) }}</td>
                            </tr>
                            @if($quotation->payment_method == 'pay_now' && $quotation->cod_flg == 1)
                                <tr>
                                    <td colspan="4" class="text-right">Amount Type:</td>
                                    <td class="text-right">Cash on Delivery</td>
                                </tr>
                            @elseif($quotation->payment_method == 'pay_now' && $quotation->cod_flg == 0)
                                <tr>
                                    <td colspan="4" class="text-right">Amount Paid:</td>
                                    <td class="text-right">{{ number_format($amountPaid, 2) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="4" class="text-right"><strong style="font-size:20px;">Grand Total:</strong></td>
                                <td class="text-right">{{ number_format($total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- Delivery Date and Payment Terms -->
                    <div style="display: flex; flex-direction: column;">
                        <span style="margin-bottom:5px;">
                            <b>Date Delivered:</b><br>
                            @if ($invoiceData->delivery_date)
                                {{ \Carbon\Carbon::parse($invoiceData->delivery_date)->format('F j, Y g:i A') }}
                            @else
                                No delivery date provided
                            @endif
                        </span>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
