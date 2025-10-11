@extends('layouts.dashboard')

@section('content')
    <div class="page-content container-xxl">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">

                @component('components.card', [
                    'title' => 'Delivery Location List',
                    'cardtopAddButton' => false,
                ])

                <ul class="nav nav-tabs nav-tabs-line mb-3" id="statusTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link" id="pending-tab" data-bs-toggle="tab" href="#pending" role="tab"
                            aria-controls="pending" aria-selected="true" data-status="pending">Pending</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="assigned-tab" data-bs-toggle="tab" href="#pending" role="tab"
                            aria-controls="assigned" aria-selected="false" data-status="assigned">Assigned</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="on_the_way-tab" data-bs-toggle="tab" href="#pending" role="tab"
                            aria-controls="on_the_way" aria-selected="false" data-status="on_the_way">On the Way</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="delivered-tab" data-bs-toggle="tab" href="#pending" role="tab"
                            aria-controls="delivered" aria-selected="false" data-status="delivered">Delivered</a>
                    </li>
                </ul>

                <div class="tab-content" id="statusTabsContent">
                    <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">

                        @component('components.table', [
                            'id' => 'deliveryLocationTable',
                            'thead' => '
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer Name</th>
                                        <th>Phone Number</th>
                                        <th>Total Items</th>
                                        <th>Grand Total</th>
                                        <th>Customer Address</th>
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

        @component('components.modal', ['id' => 'viewAddressModal', 'size' => 'md', 'scrollable' => true])
        <div id="addressDetails"></div>
        @endcomponent

        @component('components.modal', ['id' => 'uploadProofModal', 'title' => 'Upload Proof of Delivery', 'scrollable' => false])
        <form id="uploadProofForm" enctype="multipart/form-data" action="{{ route('deliveryrider.delivery.upload-proof') }}"
            method="POST">
            @csrf
            <input type="hidden" name="delivery_id" id="delivery_id">
            <div class="mb-3">
                <label for="proofImage" class="form-label">Select Proof Image</label>
                <input type="file" class="form-control" id="proofImage" name="proof_delivery" accept="image/*" required>
            </div>
        </form>
        @slot('footer')
        <button type="button" class="btn btn-sm btn-inverse-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="uploadProofBtn" class="btn btn-sm btn-inverse-primary">
            <span class="uploadProofBtn_button_text">Upload</span>
            <span class="uploadProofBtn_load_data d-none">Loading <i class="loader"></i></span>
        </button>
        @endslot
        @endcomponent

        @component('components.modal', ['id' => 'viewProofModal', 'size' => 'md', 'scrollable' => true])
        <img id="proofImagePreview" src="" class="img-fluid" alt="Proof of Delivery">
        @endcomponent

        @component('components.modal', ['id' => 'cancelDeliveryModal', 'title' => 'Cancel Delivery', 'scrollable' => true])
        <form id="cancelDeliveryForm">
            @csrf
            <input type="hidden" name="delivery_id" id="cancel_delivery_id">
            <div class="mb-3">
                <textarea name="remarks" id="cancel_remarks" class="form-control" rows="4"
                    placeholder="Enter reason here..." required></textarea>
            </div>
        </form>
        @slot('footer')
        <button type="button" class="btn btn-sm btn-inverse-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-inverse-danger" id="submitCancelDeliveryBtn">
            <span class="submitCancelDeliveryBtn_button_text">Cancel Delivery</span>
            <span class="submitCancelDeliveryBtn_load_data d-none">Processing <i class="loader"></i></span>
        </button>
        @endslot
        @endcomponent



    </div>
@endsection

@push('scripts')
    <script src="{{ route('secure.js', ['filename' => 'delivery_location']) }}"></script>
@endpush