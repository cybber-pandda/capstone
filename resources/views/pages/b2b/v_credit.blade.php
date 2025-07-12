@extends('layouts.shop')

@section('content')
<div class="section section-scrollable" style="margin-bottom: 20px;">
    <div class="container">


        <div class="section-title">
            <h3 class="title">{{ $page }}</h3>
        </div>

        @component('components.table', [
        'id' => 'myCreditTable',
        'thead' => '
        <tr>
            <th>Credit Amount</th>
            <th>Paid Amount</th>
            <th>Due Date</th>
            <th>Paid Date</th>
            <th>Status</th>
            <th>Remaining Balance</th>
            <th></th>
        </tr>'
        ])
        @endcomponent

    </div>

    <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="border:0px">
                    <h5 class="modal-title text-uppercase" id="modalTitle">Credit Payment</h5>
                </div>
                <div class="modal-body">
                    <form id="paymentForm" enctype="multipart/form-data" method="POST" action="{{ route('b2b.purchase.credit.payment') }}">
                        @csrf
                        <input type="hidden" name="credit_payment_id" id="credit_payment_id">

                        <h6>Amount to Pay: <span id="credit_amount"></span></h6>

                        <div style="margin-bottom:10px;display:none">
                            <label for="bank_id" class="form-label">Payment Type:</label>
                            <select class="form-select form-control" name="bank_id" id="bank_id" required>
                                <!-- <option selected disabled value="">-- Choose a payment type --</option> -->
                                <option value="paid" selected>Pay All</option>
                                <option value="partially_paid">Partial Payment</option>
                            </select>
                        </div>


                        <div style="margin-bottom:10px;">
                            <label for="bank_id" class="form-label">Select Bank:</label>
                            <select class="form-select form-control" name="bank_id" id="bank_id" required>
                                <option selected disabled value="">-- Choose a bank --</option>
                                @foreach ($banks as $bank)
                                <option value="{{ $bank->id }}"
                                    data-account="{{ $bank->account_number }}"
                                    data-qr="{{ asset( $bank->image ) }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>

                            <div id="bankDetails" class="text-center  d-none" style="margin-top:5px;margin-bottom:5px;">
                                <p class="mb-1"><strong>Account Number:</strong> <span id="accountNumber"></span></p>
                                <img id="qrCodeImage" src="" alt="QR Code" class="img-fluid" style="max-height: 200px;" />
                            </div>
                        </div>

                        <div style="margin-bottom:10px;">
                            <label for="proof_payment" class="form-label">Upload Proof:</label>
                            <input type="file" class="form-control" name="proof_payment" id="proof_payment" required accept="image/*">
                        </div>

                        <div style="margin-bottom:10px;">
                            <label for="bank_id" class="form-label">Enter Paid Amount:</label>
                            <input type="number" class="form-control" name="paid_amount" id="paid_amount" required>
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
    $(document).ready(function() {
        let table = $('#myCreditTable').DataTable({
            processing: true,
            serverSide: true,
            fixedHeader: {
                header: true
            },
            scrollCollapse: true,
            scrollX: true,
            scrollY: 600,
            autoWidth: false,
            responsive: true,
            ajax: {
                url: "{{ route('b2b.purchase.credit') }}"
            },
            columns: [{
                    data: "credit_amount",
                    name: "credit_amount"
                },
                {
                    data: "paid_amount",
                    name: "paid_amount"
                },
                {
                    data: "due_date",
                    name: "due_date"
                },
                {
                    data: "paid_date",
                    name: "paid_date"
                },
                {
                    data: "status",
                    name: "status",
                    orderable: false,
                    searchable: false
                },
                {
                    data: "remaining_balance",
                    name: "remaining_balance",
                    orderable: false,
                    searchable: false
                },
                {
                    data: "action",
                    name: "action",
                    orderable: false,
                    searchable: false
                },
            ]
        });


        $(document).on('click', '.pay-btn', function() {
            const id = $(this).data('id');
            const amount = $(this).data('amount');

            Swal.fire({
                title: 'Submit and Pay?',
                text: "You're about to submit a Credit Payment. Choose your payment type.",
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Pay Now',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#credit_payment_id').val(id);
                    $('#credit_amount').text(amount);
                    $('#paymentModal').modal('show');
                }
            });

        });

        // Update the payment type dropdown to work with your form
        $(document).on('change', 'select[name="payment_type"]', function() {
            const amount = parseFloat($('#credit_amount').text().replace(/[^\d.]/g, ''));
            if ($(this).val() === 'paid') {
                $('#paid_amount').val(amount.toFixed(2));
            } else {
                $('#paid_amount').val('');
            }
        });

        $('#submitPaymentBtn').on('click', function(e) {
            e.preventDefault();

            let form = $('#paymentForm')[0];
            let formData = new FormData(form);

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
                        html: 'Your credit payment has been uploaded.',
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: xhr.responseJSON?.message || 'Something went wrong.',
                    });
                }
            });
        });


    });
</script>
@endpush