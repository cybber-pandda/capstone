@extends('layouts.shop')

@section('content')
<div class="section section-scrollable" style="margin-bottom: 20px;">
    <div class="container">

        <div class="section-title" style="display:none;">
            <h3 class="title">{{ $page }}</h3>
        </div>

        <div class="row" style="margin-bottom: 20px;" id="downloadtoPDF">

            <!-- Customer Info Column -->
            <div class="col-sm-4 col-xs-12" style="margin-bottom: 20px; padding:20px; border:2px solid black; border-radius:10px;">
                <h3 style="font-weight:bold; text-transform:uppercase; font-size:21px;"><i>Tanctuco Construction & Trading Corporation</i></h3>

                <div style="display: flex; flex-direction: column; margin-bottom:10px;">
                    <strong>Balubal, Sariaya, Quezon</strong>
                    <span>VAT Reg TIN: {{ $companySettings->company_vat_reg ?? 'No VAT Reg TIN provided' }}</span>
                    <span>Tel: {{ $companySettings->company_tel ?? 'No Tel provided' }}</span>
                    <span>Telefax: {{ $companySettings->company_telefax ?? 'No Telefax provided' }}</span>
                </div>

                <div style="display: flex; flex-direction: column; margin-bottom:20px;">
                    <h4 style="margin-bottom: 0px;"><strong>Purchase Order</strong></h4>
                    <span><b>No:</b> {{ $quotation->id ?? 'No PO provided' }}-{{ date('Ymd', strtotime($quotation->created_at)) }}</span>
                    <span><b>Date Issued:</b> {{ $quotation->date_issued ?? 'No date issued provided' }}</span>
                    <span><strong>Disclaimer:</strong>
                        <i>This document is system-generated and provided for internal/business reference only. 
                        It is not BIR-accredited and shall not be considered as an official receipt or invoice 
                        for tax or accounting purposes.</i>
                    </span>
                </div>

                <div style="display: flex; flex-direction: column; margin-bottom:20px;">
                    <h4 style="margin-bottom: 0px;"><strong>Billed To</strong></h4>
                    <span><b>Name:</b> {{ $quotation->customer->name ?? 'No customer name provided' }}</span>
                    <span><b>Address:</b> {{ $b2bAddress->full_address ?? 'No full address provided' }}</span>
                    @if(!empty($b2bAddress->address_notes))
                        <span><b>Address Note:</b> {{ $b2bAddress->address_notes }}</span>
                    @endif
                    <span><b>TIN:</b> {{ $b2bReqDetails->tin_number ?? 'No TIN provided' }}</span>
                    <span><b>Business Style:</b> {{ $b2bReqDetails->business_name ?? 'No business style provided' }}</span>
                </div>

                <div style="display: flex; flex-direction: column;">
                    <span style="margin-bottom:20px;"><b>Prepared By:</b><br>{{ $superadmin->name ?? 'No superadmin name provided' }}</span>
                    <span><b>Authorized Representative:</b><br>{{ $salesOfficer->name ?? 'No sales officer name provided' }}</span>
                </div>
            </div>

            <!-- Table Column -->
            <div class="col-sm-8 col-xs-12">
                <div style="overflow-x: auto; width: 100%;">

                    <table class="table table-bordered" style="min-width: 600px; margin-top:20px; margin-bottom:20px;">
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
                            $vatRate = $quotation->vat ?? 0;
                            $vat = $subtotal * ($vatRate / 100);
                            $delivery_fee = $quotation->delivery_fee ?? 0;
                            $total = $subtotal + $vat + $delivery_fee;
                            $vatableSales = $subtotal;
                            $amountPaid = 0.00;

                            // Delivery date logic
                            $b2bDate = $quotation->b2b_delivery_date;
                            $delivery_date = null;
                            $show_note = false;
                            $note_message = '';

                            if (!is_null($b2bDate)) {
                                $delivery_date = \Carbon\Carbon::parse($b2bDate)->format('F j, Y');
                                $diffDays = \Carbon\Carbon::parse($b2bDate)->diffInDays(now());
                                if ($diffDays < 2) {
                                    $show_note = true;
                                    $note_message = "Selected date is preferred only, not guaranteed (due to volume).";
                                }
                            } elseif ($quotation->status !== 'pending') {
                                $start = now()->addDays(1)->format('F j, Y');
                                $end   = now()->addDays(3)->format('F j, Y');
                                $delivery_date = $start . ' to ' . $end;
                                $show_note = true;
                                $note_message = "Expect delay if too many orders since we are preparing it.";
                            }
                        @endphp

                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right"><span>Subtotal:</span></td>
                                <td class="text-right">₱{{ number_format($subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right"><span>VAT ({{ $vatRate }}%):</span></td>
                                <td class="text-right">₱{{ number_format($vat, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right"><span>Vatable Sales:</span></td>
                                <td class="text-right">₱{{ number_format($vatableSales, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right"><span>Delivery Fee:</span></td>
                                <td class="text-right">₱{{ number_format($delivery_fee, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right"><span>Amount Paid:</span></td>
                                <td class="text-right">₱{{ number_format($amountPaid, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right"><strong style="font-size:20px;">Grand Total:</strong></td>
                                <td class="text-right">₱{{ number_format($total, 2) }}</td>
                            </tr>
                        </tfoot>

                    </table>

                    <div style="display: flex; flex-direction: column;">
                        <span style="margin-bottom:5px;">
                            <b>Delivery Date:</b><br>
                            {{ $delivery_date }}
                            @if($show_note && !empty($note_message))
                                <br><small><i>Note: {{ $note_message }}</i></small>
                            @endif
                        </span>
                        <span><b>Payment Terms:</b><br> {{ $quotation->credit == 1 ? '1 month' : 'Cash Payment' }}</span>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
    $(document).ready(function() {
    });
</script>
@endpush