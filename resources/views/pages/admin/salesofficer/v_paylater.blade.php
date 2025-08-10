@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
                'title' => 'Pay-Later Payment Method List',
                'cardtopAddButton' => false
            ])

            {{-- Tabs --}}
            <ul class="nav nav-tabs" id="paymentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="straight-tab" data-bs-toggle="tab" data-bs-target="#straight" type="button" role="tab">
                        Credit Straight Payment
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="partial-tab" data-bs-toggle="tab" data-bs-target="#partial" type="button" role="tab">
                       Credit Partial Payment
                    </button>
                </li>
            </ul>

            <div class="tab-content mt-3" id="paymentTabsContent">
                {{-- Straight Payment Table --}}
                <div class="tab-pane fade show active" id="straight" role="tabpanel">
                    @component('components.table', [
                        'id' => 'paylaterStraightPaymentTable',
                        'thead' => '
                            <tr>
                                <th>Customer Name</th>
                                <th>Bank Name</th>
                                <th>Paid Amount</th>
                                <th>Paid Date</th>
                                <th>Status</th>
                                <th>Proof Payment</th>
                                <th>Reference Number</th>
                                <th></th>
                            </tr>
                        '
                    ])
                    @endcomponent
                </div>

                {{-- Partial Payment Table --}}
                <div class="tab-pane fade" id="partial" role="tabpanel">
                    @component('components.table', [
                        'id' => 'paylaterPartialPaymentTable',
                        'thead' => '
                            <tr>
                                <th>Customer Name</th>
                                <th>Amount to Pay</th>
                                <th>Due Date</th>
                                <th></th>
                            </tr>
                        '
                    ])
                    @endcomponent
                </div>
            </div>

            @endcomponent
        </div>
    </div>

    @component('components.modal', ['id' => 'viewPartialPaymentModal', 'size' => 'xl', 'scrollable' => true])
    <div id="partialPaymentTable"></div>
    @endcomponent

</div>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'salesofficer_paylater']) }}"></script>
@endpush
