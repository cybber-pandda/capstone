@extends('layouts.auth')

@section('content')
<form method="POST" action="{{ route('custom.login') }}">
    @csrf

    <div class="card-body p-0"> 
        <!-- Username or Email -->
        <div class="input-group mb-4">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white px-4 border-md border-right-0 @error('identifier') border-danger @enderror">
                    <i class="fa fa-envelope text-muted"></i>
                </span>
            </div>
            <input 
                class="form-control bg-white border-left-0 border-md @error('identifier') is-invalid @enderror" 
                name="identifier" 
                id="identifier" 
                value="{{ old('identifier') }}" 
                placeholder="{{ __('Username or Email') }}"
                required
            >
            @error('identifier')
            <span class="invalid-feedback d-block" role="alert">{!! $message !!}</span>
            @enderror
        </div>

        <!-- Password -->
        <div class="input-group mb-2">
            <div class="input-group-prepend">
                <span class="input-group-text bg-white px-4 border-md border-right-0 @error('password') border-danger @enderror">
                    <i class="fa fa-lock text-muted"></i>
                </span>
            </div>
            <input 
                id="password" 
                type="password" 
                name="password" 
                placeholder="Password" 
                class="form-control bg-white border-left-0 border-md @error('password') is-invalid border-right-0 @enderror"
                required
            >
            <div class="input-group-append">
                <span 
                    class="input-group-text bg-white border-md border-left-0 toggle-password @error('password') border-danger-left @enderror" 
                    data-target="#password" 
                    style="cursor: pointer;"
                >
                    <i class="fa fa-eye"></i>
                </span>
            </div>
            @error('password')
            <span class="invalid-feedback d-block" role="alert">{!! $message !!}</span>
            @enderror
        </div>

        <!-- Forgot Password -->
        <div class="mb-4 d-flex justify-content-start">
            @if (Route::has('password.request'))
                <a class="fs-5 mr-3 link-orange" href="{{ route('password.request') }}">
                    {{ __("Forgot your password?") }}
                </a>
            @endif
        </div>

        <!-- Submit -->
        <div class="form-group mb-0">
            <button type="submit" class="btn btn-twitter btn-block py-2">
                <span class="font-weight-bold">Login Account</span>
            </button>
        </div>

        <!-- Divider -->
        <div class="form-group d-flex align-items-center my-4 px-3">
            <div class="border-bottom w-100"></div>
            <span class="px-2 small text-muted font-weight-bold text-muted">OR</span>
            <div class="border-bottom w-100"></div>
        </div>

        <!-- Google Login -->
        <div class="form-group">
            <a href="{{ route('google.redirect') }}" class="btn btn-primary btn-block py-2 btn-facebook">
                <i class="fa fa-google mr-2"></i>
                <span class="font-weight-bold">Login with Google</span>
            </a>
        </div>

        <!-- Register -->
        <div class="text-center w-100">
            <p class="text-muted font-weight-bold">
                Don't have an account yet? 
                <a href="{{ route('register') }}" class="fs-5 mr-3 link-orange">Register</a>
            </p>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="{{ route('secure.js', ['filename' => 'login']) }}"></script>
@if(session('account_deactivated'))
<script>
//if hanngang endif
Swal.fire({ 
  icon: 'warning',
  title: 'Account Deactivated',
  text: 'Your account is temporarily deactivated. Please contact us at tantucoctc@gmail.com',
  confirmButtonColor: '#6571ff',
  customClass: {
    popup: 'swal-custom-popup',
    title: 'swal-custom-title',
    confirmButton: 'swal-custom-button'
  }
});
</script>
@endif
@endpush
