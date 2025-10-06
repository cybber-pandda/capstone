@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    {{-- Summary Cards --}}
    <div class="row mb-4">
        @foreach ([
        ['label' => 'Total Due Date Overdue', 'value' => $totalOverDue],
        ['label' => 'Total Balance', 'value' => $totalBalance],
        ] as $stat)
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title mb-0">{{ $stat['label'] }}</h6>
                    <h3 class="mb-2">₱{{ number_format($stat['value'], 2) }}</h3>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Customers Account Receivable Table --}}
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
            'title' => 'Account Receivable by Customer',
            'cardtopAddButton' => false,
            ])

            @component('components.table', [
            'id' => 'accountReceivableTable',
            'thead' => '
            <tr>
                <th>Customer Name</th>
                <!-- <th>Total Straight & Partial<br>Pending Amount</th> -->
                <th>Total Straight & Partial<br>Overdue Amount</th>
                <th>Total Straight & Partial<br>Balance Amount</th>
                <th></th>
            </tr>
            '
            ])
            @endcomponent

            @endcomponent
        </div>
    </div>

    @component('components.modal', ['id' => 'viewPRDebtModal', 'size' => 'lg', 'scrollable' => true])
    <div id="customerPRDebtDetails"></div>
    @endcomponent

    @component('components.modal', ['id' => 'viewDebtModal', 'size' => 'lg', 'scrollable' => true])
    <div id="customerDebtDetails"></div>
    @endcomponent

</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#accountReceivableTable').DataTable({
            processing: true,
            serverSide: true,
            paginationType: "simple_numbers",
            responsive: true,
            layout: {
                topEnd: {
                    search: {
                        placeholder: "Search Customer",
                    },
                },
            },
            aLengthMenu: [
                [5, 10, 30, 50, -1],
                [5, 10, 30, 50, "All"],
            ],
            iDisplayLength: 10,
            language: {
                search: "",
            },
            fixedHeader: {
                header: true
            },
            scrollCollapse: true,
            scrollX: true,
            scrollY: 600,
            ajax: "/salesofficer/account-receivable/all",
            autoWidth: false,
            columns: [{
                    data: 'customer_name',
                    width: '30%'
                },
                // {
                //     data: 'pending',
                //     width: '15%',
                //     render: $.fn.dataTable.render.number(',', '.', 2, '₱')
                // },
                {
                    data: 'overdue',
                    width: '15%',
                    render: $.fn.dataTable.render.number(',', '.', 2, '₱')
                },
                {
                    data: 'balance',
                    width: '15%',
                    render: $.fn.dataTable.render.number(',', '.', 2, '₱')
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                    width: '25%',
                },
            ],
            order: [
                [3, 'desc']
            ],
            language: {
                emptyTable: "No account receivable records found."
            }
        });

        $(document).on("click", ".view-details", function(e) {
            e.preventDefault();

            let userid = $(this).data("userid");
            let prid   = $(this).data("prid");

            $(".modal-title").text("Customer Purchase Request Payment");

            let html = `
                <div class="mb-3 d-flex flex-column">
                    <label class="form-label fw-bold text-uppercase mb-3">Choose Payment Type</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="creditPaymentType" id="straightPayment" value="Straight Payment" checked>
                            <label class="form-check-label" for="straightPayment">Straight Payment</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="creditPaymentType" id="partialPayment" value="Partial Payment">
                            <label class="form-check-label" for="partialPayment">Partial Payment</label>
                        </div>
                    </div>
                </div>

                <hr class="border border-dark my-3">

                <div id="straightPaymentBox" class="d-none">
                    <h5 class="text-uppercase">Straight Payment List</h5>

                    <table class="table table-striped table-sm mt-3 mb-3 table-2" id="straightPRTable">
                        <thead>
                            <tr>
                                <th>Invoice No.</th>
                                <th>Credit Amount</th>
                                <th>Status</th>
                                <th>Date Created</th>
                                <th></th> 
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div id="partialPaymentBox" class="d-none">
                    <h5 class="text-uppercase">Partial Payment List</h5>

                    <table class="table table-striped table-sm mt-3 mb-3 table-2" id="partialPRTable">
                        <thead>
                            <tr>
                                <th>Invoice No.</th>
                                <th>Credit Amount</th>
                                <th>Status</th>
                                <th>Date Created</th>
                                <th></th> 
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            `;

            $("#customerPRDebtDetails").html(html);
            $('#viewPRDebtModal').modal('show');

            // define toggle AFTER content is injected
            function togglePaymentTables() {
                let selected = $('input[name="creditPaymentType"]:checked').val();
                if (selected === "Straight Payment") {
                    $("#straightPaymentBox").removeClass("d-none");
                    $("#partialPaymentBox").addClass("d-none");
                    loadPRTable(userid, 'straight');
                } else {
                    $("#partialPaymentBox").removeClass("d-none");
                    $("#straightPaymentBox").addClass("d-none");
                    loadPRTable(userid, 'partial');
                }
            }

            // run once initially
            togglePaymentTables();

            // bind change handler
            $('input[name="creditPaymentType"]').on("change", togglePaymentTables);
        
        });

        function loadPRTable(userid, type) {

            $.get(`/salesofficer/ar-pr-table/${userid}?type=${type}`, function(res) {
                let tbody;
               

                if (type === 'straight') {
                   tbody = $('#straightPRTable tbody');
                   tbody.empty();
                } else if (type === 'partial') {
                   tbody = $('#partialPRTable tbody');
                   tbody.empty();
                }

                if (res.prLists.length === 0) {
                    tbody.append(`<tr><td colspan="5" class="text-center">No payments found</td></tr>`);
                    return;
                }

                res.prLists.forEach(pr => {
                    let invoiceTd = `<td data-label="Invoice #:">${pr.invoice_number}</td>`;
                    let creditAmountTd = `<td data-label="Credit Amount">₱ ${pr.credit_amount}</td>`;
                    let dateCreated = pr.created_at;
                    let status = pr.status.charAt(0).toUpperCase() + pr.status.slice(1);

                    let rowHtml = `
                        <tr>
                            ${invoiceTd}
                            ${creditAmountTd}
                            <td data-label="Status"><span class="badge bg-info text-white">${status}</span></td>
                            <td data-label="Date Created">${dateCreated}</td>
                            <td>
                                <button class="btn btn-sm btn-inverse-dark show-pr-payment" 
                                data-id="${pr.pr_id}" style="font-size:11px;">Show Payment
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.append(rowHtml);
                });
            });
        }

        $(document).on("click", ".show-pr-payment", function(e) {
            e.preventDefault();

            $('#viewPRDebtModal').modal('hide');

            let prid = $(this).data("id");
            let paymentType;

            $(".modal-title").text("Customer Account Receivable Details");

            $.get(`/salesofficer/ar-details/${prid}`, function(res) {
                let customer = res.customer;
                paymentType = customer.credit_payment_type === 'Straight Payment' ? 'straight' : 'partial';

                let businessName = res.customerRequirements ? res.customerRequirements.business_name : 'No business name';
                let tinNumber = res.customerRequirements ? res.customerRequirements.tin_number : 'No tin number';
                let address = res.customerAddress ? res.customerAddress.full_address : 'No address';


                let html = `
                    <div class="d-flex justify-content-between p-2">
                        <div class="d-flex flex-column">
                            <span><b class="text-uppercase">Name:</b> ${customer.customer_name}</span>
                            <span><b class="text-uppercase">Email Address:</b> ${customer.customer_email}</span>
                            <span><b class="text-uppercase">Business Name:</b> ${businessName}</span>
                            <span><b class="text-uppercase">TIN Number:</b> ${tinNumber}</span>
                            <span><b class="text-uppercase">Address:</b> ${address}</span>
                        </div>
                        <div class="d-flex flex-column">
                            <span><b class="text-uppercase">Credit limit:</b> ₱ ${customer.customer_creditlimit}</span>
                            <span><b class="text-uppercase">Balance:</b> ₱ ${customer.balance || 0}</span>
                            <span><b class="text-uppercase">Overdue:</b> ₱ ${customer.overdue || 0}</span>
                            <span class="d-none"><b class="text-uppercase">Pending:</b> ₱ ${customer.pending || 0}</span>
                        </div>
                    </div>

                    <table class="table table-striped table-sm mt-3 mb-3 table-2" id="paymentDetailsTable">
                        <thead>
                            <tr>
                                <th>Invoice No.</th>
                                <th>Due Date</th>
                                <th>Paid Amount</th>
                                <th class="amount-to-pay-th d-none">Amount to Pay</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                `;

                $("#customerDebtDetails").html(html);
                $('#viewDebtModal').modal('show');

                loadPayments(prid, paymentType);
            });
        });

        function loadPayments(prid, type) {
            $.get(`/salesofficer/ar-payments/${prid}?type=${type}`, function(res) {
                let tbody = $('#paymentDetailsTable tbody');
                tbody.empty();

                if (type === 'straight') {
                    $('.amount-to-pay-th').addClass('d-none');
                } else if (type === 'partial') {
                    $('.amount-to-pay-th').removeClass('d-none');
                }

                if (res.payments.length === 0) {
                    let colspan = type === 'partial' ? 5 : 4;
                    tbody.append(`<tr><td colspan="${colspan}" class="text-center">No payments found</td></tr>`);
                    return;
                }

                res.payments.forEach(payment => {
                    let dueDate = new Date(payment.due_date).toLocaleDateString();
                    let paidAmountTd = `<td data-label="Paid Amount:">₱ ${parseFloat(payment.paid_amount).toFixed(2)}</td>`;
                    let amountToPayTd = type === 'partial' ?
                        `<td data-label="Amount to Pay:">₱ ${parseFloat(payment.amount_to_pay).toFixed(2)}</td>` :
                        '';
                    let invoiceTd = `<td data-label="Invoice #:">${payment.invoice_number}</td>`;

                    // Capitalize first letter of status
                    let status = payment.status.charAt(0).toUpperCase() + payment.status.slice(1);

                    let rowHtml = `
                        <tr>
                            ${invoiceTd}
                            <td data-label="Due Date:">${dueDate}</td>
                            ${paidAmountTd}
                            ${amountToPayTd}
                            <td data-label="Status:"><span class="badge bg-info text-white">${status}</span></td>
                        </tr>
                    `;
                    tbody.append(rowHtml);
                });
            });
        }

        $('#viewDebtModal').on('hidden.bs.modal', function () {
            if (!$('#viewPRDebtModal').hasClass('show')) {
                $('#viewPRDebtModal').modal('show');
            }
        });

    });
</script>
@endpush