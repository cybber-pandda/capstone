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

        @component('components.modal', ['id' => 'paymentModal', 'title' => 'Upload Proof of Payment'])
        <form id="paymentForm" enctype="multipart/form-data" method="POST" action="{{ route('b2b.quotations.payment.upload') }}">
            @csrf
            <input type="hidden" name="quotation_id" id="modal_quotation_id">

            <div class="mb-3">
                <label for="bank_id" class="form-label">Select Bank</label>
                <select class="form-select" name="bank_id" id="bank_id" required>
                    @foreach ($banks as $bank)
                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="proof_payment" class="form-label">Upload Proof</label>
                <input type="file" class="form-control" name="proof_payment" id="proof_payment" required accept="image/*">
            </div>

        </form>
        @slot('footer')
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" id="submitPaymentBtn">Submit</button>
        @endslot
        @endcomponent

    </div>
@endsection

@push('scripts')
    <script>
        // $(document).on('click', '#submitQuotationBtn', function() {
        //     const id = $(this).data('id');

        //     Swal.fire({
        //         title: 'Are you sure?',
        //         text: "You are about to submit and pay this purchase order.",
        //         icon: 'warning',
        //         showCancelButton: true,
        //         confirmButtonText: 'Yes, submit it!',
        //     }).then((result) => {
        //         if (result.isConfirmed) {
        //             $.ajax({
        //                 url: `/b2b/quotations/submit/${id}`,
        //                 method: 'POST',
        //                 headers: {
        //                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //                 },
        //                 success: function(response) {
        //                     Swal.fire({
        //                         icon: 'success',
        //                         title: 'Purchase Order Submitted',
        //                         html: 'Please wait for the sales officer to process and deliver to your address.',
        //                         confirmButtonText: 'OK'
        //                     }).then(() => {
        //                         window.location.href = `/b2b/quotations/review?track_id=${id}`;
        //                     });
        //                 },
        //                 error: function(xhr) {
        //                     const response = xhr.responseJSON;
        //                     if (response && response.message) {
        //                         toast('error', response.message);
        //                     } else {
        //                         toast('error', 'An unexpected error occurred. Please try again.');
        //                     }
        //                 }
        //             });
        //         }
        //     });
        // });

        $(document).on('click', '#submitQuotationBtn', function () {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Submit and Pay?',
                text: "You're about to submit a Purchase Order and must upload a proof of payment.",
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Continue to Payment',
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#modal_quotation_id').val(id); // set PR id in hidden input
                    $('#paymentModal').modal('show');
                }
            });
        });

        $('#submitPaymentBtn').on('click', function (e) {
            e.preventDefault();

            let form = $('#paymentForm')[0];
            let formData = new FormData(form);

            $.ajax({
                url: $(form).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#paymentModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Submitted',
                        html: 'Your proof of payment has been uploaded. Please wait for confirmation.',
                    }).then(() => {
                        window.location.href = `/b2b/quotations/review?track_id=${$('#modal_quotation_id').val()}`;
                    });
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: xhr.responseJSON?.message || 'Something went wrong.',
                    });
                }
            });
        });

    </script>
@endpush