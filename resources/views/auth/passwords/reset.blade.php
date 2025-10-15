@extends('layouts.auth')

@section('content')
<form id="resetPasswordForm" action="{{ route('password.update') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">
        <!-- DONE-->
        <div class="row">

            <div>
                <h3 class="ml-3">Reset Password</h3>
                <p class="ml-3 mb-3">Enter your new password.</p>
            </div>

            <!-- Email Address -->
            <div class="input-group col-lg-12 mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white px-4 border-md border-right-0" id="email_prepend">
                        <i class="fa fa-envelope text-muted"></i>
                    </span>
                </div>
                <input class="form-control bg-white border-left-0 border-md" 
                    name="email" 
                    id="email" 
                    value="{{ $email ?? old('email') }}" 
                    placeholder="{{ __('Email') }}" 
                    autocomplete="email"
                    readonly>
                <span class="invalid-feedback d-block" role="alert" id="email_error"></span>
            </div>

            <!-- Password -->
            <div class="input-group col-lg-12 mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white px-4 border-md border-right-0" id="password_prepend">
                        <i class="fa fa-lock text-muted"></i>
                    </span>
                </div>
                <input id="password" type="password" name="password" placeholder="Password" class="form-control bg-white border-left-0 border-md">
                <div class="input-group-append">
                    <span class="input-group-text bg-white border-md border-left-0 toggle-password" id="password_prepend_left" data-target="#password" style="cursor: pointer;">
                        <i class="fa fa-eye"></i>
                    </span>
                </div>
                <span class="invalid-feedback d-block" role="alert" id="password_error"></span>
            </div>


            <!-- Password Confirmation -->
            <div class="input-group col-lg-12 mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white px-4 border-md border-right-0">
                        <i class="fa fa-lock text-muted"></i>
                    </span>
                </div>
                <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Confirm Password" class="form-control bg-white border-left-0 border-md">
                <div class="input-group-append">
                    <span class="input-group-text bg-white border-md border-left-0 toggle-password" data-target="#password_confirmation" style="cursor: pointer;">
                        <i class="fa fa-eye"></i>
                    </span>
                </div>
            </div>


            <!-- Submit Button -->
            <div class="form-group col-lg-12 mx-auto mb-0">
                <button type="button" class="btn btn-twitter btn-block py-2" id="resetAccount">
                    <span class="font-weight-bold">{{ __('Reset Password') }}</span>
                </button>
            </div>

        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'reset']) }}"></script>
@endpush