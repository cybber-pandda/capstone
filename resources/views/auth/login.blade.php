@extends('layouts.auth')

@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="row mt-0 desktop-mt align-items-center">

        <!-- Email Address -->
        <div class="input-group col-lg-12 mb-4">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white px-4 border-md border-right-0  @error('identifier') border-danger @enderror">
                    <i class="fa fa-envelope text-muted"></i>
                </span>
            </div>
            <input class="form-control bg-white border-left-0 border-md @error('identifier') is-invalid @enderror" name="identifier" id="identifier" value="{{ old('identifier') }}" placeholder="{{ __('Username or Email') }}">
            @error('identifier')
            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
            @enderror
        </div>

        <!-- Password -->
        <div class="input-group col-lg-12 mb-4">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white px-4 border-md border-right-0  @error('password') border-danger @enderror">
                    <i class="fa fa-lock text-muted"></i>
                </span>
            </div>
            <input id="password" type="password" name="password" placeholder="{{ __('Password') }}" class="form-control bg-white border-left-0 border-md  @error('password') is-invalid @enderror">
            @error('password')
            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
            @enderror
        </div>

        <!-- Checkbox -->
        <div class="mb-4 ml-3 w-100" style="display:flex;justify-content:space-between">
            <div class="form-check custom-checkbox">
                <input type="checkbox" class="form-check-input" name="remember" id="rememberme" {{ old('remember') ? 'checked' : '' }} />
                <label class="form-check-label text-muted" for="rememberme">{{ __('Remember me') }}</label>
            </div>
            <div>
                @if (Route::has('password.request'))
                <a class="text-inherit fs-5 mr-3" href="{{ route('password.request') }}">
                    {{ __("Forgot your password?") }}
                </a>
                @endif
            </div>
        </div>

        <!-- Submit Button -->
        <div class="form-group col-lg-12 mx-auto mb-0">
            <button type="submit" class="btn btn-primary btn-block py-2">
                <span class="font-weight-bold">Login account</span>
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
                <span class="font-weight-bold">Login with Google</span>
            </a>
            <a href="#" class="btn btn-primary btn-block py-2 btn-twitter">
                <i class="fa fa-facebook-f mr-2"></i>
                <span class="font-weight-bold">Login with facebook</span>
            </a>
        </div>

        <!-- Already Registered -->
        <div class="text-center w-100">
            <p class="text-muted font-weight-bold">Don't have an account yet? <a href="{{ route('register') }}" class="text-primary ml-2">Register</a></p>
        </div>

    </div>
</form>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'login']) }}"></script>
@endpush