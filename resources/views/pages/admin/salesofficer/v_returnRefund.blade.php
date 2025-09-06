@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
                'title' => 'Return & Refund',
                'cardtopAddButton' => false,
            ])
            
            <!-- Nav Tabs -->
            <ul class="nav nav-tabs" id="returnRefundTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="returns-tab" data-bs-toggle="tab" data-bs-target="#returns" type="button" role="tab" aria-controls="returns" aria-selected="true">
                        Returns
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="refunds-tab" data-bs-toggle="tab" data-bs-target="#refunds" type="button" role="tab" aria-controls="refunds" aria-selected="false">
                        Refunds
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content mt-3" id="returnRefundTabContent">
                <div class="tab-pane fade show active" id="returns" role="tabpanel" aria-labelledby="returns-tab">
                    @component('components.table', [
                        'id' => 'returnsTable',
                        'thead' => '
                            <tr>
                                <th>Customer Name</th>
                                <th>Product</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Photo</th>
                                <th>Date Requested</th>
                                <th>Action</th>
                            </tr>
                        '
                    ])
                    @endcomponent
                </div>
                <div class="tab-pane fade" id="refunds" role="tabpanel" aria-labelledby="refunds-tab">
                    @component('components.table', [
                        'id' => 'refundsTable',
                        'thead' => '
                            <tr>
                                <th>Customer Name</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Photo</th>
                                <th>Date Processed</th>
                                <th>Action</th>
                            </tr>
                        '
                    ])
                    @endcomponent
                </div>
            </div>

            @endcomponent
        </div>
    </div>

    @component('components.modal', ['id' => 'viewReturnModal', 'size' => 'md', 'scrollable' => true])
    <div id="returnDetails"></div>
    @endcomponent


    @component('components.modal', ['id' => 'viewRefundModal', 'size' => 'md', 'scrollable' => true])
    <div id="refundDetails"></div>
    @endcomponent


</div>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'salesofficer_return_refund']) }}"></script>
@endpush
