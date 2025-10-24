@extends('layouts.shop')

@section('content')
<div class="section section-scrollable" style="margin-bottom: 20px;">
    <div class="container">

        <div class="section-title" style="display:none;">
            <h3 class="title">{{ $page }}</h3>
        </div>

        <div class="row" style="padding:5px;" id="downloadtoPDF">
            <!-- Customer Info Column -->
            <div class="col-sm-4 col-xs-12" style="margin-bottom: 5px;padding:20px;border:2px solid black;border-radius:10px;">
                <h3 style="font-weight:bold;text-transform:uppercase;font-size:16px;"><i>Tanctuco Construction & Trading Corporation</i></h3>
                <div style="display: flex; flex-direction: column;margin-bottom:10px;">
                    <strong>Balubal, Sariaya, Quezon</strong>
                    <span>VAT Reg TIN: {{ $companySettings->company_vat_reg ?? 'No VAT Reg TIN provided' }}</span>
                    <span>Tel: {{ $companySettings->company_tel ?? 'No Tel provided' }}</span>
                    <span>Telefax: {{ $companySettings->company_telefax ?? 'No Telefax provided' }}</span>
                </div>

                <div style="display: flex; flex-direction: column;margin-bottom:20px;">
                    <h4 style="margin-bottom: 0px;"><strong>Purchase Quotation</strong></h4>
                    <span><b>No:</b> {{ $quotation->id ?? 'No PO provided' }}-{{ date('Ymd', strtotime($quotation->created_at)) }}</span>
                    <span><b>Date Issued:</b> {{ $quotation->date_issued ?? 'No date issued provided' }}</span>
                    <span style="font-size:12px;">
                        <strong>Disclaimer:</strong>
                        <i>
                            This document is system-generated and provided for internal/business reference only.
                            It is not BIR-accredited and shall not be considered as an official receipt or invoice
                            for tax or accounting purposes.
                        </i>
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
                    <span><b>Authorized Representative:</b><br> {{ $salesOfficer->name ?? 'No sales officer name provided' }}</span>
                </div>

            </div>

            <!-- Table Column -->
            <div class="col-sm-8 col-xs-12">
                <div style="overflow-x: auto; width: 100%;">

                    @php
                        $subtotal = $quotation->items->sum('subtotal');
                        $vatRate = $quotation->vat ?? 0;
                        $vat = $subtotal * ($vatRate / 100);
                        $delivery_fee = $quotation->delivery_fee ?? 0;
                        $total = $subtotal + $vat + $delivery_fee;
                        $vatableSales = $subtotal;
                        $amountPaid = 0.00;

                        $b2bDate = $quotation->b2b_delivery_date;
                        $delivery_date = null;
                        $show_note = false;

                        if (!is_null($b2bDate)) {
                            // User chose a delivery date
                            $delivery_date = \Carbon\Carbon::parse($b2bDate)->format('F j, Y');

                            // Check if chosen date is less than 2 days from today
                            $diffDays = \Carbon\Carbon::parse($b2bDate)->diffInDays(now());

                            if ($diffDays < 2) {
                                $show_note = true;
                                $note_message = "Selected date is preferred only, not guaranteed (due to volume).";
                            }
                        } elseif ($quotation->status !== 'pending') {
                            // No date chosen → default 1 to 3 days
                            $start = now()->addDays(1)->format('F j, Y');
                            $end   = now()->addDays(3)->format('F j, Y');
                            $delivery_date = $start . ' to ' . $end;
                            $show_note = true;
                            $note_message = "Expect delay if too many orders since we are preparing it.";
                        }

                        /*
                        // OLD CODE with quantity condition
                        elseif ($quotation->status !== 'pending') {
                            if ($isLargeOrder) {
                                $start = now()->addDays(7)->format('F j, Y');
                                $end   = now()->addDays(14)->format('F j, Y');
                                $delivery_date = $start . ' to ' . $end;
                            } else {
                                $start = now()->addDays(2)->format('F j, Y');
                                $end   = now()->addDays(7)->format('F j, Y');
                                $delivery_date = $start . ' to ' . $end;
                            }
                            $show_note = true;
                        }
                        */
                    @endphp



                    <table class="table table-bordered" style="min-width: 600px;margin-top: 10px;margin-bottom:10px;font-size:12px;">
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
                            @foreach ($quotation->items as $item)
                            @php
                            $price = $item->product->discount == 0 ? $item->product->price : $item->product->discounted_price;
                            @endphp
                            <tr>
                                <td>{{ $item->product->sku }}</td>
                                <td>{{ $item->product->name }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-right">
                                    ₱{{ number_format($price, 2) }}
                                    @if($item->product->discount > 0)
                                        <br>
                                        <small class="text-success">({{ $item->product->discount }}% off)</small>
                                    @endif
                                </td>
                                <td class="text-right">₱{{ number_format($item->quantity * $price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>



                        <tfoot style="font-size: 12px;">
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

                    <div style="display: flex; justify-content:space-between;font-size:12px;margin-bottom:60px;">
                        <span style="margin-bottom:5px;">
                            <b>Delivery Date:</b><br>
                            {{ $delivery_date }}
                                @if($show_note && !empty($note_message))
                                <br><small><i>Note: {{ $note_message }}</i></small>
                                @endif
                    </div>

                </div>
                <div class="text-right" style="margin-top: 10px;margin-bottom: 60px; display: flex; justify-content: relative; gap:3px">
                    
                    <a href="{{ route('b2b.quotation.download', ['id' => $quotation->id]) }}" 
                    class="btn btn-download-pdf">
                        <i class="fa fa-file-pdf"></i> Download PDF
                    </a>


                    <button type="button" 
                            class="btn btn-accept-quotation" 
                            id="submitQuotationBtn" 
                            data-id="{{ $quotation->id }}" 
                            data-totalpayment="{{ $total }}">
                        <i class="fa fa-check"></i> Accept
                    </button>

                    <button type="button" 
                            class="btn btn-cancel-quotation cancel-pr-btn" 
                            data-id="{{ $quotation->id }}">
                        <i class="fa fa-xmark"></i> Cancel
                    </button>

                </div>

            </div>
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

                        {{-- COD Dropdown --}}
                        <div style="margin-bottom:10px;">
                            <label for="cod_flg" class="form-label">Payment Method:</label>
                            <select name="cod_flg" id="cod_flg" class="form-control">
                                <option value="" selected disabled>-- Select Payment Method --</option>
                                <option value="1">Cash or Cheque</option>
                                <!-- <option value="0">Bank Transfer</option> -->
                            </select>
                            <div class="invalid-feedback cod_flg_error text-danger"></div>
                        </div>

                        {{-- Bank Transfer Details (shown only if cod_flg = 0) --}}
                        <div id="bankTransferSection" class="d-none">
                            <div style="margin-bottom:10px;">
                                <label for="bank_id" class="form-label">Select Bank:</label>
                                <select class="form-select form-control" name="bank_id" id="bank_id">
                                    <option selected disabled value="">-- Choose a bank --</option>
                                    @foreach ($banks as $bank)
                                    <option value="{{ $bank->id }}"
                                        data-account="{{ $bank->account_number }}"
                                        data-qr="{{ asset($bank->image) }}">{{ $bank->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback bank_id_error text-danger"></div>

                                <div id="bankDetails" class="text-center d-none" style="margin-top:5px;margin-bottom:5px;">
                                    <p class="mb-1"><strong>Account Number:</strong> <span id="accountNumber"></span></p>
                                    <img id="qrCodeImage" src="" alt="QR Code" class="img-fluid" style="max-height: 200px;" />
                                </div>
                            </div>

                            <div style="margin-bottom:10px;">
                                <label for="paid_amount" class="form-label">Paid Amount:</label>
                                <input type="number" class="form-control" name="paid_amount" id="paid_amount" placeholder="Enter paid amount">
                                <div class="invalid-feedback paid_amount_error text-danger"></div>
                            </div>

                            <div style="margin-bottom:10px;">
                                <label for="proof_payment" class="form-label">Upload Proof:</label>
                                <input type="file" class="form-control" name="proof_payment" id="proof_payment" accept="image/*">
                                <div class="invalid-feedback proof_payment_error text-danger"></div>
                            </div>

                            <div style="margin-bottom:10px;">
                                <label for="reference_number" class="form-label">Reference Number:</label>
                                <input type="text" class="form-control" name="reference_number" id="reference_number" placeholder="Enter reference number">
                                <div class="invalid-feedback reference_number_error text-danger"></div>
                            </div>
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

    <!-- Cancel Modal -->
    <div class="modal fade" id="cancelPRModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="border:0px">
                    <h5 class="modal-title" id="modalTitle">Cancel Quotation</h5>
                </div>
                <div class="modal-body">
                    <form id="cancelPRForm">
                        @csrf
                        <input type="hidden" name="quotation_id" id="cancelQuotationId">
                        <div class="mb-3">
                            <label for="cancelRemarks" class="form-label">Remarks (optional)</label>
                            <textarea name="remarks" id="cancelRemarks" class="form-control" rows="4" placeholder="Reason for cancellation..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border:0px">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger" id="confirmCancelPRBtn">
                        Confirm Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>


</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // $(document).on('click', '#submitQuotationBtn', function() {
        //     const id = $(this).data('id');

        //     Swal.fire({
        //         title: 'Submit and Pay?',
        //         text: "You're about to submit a Purchase Order. Choose your payment method.",
        //         icon: 'info',
        //         showDenyButton: true,
        //         showCancelButton: true,
        //         confirmButtonText: 'Pay Now',
        //         denyButtonText: 'Pay Later',
        //         cancelButtonText: 'Cancel',
        //     }).then((result) => {
        //         if (result.isConfirmed) {
        //             $('#modal_quotation_id').val(id);
        //             $('#paymentModal').modal('show');
        //         } else if (result.isDenied) {
        //             Swal.fire({  // in here there should be select were user can select first if straight payment or Partial Payment after done selecting enable confirm pay later button by default that is disabled
        //                 title: 'Pay Later Confirmation',
        //                 text: "You have 1 month to complete payment. Your credit limit will be checked.",
        //                 icon: 'warning',
        //                 showCancelButton: true,
        //                 confirmButtonText: 'Confirm Pay Later',
        //                 cancelButtonText: 'Cancel',
        //             }).then((result) => {
        //                 if (result.isConfirmed) {
        //                     // Show loading state for exactly 3 seconds
        //                     Swal.fire({
        //                         title: 'Processing...',
        //                         html: 'Submitting your pay later request',
        //                         allowOutsideClick: false,
        //                         showConfirmButton: false,
        //                         timer: 3000, // Show for 3 seconds
        //                         timerProgressBar: true,
        //                         willOpen: () => Swal.showLoading(),
        //                         didOpen: () => {
        //                             // Submit the request after showing loading for 3 seconds
        //                             setTimeout(() => {
        //                                 $.ajax({
        //                                     url: '/b2b/quotations/payment/paylater',
        //                                     method: 'POST',
        //                                     data: {
        //                                         quotation_id: id,
        //                                         _token: '{{ csrf_token() }}'
        //                                         // we need to get the value here if straight payment or partial payment
        //                                     },
        //                                     success: function(response) {
        //                                         Swal.fire({
        //                                             icon: 'success',
        //                                             title: 'Success!',
        //                                             html: `${response.message}<br>Remaining Credit: ₱${response.credit_limit_remaining.toLocaleString()}`,
        //                                             showConfirmButton: true,
        //                                             confirmButtonText: 'View Order'
        //                                         }).then(() => {
        //                                             window.location.href = `/b2b/quotations/review?track_id=${id}`;
        //                                         });
        //                                     },
        //                                     error: function(error) {
        //                                         let errorMsg = error.responseJSON?.message || 'Request failed';
        //                                         if (error.status === 400 && error.responseJSON?.credit_limit_remaining !== undefined) {
        //                                             errorMsg += `<br>Your credit: ₱${error.responseJSON.credit_limit_remaining.toLocaleString()}`;
        //                                         }

        //                                         Swal.fire({
        //                                             icon: 'error',
        //                                             title: 'Error',
        //                                             html: errorMsg,
        //                                             confirmButtonText: 'OK'
        //                                         });
        //                                     }
        //                                 });
        //                             }, 3000);
        //                         }
        //                     });
        //                 }
        //             });
        //         }
        //     });

        // });

        $(document).on('click', '#submitQuotationBtn', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Submit and Pay?',
                text: "You're about to submit a Purchase Order. Choose your payment method.",
                icon: 'info',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: 'Pay Via COD',
                denyButtonText: 'Pay Later',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#modal_quotation_id').val(id);
                    $('#paymentModal').modal('show');
                    $('#paid_amount').val($(this).data('totalpayment'));
                } else if (result.isDenied) {
                    Swal.fire({
                        title: 'Pay Later Confirmation',
                        html: `
                                <p>You have 1 month to complete payment. Your credit limit will be checked.</p>
                                <select id="paymentTypeSelect" class="swal2-select">
                                    <option value="" disabled selected>-- Select Payment Type --</option>
                                    <option value="straight">Straight Payment</option>
                                    <option value="partial">Partial Payment</option>
                                </select>
                            `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Confirm Pay Later',
                        cancelButtonText: 'Cancel',
                        didOpen: () => {
                            const confirmBtn = Swal.getConfirmButton();
                            confirmBtn.disabled = true; // disable until selection

                            document.getElementById('paymentTypeSelect').addEventListener('change', function() {
                                confirmBtn.disabled = (this.value === '');
                            });
                        },
                        preConfirm: () => {
                            const type = document.getElementById('paymentTypeSelect').value;
                            if (!type) {
                                Swal.showValidationMessage('Please select a payment type.');
                                return false;
                            }
                            return type;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const paymentType = result.value; // straight or partial

                            Swal.fire({
                                title: 'Processing...',
                                html: 'Submitting your pay later request',
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                willOpen: () => Swal.showLoading(),
                                didOpen: () => {
                                    setTimeout(() => {
                                        $.ajax({
                                            url: '/b2b/quotations/payment/paylater',
                                            method: 'POST',
                                            data: {
                                                quotation_id: id,
                                                payment_type: paymentType,
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


        $('#cod_flg').on('change', function() {
            if ($(this).val() == '0') {
                $('#bankTransferSection').removeClass('d-none');
            } else {
                $('#bankTransferSection').addClass('d-none');
            }
        });

        $('#submitPaymentBtn').on('click', function(e) {
            e.preventDefault();

            let form = $('#paymentForm')[0];
            let formData = new FormData(form);

            $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...');
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

        // Show Cancel Modal
        $(document).on('click', '.cancel-pr-btn', function() {
            const id = $(this).data('id');
            $('#cancelQuotationId').val(id);
            $('#cancelRemarks').val('');
            $('#cancelPRModal').modal('show');
        });

        // Confirm Cancel Action
        $(document).on('click', '#confirmCancelPRBtn', function() {
            const prId = $('#cancelQuotationId').val();
            const remarks = $('#cancelRemarks').val();

            $('#confirmCancelPRBtn').prop('disabled', true);

            $.ajax({
                url: `/b2b/quotations/cancel/${prId}`,
                method: 'POST',
                data: {
                    remarks: remarks,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    $('#cancelPRModal').modal('hide');
                    toast('success', res.message);
                    $('#processingTable').DataTable().ajax.reload(null, false);
                    $('#confirmCancelPRBtn').prop('disabled', false);
                    setTimeout(function() {
                        window.location = '/b2b/quotations/review#cancelledTab';
                    }, 3000);
                },
                error: function(xhr) {
                    toast('error', xhr.responseJSON?.message || 'Failed to cancel.');
                    $('#confirmCancelPRBtn').prop('disabled', false);
                }
            });
        });

    });
</script>
@endpush