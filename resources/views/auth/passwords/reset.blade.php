@extends('layouts.auth')

@section('content')
<form id="resetPasswordForm" action="{{ route('password.update') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <div class="row mt-0 desktop-mt">

        <div>
            <h1 class="ml-3">Reset Password</h1>
            <p class="ml-3 mb-5">Enter you're new password.</p>
        </div>

        <!-- Email Address -->
        <div class="input-group col-lg-12 mb-4">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white px-4 border-md border-right-0" id="email_prepend">
                    <i class="fa fa-envelope text-muted"></i>
                </span>
            </div>
            <input class="form-control bg-white border-left-0 border-md" name="email" id="email" value="{{ $email ?? old('email') }}" placeholder="{{ __('Email') }}" autocomplete="email" autofocus>
            <span class="invalid-feedback d-block" role="alert" id="email_error"></span>
        </div>

        <!-- Password -->
        <div class="input-group col-lg-12 mb-4">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white px-4 border-md border-right-0" id="password_prepend">
                    <i class="fa fa-lock text-muted"></i>
                </span>
            </div>
            <input id="password" type="password" name="password" placeholder="{{ __('Password') }}" class="form-control bg-white border-left-0 border-md">
            <span class="invalid-feedback d-block" role="alert" id="password_error"></span>
        </div>

        <!-- Confirm Password -->
        <div class="input-group col-lg-12 mb-4">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white px-4 border-md border-right-0">
                    <i class="fa fa-lock text-muted"></i>
                </span>
            </div>
            <input type="password" name="password_confirmation" id="password-confirm" placeholder="{{ __('Confirm Password') }}" class="form-control bg-white border-left-0 border-md">
            <span class="invalid-feedback d-block" role="alert" id="password_error"></span>
        </div>

        <!-- Submit Button -->
        <div class="form-group col-lg-12 mx-auto mb-0">
            <button type="button" class="btn btn-primary btn-block py-2" id="resetAccount">
                <span class="font-weight-bold">{{ __('Reset Password') }}</span>
            </button>
        </div>

    </div>
</form>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'reset']) }}"></script>
@endpush