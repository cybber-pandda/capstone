@extends('layouts.shop')

@section('content')
<div class="section">
    <div class="container">

        <div class="section-title">
            <h3 class="title">{{ $page }}</h3>
        </div>

        <div class="mb-4">
            <h5>To:</h5>
            <p>
                <strong>{{ $quotation->customer->name ?? '' }}</strong><br>
                {{ $quotation->customer->email ?? '' }}<br>
                {{ $quotation->customer->phone ?? '' }}<br>
                {{ $quotation->customer->address ?? '' }}
            </p>
        </div>

        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>SKU</th>
                    <th>Product Name</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quotation->items as $item)
                <tr>
                    <td>{{ $item->product->sku }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">₱{{ number_format($item->product->price, 2) }}</td>
                    <td class="text-end">₱{{ number_format($item->quantity * $item->product->price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end"><strong>Grand Total:</strong></td>
                    <td class="text-end">
                        ₱{{ number_format($quotation->items->sum(fn($item) => $item->quantity * $item->product->price), 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="text-end mt-4">
            <button type="button" class="btn btn-success" id="submitQuotationBtn" data-id="{{ $quotation->id }}">
                <i class="fa fa-paper-plane"></i> Submit Purchase Order
            </button>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).on('click', '#submitQuotationBtn', function() {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to submit this purchase order.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, submit it!',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/b2b/quotations/submit/${id}`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Purchase Order Submitted',
                            html: 'Please wait for the sales officer to process and deliver to your address.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = `/b2b/quotations/review?track_id=${id}`;
                        });
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        if (response && response.message) {
                            toast('error', response.message);
                        } else {
                            toast('error', 'An unexpected error occurred. Please try again.');
                        }
                    }
                });
            }
        });
    });
</script>
@endpush