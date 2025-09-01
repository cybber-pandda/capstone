<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Quotation</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 30px;
        }

        .company-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .company-header img {
            max-width: 100px;
            height: auto;
            margin-bottom: 10px;
        }

        .section {
            border: 1px solid #000;
            padding: 15px;
            margin-bottom: 20px;
        }
        .section h3 {
            margin: 0 0 10px 0;
            font-size: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-bordered th, .table-bordered td {
            border: 1px solid #000;
            padding: 5px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals td {
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="company-header">
         <img src="{{ public_path($companySettings->company_logo ?? 'assets/dashboard/images/noimage.png') }}" alt="Company Logo" width="100">
        TANCTUCO CONSTRUCTION & TRADING CORPORATION<br>
        Balubal, Sariaya, Quezon<br>
        VAT Reg TIN: {{ $companySettings->company_vat_reg ?? 'N/A' }}<br>
        Tel: {{ $companySettings->company_tel ?? 'N/A' }} /
        Telefax: {{ $companySettings->company_telefax ?? 'N/A' }}
    </div>

    <div class="section">
        <h3>Purchase Quotation</h3>
        <p>
            <strong>No:</strong> {{ $quotation->id ?? 'N/A' }}-{{ date('Ymd', strtotime($quotation->created_at)) }}<br>
            <strong>Date Issued:</strong> {{ $quotation->date_issued ?? now()->toDateString() }}<br>
            <strong>Disclaimer:</strong>
            <i>
                This document is system-generated and provided for internal/business reference only. 
                It is not BIR-accredited and shall not be considered as an official receipt or invoice 
                for tax or accounting purposes.
            </i>
        </p>
    </div>

    <div class="section">
        <h3>Billed To</h3>
        <p>
            <strong>Name:</strong> {{ $quotation->customer->name ?? 'N/A' }}<br>
            <strong>Address:</strong> {{ $b2bAddress->full_address ?? 'N/A' }}<br>
            <strong>TIN:</strong> {{ $b2bReqDetails->tin_number ?? 'N/A' }}<br>
            <strong>Business Style:</strong> {{ $b2bReqDetails->business_name ?? 'N/A' }}
        </p>
        <p>
            <strong>Prepared By:</strong> {{ $superadmin->name ?? 'N/A' }}<br>
            <strong>Authorized Representative:</strong> {{ $salesOfficer->name ?? 'N/A' }}
        </p>
    </div>

    <h3>Quotation Items</h3>
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
                    $itemTotal = $item->quantity * $item->product->price;
                    $subtotal += $itemTotal;
                @endphp
                <tr>
                    <td>{{ $item->product->sku }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">₱{{ number_format($item->product->price, 2) }}</td>
                    <td class="text-right">₱{{ number_format($itemTotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $vatRate = $quotation->vat ?? 0;
        $vat = $subtotal * ($vatRate / 100);
        $delivery_fee = $quotation->delivery_fee ?? 0;
        $total = $subtotal + $vat + $delivery_fee;
        $amountPaid = 0;
    @endphp

    <table style="margin-top: 15px;">
        <tr class="totals">
            <td style="width: 80%;" class="text-right">Subtotal:</td>
            <td class="text-right">₱{{ number_format($subtotal, 2) }}</td>
        </tr>
        <tr class="totals">
            <td class="text-right">VAT ({{ $vatRate }}%):</td>
            <td class="text-right">₱{{ number_format($vat, 2) }}</td>
        </tr>
        <tr class="totals">
            <td class="text-right">Delivery Fee:</td>
            <td class="text-right">₱{{ number_format($delivery_fee, 2) }}</td>
        </tr>
        <tr class="totals">
            <td class="text-right">Amount Paid:</td>
            <td class="text-right">₱{{ number_format($amountPaid, 2) }}</td>
        </tr>
        <tr class="totals">
            <td class="text-right"><strong>Grand Total:</strong></td>
            <td class="text-right"><strong>₱{{ number_format($total, 2) }}</strong></td>
        </tr>
    </table>

    <p style="margin-top: 20px;">
        <strong>Delivery Date:</strong> {{ $quotation->b2b_delivery_date ?? now()->toFormattedDateString() }}<br>
        <strong>Payment Terms:</strong> {{ $quotation->credit == 1 ? '1 month' : 'Cash Payment' }}
    </p>
</div>

</body>
</html>
