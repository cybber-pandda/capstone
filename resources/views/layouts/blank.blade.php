<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Invoice</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 30px;
        }
        .company-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-header img {
            max-width: 120px;
            height: auto;
            margin-bottom: 10px;
        }
        .company-header h1 {
            font-size: 18px;
            margin: 5px 0;
        }
        .company-contact {
            font-size: 12px;
        }
        .section {
            border: 1px solid #000;
            padding: 15px;
            margin-bottom: 20px;
        }
        .section h3 {
            font-size: 14px;
            margin: 0 0 10px 0;
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .table-bordered th, .table-bordered td {
            border: 1px solid #000;
            padding: 6px;
        }
        .table-bordered th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals td {
            font-weight: bold;
            padding: 6px;
            font-size: 12px;
        }
        .disclaimer {
            font-size: 10px;
            font-style: italic;
        }
        .small-note {
            font-size: 10px;
            font-style: italic;
        }
    </style>
</head>
<body>
<div class="wrapper">

    <!-- COMPANY HEADER -->
    <div class="company-header">
        <img src="{{ public_path($companySettings->company_logo ?? 'assets/dashboard/images/noimage.png') }}" alt="Company Logo">
        <h1>TANTUCO CONSTRUCTION & TRADING CORPORATION</h1>
        <div class="company-contact">
            Balubal, Sariaya, Quezon<br>
            VAT Reg TIN: {{ $companySettings->company_vat_reg ?? 'N/A' }}<br>
            Tel: {{ $companySettings->company_tel ?? 'N/A' }} / Telefax: {{ $companySettings->company_telefax ?? 'N/A' }}
        </div>
    </div>

    <!-- INVOICE INFO -->
    <div class="section">
        <h3>Sales Invoice</h3>
        <p>
            <strong>No:</strong> {{ $quotation->id ?? 'N/A' }}-{{ date('Ymd', strtotime($quotation->created_at)) }}<br>
            <strong>Date Issued:</strong> {{ $quotation->date_issued ?? now()->toDateString() }}<br>
            <strong>Disclaimer:</strong>
            <span class="disclaimer">
                This document is system-generated and provided for internal/business reference only.
                It is not BIR-accredited and shall not be considered as an official receipt or invoice for tax or accounting purposes.
            </span>
        </p>
    </div>

    <!-- BILLED TO -->
    <div class="section">
        <h3>Billed To</h3>
        <p>
            <strong>Name:</strong> {{ $quotation->customer->name ?? 'N/A' }}<br>
            <strong>Address:</strong> {{ $b2bAddress->full_address ?? 'N/A' }}<br>
            @if(!empty(trim($b2bAddress?->address_notes ?? '')))
                <strong>Address Note:</strong> {{ $b2bAddress->address_notes }}<br>
            @endif
            <strong>TIN:</strong> {{ $b2bReqDetails->tin_number ?? 'N/A' }}<br>
            <strong>Business Style:</strong> {{ $b2bReqDetails->business_name ?? 'N/A' }}
        </p>
        <p>
            <strong>Prepared By:</strong> {{ $superadmin->name ?? 'N/A' }}<br>
            <strong>Authorized Representative:</strong> {{ $salesOfficer->name ?? 'N/A' }}
        </p>
    </div>

    <!-- ITEMS TABLE -->
    <h3>Items</h3>
    <table class="table-bordered">
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
                @php $subtotal = 0; @endphp
                @foreach ($quotation->items as $item)
                    @php
                        // Use unit_price directly from quotation item
                        $unitPrice = $item->unit_price ?? 0;
                        $itemTotal = $unitPrice * $item->quantity;
                        $subtotal += $itemTotal;

                        // Optional: show discount if unit_price < product price
                        $productPrice = $item->product->price ?? 0;
                        $discountPercent = $productPrice > 0
                            ? round((($productPrice - $unitPrice) / $productPrice) * 100)
                            : 0;
                    @endphp

                    <tr>
                        <td>{{ $item->product->sku ?? 'N/A' }}</td>
                        <td>{{ $item->product->name ?? 'N/A' }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">
                            ₱{{ number_format($unitPrice, 2) }}
                        </td>
                        <td class="text-right">₱{{ number_format($itemTotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
    </table>

    <!-- TOTALS -->
    @php
        $vatRate = $quotation->vat ?? 0;
        $vat = $subtotal * ($vatRate / 100);
        $delivery_fee = $quotation->delivery_fee ?? 0;
        $total = $subtotal + $vat + $delivery_fee;
        $amountPaid = $quotation->amount_paid ?? 0;
    @endphp
    <table>
        <tr class="totals">
            <td style="width: 80%;" class="text-right">Subtotal:</td>
            <td class="text-right">₱{{ number_format($subtotal, 2) }}</td>
        </tr>
        <tr class="totals">
            <td class="text-right">VAT ({{ $vatRate }}%):</td>
            <td class="text-right">₱{{ number_format($vat, 2) }}</td>
        </tr>
        <tr class="totals">
            <td class="text-right">Vatable Sales:</td>
            <td class="text-right">₱{{ number_format($subtotal, 2) }}</td>
        </tr>
        <tr class="totals">
            <td class="text-right">Delivery Fee:</td>
            <td class="text-right">₱{{ number_format($delivery_fee, 2) }}</td>
        </tr>
        <tr class="totals">
            <td class="text-right"><strong>Grand Total:</strong></td>
            <td class="text-right"><strong>₱{{ number_format($total, 2) }}</strong></td>
        </tr>
    </table>

    <!-- DELIVERY & PAYMENT -->
    <p>
        <b>Date Delivered:</b>
        @if ($invoiceData->delivery_date)
            {{ \Carbon\Carbon::parse($invoiceData->delivery_date)->format('F j, Y g:i A') }}
        @else
            No delivery date provided
        @endif
    </p>
    <p>
        <strong>Payment Terms:</strong> {{ $quotation->credit == 1 ? '1 month' : 'Cash Payment' }}
    </p>

</div>
</body>
</html>
