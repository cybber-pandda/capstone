<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
</head>

<body>
    <div class="card">
        <div class="card-body">
            <div class="container-fluid d-flex justify-content-between">
                <div class="col-lg-3 ps-0">
                    <h4 class="mt-3 mb-1"><b>{{ config('app.name') }}</b></h4>
                    <p class="mb-1">Your Company Address</p>
                    <h5 class="mt-4 text-secondary">Invoice to:</h5>
                    <p>
                        {{ $pr->customer->name }}<br>
                        {{ $pr->customer->address }}<br>
                        {{ $pr->customer->email }}<br>
                        {{ $pr->customer->phone }}
                    </p>
                </div>
                <div class="col-lg-3 pe-0">
                    <h4 class="text-end mt-4 mb-2">INVOICE</h4>
                    <h6 class="text-end mb-5 pb-4"># INV-{{ str_pad($pr->id, 6, '0', STR_PAD_LEFT) }}</h6>
                    <p class="text-end mb-1">Balance Due</p>
                    <h4 class="text-end">₱
                        {{ number_format($pr->items->sum(fn($item) => $item->quantity * $item->product->price), 2) }}
                    </h4>
                    <h6 class="text-end mt-3"><span class="text-secondary">Invoice Date:</span>
                        {{ $pr->created_at->format('M d, Y') }}</h6>
                    <h6 class="text-end"><span class="text-secondary">Due Date:</span>
                        {{ now()->addDays(7)->format('M d, Y') }}</h6>
                </div>
            </div>

            <div class="container-fluid mt-5 w-100">
                <div class="table-responsive w-100">
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
                            @foreach ($pr->items as $index => $item)
                                <tr class="text-end">
                                    <td class="text-start">{{ $index + 1 }}</td>
                                    <td class="text-start">{{ $item->product->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>₱ {{ number_format($item->product->price, 2) }}</td>
                                    <td>₱ {{ number_format($item->quantity * $item->product->price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @php
                $subtotal = $pr->items->sum(fn($item) => $item->quantity * $item->product->price);
                $tax = $subtotal * 0.12;
                $total = $subtotal + $tax;
            @endphp

            <div class="container-fluid mt-5 w-100">
                <div class="row">
                    <div class="col-md-6 ms-auto">
                        <table class="table">
                            <tr>
                                <td>Sub Total</td>
                                <td class="text-end">₱ {{ number_format($subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td>VAT (12%)</td>
                                <td class="text-end">₱ {{ number_format($tax, 2) }}</td>
                            </tr>
                            <tr class="fw-bold">
                                <td>Total</td>
                                <td class="text-end">₱ {{ number_format($total, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Payment Made</td>
                                <td class="text-end text-danger">(-) ₱ {{ number_format($subtotal, 2) }}</td>
                            </tr>
                            <tr class="bg-light fw-bold">
                                <td>Balance Due</td>
                                <td class="text-end">₱ 0.00</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="container-fluid w-100">
                <a href="javascript:;" onclick="window.print()" class="btn btn-outline-primary float-end mt-4">
                    <i class="me-2" data-lucide="printer"></i>Print
                </a>
            </div>
        </div>
    </div>

</body>

</html>