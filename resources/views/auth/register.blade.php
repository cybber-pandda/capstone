@extends('layouts.auth')

@section('content')

@if (session('exist'))
<div class="alert alert-danger">
    {{ session('exist') }}
</div>
@endif

<form id="registerForm" action="{{ route('register') }}" method="POST">
    @csrf
    <div class="card shadow-lg p-3">
        <div class="row align-items-center">

            <!-- First Name -->
            <div class="input-group col-lg-6 mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white px-4 border-md border-right-0" id="firstname_prepend">
                        <i class="fa fa-user text-muted"></i>
                    </span>
                </div>
                <input id="firstname" type="text" name="firstname" placeholder="First Name" class="form-control bg-white border-left-0 border-md">
                <span class="invalid-feedback d-block" role="alert" id="firstname_error"></span>
            </div>

            <!-- Last Name -->
            <div class="input-group col-lg-6 mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white px-4 border-md border-right-0" id="lastname_prepend">
                        <i class="fa fa-user text-muted"></i>
                    </span>
                </div>
                <input id="lastname" type="text" name="lastname" placeholder="Last Name" class="form-control bg-white border-left-0 border-md">
                <span class="invalid-feedback d-block" role="alert" id="lastname_error"></span>
            </div>

            <!-- User Name -->
            <div class="input-group col-lg-12 mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white px-4 border-md border-right-0" id="username_prepend">
                        <i class="fa fa-user text-muted"></i>
                    </span>
                </div>
                <input id="username" type="text" name="username" placeholder="User Name" class="form-control bg-white border-left-0 border-md" value="{{ old('username') }}" autocomplete="username">
                <span class="invalid-feedback d-block" role="alert" id="username_error"></span>
            </div>

            <!-- Email Address -->
            <div class="input-group col-lg-12 mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white px-4 border-md border-right-0" id="email_prepend">
                        <i class="fa fa-envelope text-muted"></i>
                    </span>
                </div>
                <input id="email" type="email" name="email" placeholder="Email Address" class="form-control bg-white border-left-0 border-md">
                <span class="invalid-feedback d-block" role="alert" id="email_error"></span>
            </div>

            <!-- Phone Number -->
            <!-- <div class="input-group col-lg-12 mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white px-4 border-md border-right-0" id="phone_number_prepend">
                        <i class="fa fa-phone-square text-muted"></i>
                    </span>
                </div>
                <input id="phone_number" type="number" name="phone_number" placeholder="Phone Number" class="form-control bg-white border-md border-left-0 pl-3">
                <span class="invalid-feedback d-block" role="alert" id="phone_number_error"></span>
            </div> -->

            <!-- Password -->
            <div class="input-group col-lg-12 mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white px-4 border-md border-right-0" id="password_prepend">
                        <i class="fa fa-lock text-muted"></i>
                    </span>
                </div>
                <input id="password" type="password" name="password" placeholder="Password" class="form-control bg-white border-left-0 border-md">
                <span class="invalid-feedback d-block" role="alert" id="password_error"></span>
            </div>

            <!-- Password Confirmation -->
            <div class="input-group col-lg-12 mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white px-4 border-md border-right-0">
                        <i class="fa fa-lock text-muted"></i>
                    </span>
                </div>
                <input id="password_confirmation" type="text" name="password_confirmation" placeholder="Confirm Password" class="form-control bg-white border-left-0 border-md">
            </div>

            <!-- Certificate of Registration -->
            <!-- <div class="form-group col-lg-6 mb-2">
                <label for="cor" class="form-label">Certificate of Registration (PDF)</label>

                <input
                    type="file"
                    class="bg-white  border-md"
                    id="cor"
                    name="cor"
                    accept="application/pdf">

                <span class="invalid-feedback d-block" role="alert" id="cor_error"></span>
            </div> -->


            <!-- Certificate of Registration -->
            <!-- <div class="form-group col-lg-6 mb-2">
                <label for="cor" class="form-label">Business Permit (PDF)</label>

                <input
                    type="file"
                    class="bg-white  border-md"
                    id="businesspermit"
                    name="businesspermit"
                    accept="application/pdf">
                <span class="invalid-feedback d-block" role="alert" id="businesspermit_error"></span>
            </div> -->


            <div class="mb-2 ml-3 w-100">
                <div class="form-check custom-checkbox">
                    <input type="checkbox" class="form-check-input" name="agree" id="agree" />
                    <label class="form-check-label text-muted" for="agree">
                        {{ __('Agree to our') }}
                        <a href="javascript:void(0);" class="showTCP" data-tab="terms">Terms and Conditions</a>
                        {{ __('and') }}
                        <a href="javascript:void(0);" class="showTCP" data-tab="policy">Privacy Policy</a>
                    </label>
                    <span class="invalid-feedback d-block" role="alert" id="agree_error"></span>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="form-group col-lg-12 mx-auto mb-0">
                <button type="button" id="registerAccount" class="btn btn-primary btn-block py-2">
                    <span class="font-weight-bold">Create your account</span>
                </button>
            </div>

            <!-- Divider Text -->
            <div class="form-group col-lg-12 mx-auto d-flex align-items-center my-2">
                <div class="border-bottom w-100 ml-5"></div>
                <span class="px-2 small text-muted font-weight-bold text-muted">OR</span>
                <div class="border-bottom w-100 mr-5"></div>
            </div>

            <!-- Social Login -->
            <div class="form-group col-lg-12 mx-auto">
                <a href="{{ route('google.redirect') }}" class="btn btn-primary btn-block py-2 btn-facebook">
                    <i class="fa fa-google mr-2"></i>
                    <span class="font-weight-bold">Continue with Google</span>
                </a>
                <a href="#" class="btn btn-primary btn-block py-2 btn-twitter d-none">
                    <i class="fa fa-facebook-f mr-2"></i>
                    <span class="font-weight-bold">Continue with Facebook</span>
                </a>
            </div>

            <!-- Already Registered -->
            <div class="text-center w-100">
                <p class="text-muted font-weight-bold">Already Registered? <a href="{{ route('login') }}" class="text-primary ml-2">Login</a></p>
            </div>

        </div>
    </div>
</form>

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" role="dialog" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <!-- <div class="modal-header border-0">
                <h5 class="modal-title" id="termsModalLabel">Accept our Terms & Conditions and Privacy Policy</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div> -->
            <div class="modal-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs mb-3" id="termsTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="terms-tab" data-toggle="tab" href="#terms" role="tab" aria-controls="terms" aria-selected="true">Terms & Conditions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="policy-tab" data-toggle="tab" href="#policy" role="tab" aria-controls="policy" aria-selected="false">Privacy Policy</a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="terms" role="tabpanel" aria-labelledby="terms-tab">
                        <div class="p-3" style="max-height: 400px; overflow-y: auto;">
                            @if($terms && $conditions)
                                @if($terms->content)
                                    {!! $terms->content !!}
                                @endif
                                
                                @if($conditions->content)
                                    <hr>
                                    {!! $conditions->content !!}
                                @endif
                            @else
                                <h4>Default Terms and Conditions</h4>
                                <p>Please contact the administrator for the terms and conditions.</p>
                            @endif
                        </div>
                    </div>
                    <div class="tab-pane fade" id="policy" role="tabpanel" aria-labelledby="policy-tab">
                        <div class="p-3" style="max-height: 400px; overflow-y: auto;">
                            @if($policy && $policy->content)
                                {!! $policy->content !!}
                            @else
                                <h4>Default Privacy Policy</h4>
                                <p>Please contact the administrator for the privacy policy.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-primary" data-dismiss="modal">I Understand</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'register']) }}"></script>
<script>
    $(document).ready(function() {
        // Form submission handler
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            this.submit();
            window.location.href = '{{ route("verification.notice") }}';
        });

        // Handle modal show and tab switching
        $(document).on('click', '.showTCP', function(e) {
            e.preventDefault();
            var tabToShow = $(this).data('tab');

            // Show the modal
            $('#termsModal').modal('show');

            // After modal is shown, switch to the correct tab
            $('#termsModal').on('shown.bs.modal', function() {
                $('.nav-tabs a[href="#' + tabToShow + '"]').tab('show');
            });
        });

        // Initialize tabs
        $('.nav-tabs a').on('click', function(e) {
            e.preventDefault();
            $(this).tab('show');
        });
    });
</script>
@endpush