@extends('layouts.shop')

@section('content')
<div class="section section-scrollable" style="margin-bottom: 20px;">
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

            @php
            $subtotal = $quotation->items->sum(fn($item) => $item->quantity * $item->product->price);
            $vatRate = $quotation->vat ?? 0;
            $vat = $subtotal * ($vatRate / 100);
            $delivery_fee = $quotation->delivery_fee ?? 0;
            $total = $subtotal + $vat + $delivery_fee;
            @endphp

            <tfoot>
                <tr>
                    <td colspan="4" class="text-end"><strong style="float: right;">Subtotal:</strong></td>
                    <td class="text-end">₱{{ number_format($subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="text-end"><strong style="float: right;">VAT({{$vatRate}}%):</strong></td>
                    <td class="text-end">₱{{ number_format($vat, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="text-end"><strong style="float: right;">Delivery Fee:</strong></td>
                    <td class="text-end">₱{{ number_format($delivery_fee, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="text-end"><strong style="float: right;">Grand Total:</strong></td>
                    <td class="text-end">₱{{ number_format($total, 2) }}</td>
                </tr>
            </tfoot>

        </table>

        <div class="text-end mt-4">
            <button type="button" class="btn btn-success" id="submitQuotationBtn" data-id="{{ $quotation->id }}">
                <i class="fa fa-paper-plane"></i> Submit Purchase Order
            </button>
        </div>

    </div>

    <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="border:0px">
                    <h5 class="modal-title" id="modalTitle">Upload Proof of Payment</h5>
                </div>
                <div class="modal-body">
                    <form id="paymentForm" enctype="multipart/form-data" method="POST" action="{{ route('b2b.quotations.payment.upload') }}">
                        @csrf
                        <input type="hidden" name="quotation_id" id="modal_quotation_id">

                        <div style="margin-bottom:10px;">
                            <label for="bank_id" class="form-label">Select Bank:</label>
                            <select class="form-select form-control" name="bank_id" id="bank_id">
                                <option selected disabled value="">-- Choose a bank --</option>
                                @foreach ($banks as $bank)
                                <option value="{{ $bank->id }}"
                                    data-account="{{ $bank->account_number }}"
                                    data-qr="{{ asset( $bank->image ) }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback bank_id_error text-danger"></div>
                            

                            <div id="bankDetails" class="text-center  d-none" style="margin-top:5px;margin-bottom:5px;">
                                <p class="mb-1"><strong>Account Number:</strong> <span id="accountNumber"></span></p>
                                <img id="qrCodeImage" src="" alt="QR Code" class="img-fluid" style="max-height: 200px;" />
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="proof_payment" class="form-label">Upload Proof:</label>
                            <input type="file" class="form-control" name="proof_payment" id="proof_payment" accept="image/*">
                            <div class="invalid-feedback proof_payment_error text-danger"></div>
                        </div>

                         <div class="mb-3">
                            <label for="reference_number" class="form-label">Reference Number:</label>
                            <input type="text" class="form-control" name="reference_number" id="reference_number" placeholder="Enter reference number">
                            <div class="invalid-feedback reference_number_error text-danger"></div>
                        </div>

                    </form>
                </div>
                <div class="modal-footer" style="border:0px">
                    <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" id="submitPaymentBtn">Submit</button>
                </div>
            </div>
        </div>
    </div>


</div>
@endsection

@push('scripts')
<script>
    $(document).on('click', '#submitQuotationBtn', function() {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Submit and Pay?',
            text: "You're about to submit a Purchase Order. Choose your payment method.",
            icon: 'info',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'Pay Now',
            denyButtonText: 'Pay Later',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                $('#modal_quotation_id').val(id);
                $('#paymentModal').modal('show');
            } else if (result.isDenied) {
                Swal.fire({
                    title: 'Pay Later Confirmation',
                    text: "You have 1 month to complete payment. Your credit limit will be checked.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Confirm Pay Later',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state for exactly 3 seconds
                        Swal.fire({
                            title: 'Processing...',
                            html: 'Submitting your pay later request',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            timer: 3000, // Show for 3 seconds
                            timerProgressBar: true,
                            willOpen: () => Swal.showLoading(),
                            didOpen: () => {
                                // Submit the request after showing loading for 3 seconds
                                setTimeout(() => {
                                    $.ajax({
                                        url: '/b2b/quotations/payment/paylater',
                                        method: 'POST',
                                        data: {
                                            quotation_id: id,
                                            _token: '{{ csrf_token() }}'
                                        },
                                        success: function(response) {
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Success!',
                                                html: `${response.message}<br>Remaining Credit: ₱${response.credit_limit_remaining.toLocaleString()}`,
                                                showConfirmButton: true,
                                                confirmButtonText: 'View Order'
                                            }).then(() => {
                                                window.location.href = `/b2b/quotations/review?track_id=${id}`;
                                            });
                                        },
                                        error: function(error) {
                                            let errorMsg = error.responseJSON?.message || 'Request failed';
                                            if (error.status === 400 && error.responseJSON?.credit_limit_remaining !== undefined) {
                                                errorMsg += `<br>Your credit: ₱${error.responseJSON.credit_limit_remaining.toLocaleString()}`;
                                            }

                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error',
                                                html: errorMsg,
                                                confirmButtonText: 'OK'
                                            });
                                        }
                                    });
                                }, 3000);
                            }
                        });
                    }
                });
            }
        });

    });

    $('#submitPaymentBtn').on('click', function(e) {
        e.preventDefault();

        let form = $('#paymentForm')[0];
        let formData = new FormData(form);

        $$(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...');
        $(this).prop('disabled', true);

        $.ajax({
            url: $(form).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#paymentModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Payment Submitted',
                    html: 'Your proof of payment has been uploaded. Please wait for confirmation.',
                }).then(() => {
                    window.location.href = `/b2b/quotations/review?track_id=${$('#modal_quotation_id').val()}`;
                });
            },
            error: function(xhr) {

                $('#submitPaymentBtn').html('Save Changes').prop('disabled', false);

                if (xhr.status === 422) {
                    // Validation errors
                    let errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        let errorMessage = errors[field][0];
                        $(`#${field}`).addClass('is-invalid');
                        $(`.${field}_error`).text(errorMessage).show();
                    }
                } else {
                   Swal.fire({
                    icon: 'error',
                    title: 'Failed',
                    text: xhr.responseJSON?.message || 'Something went wrong.',
                });
                }
            }
        });
    });
</script>
@endpush