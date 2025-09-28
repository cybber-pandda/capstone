@php
    $products = json_decode($order->purchase_request, true) ?? [];
    $total = 0;

    foreach ($products as $p) {
        $lineTotal = $p['qty'] * $p['price'];
        $total += $lineTotal;
    }

    $vatRate = 0.12;
    $vatAmount = $total * $vatRate;

    // Default delivery fee (Quezon Province)
      $deliveryFee = $order->delivery_fee ?? 0;

    $finalTotal = $total + $vatAmount + $deliveryFee;
@endphp

<div style="font-family: Arial, sans-serif; color: #333; line-height: 1.5; padding:20px;">
    <h2 style="color: #2c3e50;">Hello {{ $order->customer_name }}!</h2>

    <p>Thank you for your order. Here are your order details:</p>

    <table cellpadding="8" cellspacing="0" width="100%"
        style="border-collapse: collapse; font-size: 14px; margin-top: 10px; border: 1px solid #ddd;">
        <thead>
            <tr style="background-color: #f4f6f8; text-align: left; border-bottom: 2px solid #ddd;">
                <th style="border: 1px solid #ddd;">Category</th>
                <th style="border: 1px solid #ddd;">Product</th>
                <th style="border: 1px solid #ddd;">Qty</th>
                <th style="border: 1px solid #ddd;">Price</th>
                <th style="border: 1px solid #ddd;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $p)
                @php
                    $categoryName = DB::table('categories')->where('id', $p['category_id'])->value('name');
                    $productName  = DB::table('products')->where('id', $p['product_id'])->value('name');
                    $lineTotal = $p['qty'] * $p['price'];
                @endphp
                <tr>
                    <td style="border: 1px solid #ddd;">{{ $categoryName }}</td>
                    <td style="border: 1px solid #ddd;">{{ $productName }}</td>
                    <td style="border: 1px solid #ddd; text-align: center;">{{ $p['qty'] }}</td>
                    <td style="border: 1px solid #ddd;">₱{{ number_format($p['price'], 2) }}</td>
                    <td style="border: 1px solid #ddd;">₱{{ number_format($lineTotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f9f9f9;">
                <td colspan="4" style="text-align: right; font-weight: bold; border: 1px solid #ddd;">Subtotal</td>
                <td style="font-weight: bold; border: 1px solid #ddd;">₱{{ number_format($total, 2) }}</td>
            </tr>
            <tr>
                <td colspan="4" style="text-align: right; border: 1px solid #ddd;">VAT (12%)</td>
                <td style="border: 1px solid #ddd;">₱{{ number_format($vatAmount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="4" style="text-align: right; border: 1px solid #ddd;">Delivery Fee</td>
                <td style="border: 1px solid #ddd;">₱{{ number_format($deliveryFee, 2) }}</td>
            </tr>
            <tr style="background-color: #f9f9f9;">
                <td colspan="4" style="text-align: right; font-weight: bold; border: 1px solid #ddd;">Grand Total</td>
                <td style="font-weight: bold; border: 1px solid #ddd;">₱{{ number_format($finalTotal, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <p style="margin-top: 20px;">
        <strong>Delivery Address:</strong> {{ $order->customer_address }}
    </p>

    <p style="margin-top: 20px;">Thank you for shopping with us!<br>
    <strong style="color: #2c3e50;">TantucoCTC</strong></p>
</div>
