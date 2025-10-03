@extends('layouts.auth')

@section('content')

<div class="mt-0 desktop-mt align-items-center">

        <div class="card-body p-5">
            <div class="mb-4">

                @if (session('resent'))
                <div id="otpAlert" class="alert alert-success" role="alert">
                    {{ __('A fresh 6 digit otp code has been sent to your email address.') }}
                </div>
                @endif

                <br>

                <h3 class="text-center">OTP VERIFICATION</h3>

                @php
                use App\Helpers\EmailHelper;
                $email = Auth::user()->email;
                $maskedEmail = EmailHelper::maskEmail($email);
                @endphp

                <p class="text-center mt-3 mb-3">An OTP has been sent to {{ $maskedEmail }}</p>

                <div class="otp-input-fields">
                    <input type="number" class="otp__digit otp__field__1">
                    <input type="number" class="otp__digit otp__field__2">
                    <input type="number" class="otp__digit otp__field__3">
                    <input type="number" class="otp__digit otp__field__4">
                    <input type="number" class="otp__digit otp__field__5">
                    <input type="number" class="otp__digit otp__field__6">
                </div>

                <span class="invalid-feedback d-block text-center" role="alert" id="code_error"></span>

                <div class="text-center mt-5 mb-5">
                    <button type="button" id="verifyAccount" class="btn btn-secondary">Verify Account</button>

                    <div id="timer" class="text-center mt-3 mb-3"></div>

                    <form class="d-inline d-none" id="sendRequest" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('click here to request another otp') }}</button>.
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    var otpExpireTime = "{{ Auth::user()->otp_expire }}";
</script>
<script src="{{ route('secure.js', ['filename' => 'verify']) }}"></script>
@endpush