@extends($isPdf ? 'layouts.blank' : 'layouts.shop') {{-- use minimal layout for PDF --}}

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

        <div style="display: flex; justify-content: space-between;">
            <div>
                <h2>{{ config('app.name') }}</h2>
                <p>{{ $companySettings->company_address ?? '' }}</p>
                <h4>Invoice to:</h4>
                <p>
                    {{ optional($invoiceData->order->user)->name }}<br>
                    {{ optional($invoiceData->order->b2bAddress)->full_address }}<br>
                    {{ optional($invoiceData->order->user)->email }}<br>
                </p>
            </div>

            <div style="text-align: right;">
                <h3>INVOICE</h3>
                <h5># {{ $invoiceData->order?->order_number }}</h5>
                <p><strong>Invoice Date:</strong> {{ $invoiceData->created_at->format('M d, Y') }}</p>
                <p><strong>Due Date:</strong> {{ now()->addDays(7)->format('M d, Y') }}</p>
            </div>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th class="text-end">Quantity</th>
                    <th class="text-end">Unit Cost</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoiceData->order->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-end">{{ $item->quantity }}</td>
                    <td class="text-end">{{ number_format($item->product->price, 2) }}</td>
                    <td class="text-end">{{ number_format($item->quantity * $item->product->price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @php
        $subtotal = $invoiceData->order->items->sum(fn($item) => $item->quantity * $item->product->price);
        $vatRate = $purchaseRequest?->vat ?? 0;
        $vat = $subtotal * ($vatRate / 100);
        $delivery_fee = $purchaseRequest?->delivery_fee ?? 0;
        $total = $subtotal + $vat + $delivery_fee;
        @endphp

        <table class="table table-bordered" style="width: 50%;float:right">
            <tr>
                <td>Sub Total</td>
                <td class="text-end">{{ number_format($subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>VAT({{ $vatRate}}%)</td>
                <td class="text-end">{{ number_format($vat, 2) }}</td>
            </tr>
            <tr>
                <td>Delivery Fee</td>
                <td class="text-end">{{ number_format($delivery_fee, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Total</strong></td>
                <td class="text-end"><strong>{{ number_format($total, 2) }}</strong></td>
            </tr>
            <!-- <tr>
                <td>Payment Made</td>
                <td class="text-end text-danger">(-) {{ number_format($subtotal, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Balance Due</strong></td>
                <td class="text-end">0.00</td>
            </tr> -->
        </table>

        @unless($isPdf)
        <div style="clear: both; margin-top: 40px;">
            <a href="{{ route('b2b.delivery.invoice.download', $invoiceData->id) }}" class="btn btn-primary">Download PDF</a>
        </div>
        @endunless

    </div>
</div>
@endsection