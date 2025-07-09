@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    @include('layouts.dashboard.breadcrumb')

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
            'title' => 'Company Details',
            'cardtopAddButton' => false
            ])

            @component('components.table', [
            'id' => 'companyTable',
            'thead' => '
            <tr>
                <th>Company Logo</th>
                <th>Company Email</th>
                <th>Company Phone</th>
                <th>Company Address</th>
                <th>Action</th>
            </tr>
            '
            ])
            @endcomponent

            @endcomponent
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="editCompanyModal" tabindex="-1" aria-labelledby="editCompanyModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="companyForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCompanyModalLabel">Edit Company Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="mb-3 text-center">
                            <img id="company-logo-img" src="assets/back/images/brand/logo/noimage.jpg"
                                style="width: 100px;">
                        </div>

                        <div class="mb-3">
                            <label for="company_logo" class="form-label">Company Logo</label>
                            <input type="file" class="form-control" id="company_logo" name="company_logo">
                        </div>

                        <div class="mb-3">
                            <label for="company_email" class="form-label">Email</label>
                            <input type="text" class="form-control" id="company_email" name="company_email">
                            <div class="invalid-feedback" id="company_email_error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="company_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="company_phone" name="company_phone">
                            <div class="invalid-feedback" id="company_phone_error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="company_address" class="form-label">Address</label>
                            <textarea class="form-control" id="company_address" name="company_address"></textarea>
                            <div class="invalid-feedback" id="company_address_error"></div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'company']) }}"></script>
@endpush