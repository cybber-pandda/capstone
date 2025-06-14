@extends('layouts.auth')

@section('content')

@if (session('exist'))
<div class="alert alert-danger">
    {{ session('exist') }}
</div>
@endif

<form id="registerForm" action="{{ route('register') }}" method="POST">
    @csrf

    <div class="row">

        <!-- First Name -->
        <div class="input-group col-lg-6 mb-4">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white px-4 border-md border-right-0"  id="firstname_prepend">
                    <i class="fa fa-user text-muted"></i>
                </span>
            </div>
            <input id="firstname" type="text" name="firstname" placeholder="First Name" class="form-control bg-white border-left-0 border-md">
             <span class="invalid-feedback d-block" role="alert" id="firstname_error"></span>
        </div>

        <!-- Last Name -->
        <div class="input-group col-lg-6 mb-4">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white px-4 border-md border-right-0"  id="lastname_prepend">
                    <i class="fa fa-user text-muted"></i>
                </span>
            </div>
            <input id="lastname" type="text" name="lastname" placeholder="Last Name" class="form-control bg-white border-left-0 border-md">
            <span class="invalid-feedback d-block" role="alert" id="lastname_error"></span>
        </div>

        <!-- User Name -->
        <div class="input-group col-lg-12 mb-4">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white px-4 border-md border-right-0" id="username_prepend">
                    <i class="fa fa-user text-muted"></i>
                </span>
            </div>
            <input id="username" type="text" name="username" placeholder="User Name" class="form-control bg-white border-left-0 border-md" value="{{ old('username') }}" autocomplete="username" autofocus>
            <span class="invalid-feedback d-block" role="alert" id="username_error"></span>
        </div>

        <!-- Email Address -->
        <div class="input-group col-lg-12 mb-4">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white px-4 border-md border-right-0" id="email_prepend">
                    <i class="fa fa-envelope text-muted"></i>
                </span>
            </div>
            <input id="email" type="email" name="email" placeholder="Email Address" class="form-control bg-white border-left-0 border-md">
            <span class="invalid-feedback d-block" role="alert" id="email_error"></span>
        </div>

        <!-- Phone Number -->
        <div class="input-group col-lg-12 mb-4">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white px-4 border-md border-right-0" id="phone_number_prepend">
                    <i class="fa fa-phone-square text-muted"></i>
                </span>
            </div>
            <input id="phone_number" type="number" name="phone_number" placeholder="Phone Number" class="form-control bg-white border-md border-left-0 pl-3">
            <span class="invalid-feedback d-block" role="alert" id="phone_number_error"></span>
        </div>

        <!-- Password -->
        <div class="input-group col-lg-12 mb-4">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white px-4 border-md border-right-0" id="password_prepend">
                    <i class="fa fa-lock text-muted"></i>
                </span>
            </div>
            <input id="password" type="password" name="password" placeholder="Password" class="form-control bg-white border-left-0 border-md">
            <span class="invalid-feedback d-block" role="alert" id="password_error"></span>
        </div>

        <!-- Password Confirmation -->
        <div class="input-group col-lg-12 mb-4">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white px-4 border-md border-right-0">
                    <i class="fa fa-lock text-muted"></i>
                </span>
            </div>
            <input id="password_confirmation" type="text" name="password_confirmation" placeholder="Confirm Password" class="form-control bg-white border-left-0 border-md">
        </div>

        <!-- Certificate of Registration -->
        <div class="form-group col-lg-6 mb-4">
            <label for="cor" class="form-label">Certificate of Registration (PDF)</label>

            <input
                type="file"
                class="bg-white  border-md"
                id="cor"
                name="cor"
                accept="application/pdf">

        </div>


        <!-- Certificate of Registration -->
        <div class="form-group col-lg-6 mb-4">
            <label for="cor" class="form-label">Business Permit (PDF)</label>

            <input
                type="file"
                class="bg-white  border-md"
                id="businesspermit"
                name="businesspermit"
                accept="application/pdf">

        </div>


        <div class="mb-4 ml-3 w-100">
            <div class="form-check custom-checkbox">
                <input type="checkbox" class="form-check-input" name="agree" id="agree" />
                <label class="form-check-label text-muted" for="agree">{{ __('Agree to our Terms and Conditions') }}</label>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="form-group col-lg-12 mx-auto mb-0">
            <button type="button"  id="registerAccount" class="btn btn-primary btn-block py-2">
                <span class="font-weight-bold">Create your account</span>
            </button>
        </div>

        <!-- Divider Text -->
        <div class="form-group col-lg-12 mx-auto d-flex align-items-center my-4">
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
            <a href="#" class="btn btn-primary btn-block py-2 btn-twitter">
                <i class="fa fa-facebook-f mr-2"></i>
                <span class="font-weight-bold">Continue with Facebook</span>
            </a>
        </div>

        <!-- Already Registered -->
        <div class="text-center w-100">
            <p class="text-muted font-weight-bold">Already Registered? <a href="{{ route('login') }}" class="text-primary ml-2">Login</a></p>
        </div>

    </div>
</form>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'register']) }}"></script>
<script>
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        this.submit();
        window.location.href = '{{ route("verification.notice") }}';
    });
</script>
@endpush