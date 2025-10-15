@extends('layouts.auth')

@section('content')
<form id="forgotPasswordForm" method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="row">
        <div class="col-12">
            <h3 class="ml-3">Forgot Password?</h3>
            <p class="ml-3 mb-3">Don't worry, we'll send you an email to reset your password.</p>
        </div>

        <!-- Email Address -->
        <div class="input-group col-lg-12 mb-4">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white px-4 border-md border-right-0" id="email_prepend">
                    <i class="fa fa-envelope text-muted"></i>
                </span>
            </div>
            <input class="form-control bg-white border-left-0 border-md" 
                   type="email" 
                   id="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   autocomplete="email" 
                   placeholder="{{ __('Email Address') }}">
            <span class="invalid-feedback d-block" role="alert" id="email_error"></span>
        </div>

        <!-- Submit Button -->
        <div class="form-group col-lg-12 mx-auto mb-0">
            <button type="button" class="btn btn-twitter btn-block py-2" id="forgotAccount">
                <span class="font-weight-bold">{{ __('Send Password Reset Link') }}</span>
            </button>
        </div>

        <!-- Already Registered -->
        <div class="text-center w-100 mt-3">
            <p class="text-muted font-weight-bold">
                Already have an account? 
                <a href="{{ route('login') }}" class="fs-5 mr-3 link-orange">Login</a>
            </p>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'forgot']) }}"></script>

<script>
            const emailInput = $('#email');
            const emailError = $('#email_error');

            emailInput.on('input', function() {
                let value = $(this).val().toLowerCase(); // optional: lowercase for uniformity

                // 1. Remove invalid characters (letters, numbers, ., _, -, @)
                value = value.replace(/[^a-z0-9._@-]/g, '');

                // 2. Ensure only one @
                const atParts = value.split('@');
                if (atParts.length > 2) {
                    value = atParts[0] + '@' + atParts.slice(1).join('').replace(/@/g, '');
                }

                // 3. Remove consecutive dots
                value = value.replace(/\.{2,}/g, '.');

                // 4. Remove leading periods
                value = value.replace(/^\.+/, '');

                // 5. Remove periods just before @
                value = value.replace(/\.@/g, '@');

                $(this).val(value);

                // 8. Regex check for valid email format
                const emailRegex = /^[a-z0-9._-]+@[a-z0-9.-]+\.[a-z]{2,}$/;

            });

              
     </script>
@endpush
