@extends('layouts.auth')

@section('content')
<form id="forgotPasswordForm" method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="card shadow-lg p-3">
        <div class="row">

            <div>
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
                <input class="form-control bg-white border-left-0 border-md" type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="{{ __('Email Address') }}">
                <span class="invalid-feedback d-block" role="alert" id="email_error"></span>
            </div>

            <!-- Submit Button -->
            <div class="form-group col-lg-12 mx-auto mb-0">
                <button type="button" class="btn btn-primary btn-block py-2" id="forgotAccount">
                    <span class="font-weight-bold">{{ __('Send Password Reset Link') }}</span>
                </button>
            </div>


            <!-- Already Registered -->
            <div class="text-center w-100">
                <p class="text-muted font-weight-bold">Already have an account? <a href="{{ route('login') }}" class="text-primary ml-2">Login</a></p>
            </div>

        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'forgot']) }}"></script>
@endpush