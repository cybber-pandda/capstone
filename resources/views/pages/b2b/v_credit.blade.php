@extends('layouts.shop')

@section('content')
<div class="section section-scrollable" style="margin-bottom: 20px;">
    <div class="container">
        <div class="section-title">
            <h3 class="title">{{ $page }}</h3>
        </div>

        {{-- Bootstrap 3 Tabs --}}
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#straight" aria-controls="straight" role="tab" data-toggle="tab">Straight Credit Payment</a>
            </li>
            <li role="presentation">
                <a href="#partial" aria-controls="partial" role="tab" data-toggle="tab">Partial Payment</a>
            </li>
        </ul>

        <div class="tab-content" style="margin-top: 15px;">
            {{-- Straight Payment Table --}}
            <div role="tabpanel" class="tab-pane active" id="straight">
                @component('components.table', [
                'id' => 'straightCreditTable',
                'thead' => '
                <tr>
                    <th>Credit Amount</th>
                    <th>Paid Amount</th>
                    <th>Due Date</th>
                    <th>Paid Date</th>
                    <th>Status</th>
                    <!-- <th>Remaining Balance</th> -->
                    <th></th>
                </tr>'
                ])
                @endcomponent
            </div>

            {{-- Partial Payment Table --}}
            <div role="tabpanel" class="tab-pane" id="partial">
                @component('components.table', [
                'id' => 'partialCreditTable',
                'thead' => '
                <tr>
                    <th>Total Amount</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th></th>
                </tr>'
                ])
                @endcomponent
            </div>
        </div>
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
                        <input type="hidden" name="credit_payment_type" id="credit_payment_type">

                        <!-- <h6>Amount to Pay: <span id="credit_amount"></span></h6> -->

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
                            <input type="file" class="form-control" name="proof_payment" id="proof_payment" accept="image/*">
                            <div class="invalid-feedback proof_payment_error text-danger"></div>
                        </div>

                        <div style="margin-bottom:10px;">
                            <label for="paid_amount" class="form-label">Enter Paid Amount:</label>
                            <input type="text" class="form-control" name="paid_amount" id="paid_amount">
                            <div class="invalid-feedback paid_amount_error text-danger"></div>
                        </div>

                        <div style="margin-bottom:10px;">
                            <label for="reference_number" class="form-label">Enter Reference Number:</label>
                            <input type="number" class="form-control" name="reference_number" id="reference_number">
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

    <div class="modal fade" id="partialPaymentsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="border:0px">
                    <h4 class="modal-title">Partial Payments</h4>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="partialPaymentsTable" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Amount Paid</th>
                                    <th>Due Date</th>
                                    <th>Amount to Pay</th>
                                    <th>Payment Date</th>
                                    <th>Status</th>
                                    <th>Proof</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Populated by DataTables --}}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer" style="border:0px">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>



</div>
@endsection


@push('scripts')
<script>
    $(document).ready(function() {
        // let table = $('#myCreditTable').DataTable({
        //     processing: true,
        //     serverSide: true,
        //     fixedHeader: {
        //         header: true
        //     },
        //     scrollCollapse: true,
        //     scrollX: true,
        //     scrollY: 600,
        //     autoWidth: false,
        //     responsive: true,
        //     ajax: {
        //         url: "{{ route('b2b.purchase.credit') }}"
        //     },
        //     columns: [{
        //             data: "credit_amount",
        //             name: "credit_amount"
        //         },
        //         {
        //             data: "paid_amount",
        //             name: "paid_amount"
        //         },
        //         {
        //             data: "due_date",
        //             name: "due_date"
        //         },
        //         {
        //             data: "paid_date",
        //             name: "paid_date"
        //         },
        //         {
        //             data: "status",
        //             name: "status",
        //             orderable: false,
        //             searchable: false
        //         },
        //         {
        //             data: "remaining_balance",
        //             name: "remaining_balance",
        //             orderable: false,
        //             searchable: false
        //         },
        //         {
        //             data: "action",
        //             name: "action",
        //             orderable: false,
        //             searchable: false
        //         },
        //     ]
        // });

        // Straight credit DataTable
        let straightTable = $('#straightCreditTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('b2b.purchase.credit') }}",
                data: {
                    payment_type: 'straight'
                }
            },
            columns: [{
                    data: "credit_amount"
                },
                {
                    data: "paid_amount"
                },
                {
                    data: "due_date"
                },
                {
                    data: "paid_date"
                },
                {
                    data: "status",
                    orderable: false,
                    searchable: false
                },
                // {
                //     data: "remaining_balance",
                //     orderable: false,
                //     searchable: false
                // },
                {
                    data: "action",
                    orderable: false,
                    searchable: false
                }
            ]
        });

        // Partial credit DataTable
        let partialTable = $('#partialCreditTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('b2b.purchase.credit') }}",
                data: {
                    payment_type: 'partial'
                }
            },
            columns: [{
                    data: "total_amount"
                },
                {
                    data: "due_date"
                },
                {
                    data: "status",
                    orderable: false,
                    searchable: false
                },
                {
                    data: "action",
                    orderable: false,
                    searchable: false
                }
            ]
        });

        // Tab switch reloads the relevant table
        $('a[data-toggle="tab"]').on('shown.bs.tab', function() {
            setTimeout(function() {
                $($.fn.dataTable.tables(true)).DataTable()
                    .columns.adjust()
                    .responsive.recalc()
                    .draw(false);
            }, 200);
        });

        $(document).on('click', '.pay-btn', function() {
            const id = $(this).data('id');

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
                    $('#credit_payment_type').val('straight');
                    $('#paymentModal').modal('show');
                }
            });

        });

        $(document).on('click', '.partial-payment-list', function() {
            let creditId = $(this).data('id');

            if ($.fn.DataTable.isDataTable('#partialPaymentsTable')) {
                $('#partialPaymentsTable').DataTable().destroy();
            }

            $('#partialPaymentsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('b2b.purchase.partial-payments') }}",
                    data: { credit_id: creditId }
                },
                columns: [
                    { 
                        data: "id",
                        render: function(data) {
                            return `INV-${String(data).padStart(5, '0')}`;
                        }
                    },
                    { data: "paid_amount" },
                    { 
                        data: "due_date", 
                        render: function(data) {
                            if (!data) return '-';
                            let dateObj = new Date(data);
                            return dateObj.toLocaleDateString('en-US', { 
                                year: 'numeric', 
                                month: 'short', 
                                day: 'numeric' 
                            });
                        }
                    },
                    { data: "amount_to_pay" },
                    { 
                        data: "paid_date",
                        render: function(data) {
                            if (!data) return '-';
                            let dateObj = new Date(data);
                            return dateObj.toLocaleDateString('en-US', { 
                                year: 'numeric', 
                                month: 'short', 
                                day: 'numeric' 
                            });
                        }
                    },
                    { data: "status" },
                    { 
                        data: "proof_payment",
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            if (data) {
                                return `<a href="${data}" target="_blank"><img src="${data}" style="max-height:50px"></a>`;
                            }
                            return '-';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <button class="btn btn-primary btn-sm payment-btn" data-id="${row.id}">
                                    Pay Now
                                </button>
                            `;
                        }
                    }
                ]

            });

            // Show modal
            $('#partialPaymentsModal').modal('show');
        });

        $(document).on('click', '.payment-btn', function() {
            const id = $(this).data('id');

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
                    $('#credit_payment_type').val('partial');
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


    });
</script>
@endpush