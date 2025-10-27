@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', ['title' => 'Rejected Payments', 'cardtopAddButton' => false])

            {{-- Tabs --}}
            <ul class="nav nav-tabs" id="rejectedPaymentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#straight">Straight Payment Rejected</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#partial">Partial Payment Rejected</button>
                </li>
            </ul>

            <div class="tab-content mt-3">
                <div class="tab-pane fade show active" id="straight">
                    @component('components.table', [
                        'id' => 'rejectedStraightPaymentTable',
                        'thead' => '
                            <tr>
                                <th>Customer Name</th>
                                <th>Bank Name</th>
                                <th>Paid Amount</th>
                                <th>Paid Date</th>
                                <th>Proof Payment</th>
                                <th>Reference #</th>
                                <th>Status</th>
                                <th>Rejection Reason</th>
                            </tr>'
                    ]) @endcomponent
                </div>

                <div class="tab-pane fade" id="partial">
                    @component('components.table', [
                        'id' => 'rejectedPartialPaymentTable',
                        'thead' => '
                            <tr>
                                <th>Customer Name</th>
                                <th>Amount to Pay</th>
                                <th>Due Date</th>
                                <th>Proof Payment</th>
                                <th>Reference #</th>
                                <th>Status</th>
                                <th>Rejection Reason</th>
                            </tr>'
                    ]) @endcomponent
                </div>
            </div>

            @endcomponent
        </div>
    </div>
</div>

{{-- Rejection modal (if you want to allow re-rejecting/changing reason) --}}
@component('components.modal', ['id' => 'rejectModal', 'size' => 'md', 'scrollable' => true])
    <form id="rejectForm">
        @component('components.textarea', ['label' => 'Reason', 'name' => 'rejection_reason']) @endcomponent
    </form>
    @slot('footer')
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary btn-sm" id="rejectFormbtn">
            <span class="rejectFormbtn_button_text">Save</span>
            <span class="rejectFormbtn_load_data d-none">Loading <i class="loader"></i></span>
        </button>
    @endslot
@endcomponent

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    function initRejectedDataTable(tableId, paymentType, columns) {
        return $("#" + tableId).DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('salesofficer.rejected-payments.all') }}",
                data: { payment_type: paymentType }
            },
            responsive: true,
            autoWidth: false,
            columns: columns,
            language: { search: "Search: ", searchPlaceholder: "Search here" },
            drawCallback: function () {
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        });
    }

    let straightTable = initRejectedDataTable('rejectedStraightPaymentTable', 'straight', [
        { data: 'customer_name' },
        { data: 'bank_name' },
        { data: 'paid_amount' },
        { data: 'paid_date' },
        { data: 'proof_payment', orderable: false, searchable: false },
        { data: 'reference_number' },
        { data: 'status' },
        { data: 'rejection_reason' }
    ]);

    let partialTable = null;
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        let target = $(e.target).attr('data-bs-target');
        if (target === '#partial' && !partialTable) {
            partialTable = initRejectedDataTable('rejectedPartialPaymentTable', 'partial', [
                 { data: 'customer_name' },
                 { data: 'amount_to_pay' },
                 { data: 'due_date' },
                 { data: 'proof_payment', orderable: false, searchable: false },
                 { data: 'reference_number' },
                 { data: 'status' },
                 { data: 'rejection_reason' }
            ]);
        }
    });
});
</script>
@endpush
