@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    {{-- Summary Cards --}}
    <div class="row mb-4">
        @foreach ([
        ['label' => 'Total Pending', 'value' => $totalPending],
        ['label' => 'Total Overdue', 'value' => $totalOverDue],
        ['label' => 'Total Balance', 'value' => $totalBalance],
        ] as $stat)
        <div class="col-md-4 grid-margin stretch-card">
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
                <th>Pending Amount</th>
                <th>Overdue Amount</th>
                <th>Total Balance</th>
                <th></th>
            </tr>
            '
            ])
            @endcomponent

            @endcomponent
        </div>
    </div>

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
                {
                    data: 'pending',
                    width: '15%',
                    render: $.fn.dataTable.render.number(',', '.', 2, '₱')
                },
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
            let prid = $(this).data("prid");

            $(".modal-title").text("Customer Account Receivable Details");

            // Save userid and prid to the modal (or globally) for tab clicks
            $("#viewDebtModal").data("userid", userid);
            $("#viewDebtModal").data("prid", prid);

            $.get(`/salesofficer/ar-details/${userid}/${prid}`, function(res) {
                let customer = res.customer;

                let html = `
                    <h3>${customer.credit_payment_type}</h3><br>
                    <div class="d-flex justify-content-between p-2">
                        <div class="d-flex flex-column">
                            <span><b class="text-uppercase">Name:</b> ${customer.customer_name}</span>
                            <span><b class="text-uppercase">Email Address:</b> ${customer.customer_email}</span>
                            <span><b class="text-uppercase">Business Name:</b> ${res.customerRequirements.business_name}</span>
                            <span><b class="text-uppercase">TIN Number:</b> ${res.customerRequirements.tin_number}</span>
                            <span><b class="text-uppercase">Address:</b> ${res.customerAddress.full_address}</span>
                        </div>
                        <div class="d-flex flex-column">
                            <span><b class="text-uppercase">Credit limit:</b> ₱ ${customer.customer_creditlimit}</span>
                            <span><b class="text-uppercase">Balance:</b> ₱ ${customer.balance || 0}</span>
                            <span><b class="text-uppercase">Overdue:</b> ₱ ${customer.overdue || 0}</span>
                            <span><b class="text-uppercase">Pending:</b> ₱ ${customer.pending || 0}</span>
                        </div>
                    </div>

                    <ul class="nav nav-tabs mt-3" id="paymentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-type="straight" type="button">Straight Credit Payment</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-type="partial" type="button">Partial Credit Payment</button>
                        </li>
                    </ul>

                    <table class="table table-striped table-sm mt-3 mb-3" id="paymentDetailsTable">
                        <thead>
                            <tr id="paymentTableHeader">
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

                loadPayments(userid, prid, 'straight');
            });
        });

        function loadPayments(userid, prid, type) {
            $.get(`/salesofficer/ar-payments/${userid}/${prid}?type=${type}`, function(res) {
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
                    let paidAmountTd = `<td>₱ ${parseFloat(payment.paid_amount).toFixed(2)}</td>`;
                    let amountToPayTd = type === 'partial' ?
                        `<td>₱ ${parseFloat(payment.amount_to_pay).toFixed(2)}</td>` :
                        '';
                    let invoiceTd = `<td>${payment.invoice_number}</td>`;

                    // Capitalize first letter of status
                    let status = payment.status.charAt(0).toUpperCase() + payment.status.slice(1);

                    let rowHtml = `
                        <tr>
                            ${invoiceTd}
                            <td>${dueDate}</td>
                            ${paidAmountTd}
                            ${amountToPayTd}
                            <td><span class="badge bg-info text-white">${status}</span></td>
                        </tr>
                    `;
                    tbody.append(rowHtml);
                });
            });
        }

        $(document).on('click', '#paymentTabs button', function() {
            let type = $(this).data('type');
            let userid = $("#viewDebtModal").data('userid');
            let prid = $("#viewDebtModal").data('prid');

            $('#paymentTabs button').removeClass('active');
            $(this).addClass('active');

            loadPayments(userid, prid, type);
        });



    });
</script>
@endpush