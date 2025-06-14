@extends('layouts.app')

@section('content')
<div class="row align-items-center justify-content-center g-0 min-vh-100">
    <div class="col-12 col-md-8 col-lg-6 col-xxl-4 py-8 py-xl-0">

        @include('components.toggle-theme')

        <!-- Card -->
        <div class="card smooth-shadow-md">
            <!-- Card body -->
            <div class="card-body p-6">
                <div class="mb-4">
                    <a href="{{ url('/') }}">
                        @if($companysettings)
                        <img class="mb-2 text-inverse" src="{{ asset($companysettings->company_logo ?? '') }}" alt="logo" style="width: 130px; height: 100px;">
                        @else
                        Logo not found.
                        @endif
                    </a>
                    <p class="mb-6">
                        By creating a Pawpanion account, you agree to our <br>
                        <a href="terms-condition-page.html">Terms of Service</a> and <a href="terms-condition-page.html">Privacy Policy.</a>
                    </p>
                </div>

                @if (session('exist'))
                <div class="alert alert-danger">
                    {{ session('exist') }}
                </div>
                @endif

                <!-- Form -->
                <form class="px-3 pb-3 pt-3" id="registerForm" action="{{ route('register') }}" method="POST">
                    @csrf
                    <!-- Username -->
                    <div class="mb-3">
                        <label for="username" class="form-label">User Name</label>
                        <input type="text" id="username" class="form-control" name="username" placeholder="User Name" value="{{ old('username') }}" autocomplete="username" autofocus />
                        <span class="invalid-feedback d-block" role="alert" id="username_error"></span>
                    </div>
                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" class="form-control" name="email" placeholder="Email address here" value="{{ old('email') }}" autocomplete="email" />
                        <span class="invalid-feedback d-block" role="alert" id="email_error"></span>
                    </div>
                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" class="form-control" name="password" placeholder="**************" autocomplete="new-password" />
                        <span class="invalid-feedback d-block" role="alert" id="password_error"></span>
                    </div>
                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label for="password-confirm" class="form-label">Confirm Password</label>
                        <input type="password" id="password-confirm" class="form-control" name="password_confirmation" placeholder="**************" autocomplete="new-password" />
                    </div>

                    <!-- Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" id="registerAccount">Create Free Account</button>
                    </div>

                    <h4 class="mt-2 mb-2 fw-bold text-center">OR</h4>

                    <div class="d-grid">
                        <a href="{{ route('google.redirect') }}" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="currentColor" class="bi bi-google mx-2" viewBox="0 0 16 16">
                                <path d="M15.545 6.558a9.4 9.4 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.7 7.7 0 0 1 5.352 2.082l-2.284 2.284A4.35 4.35 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.8 4.8 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.7 3.7 0 0 0 1.599-2.431H8v-3.08z" />
                            </svg>Use Google Account</a>
                    </div>

                    <div class="d-md-flex justify-content-between mt-4">
                        <div class="mb-2 mb-md-0">
                            Already member?
                            <a href="{{ route('login') }}" class="fs-5"> login</a>
                        </div>
                        <div>
                            @if (Route::has('password.request'))
                            <a class="text-inherit fs-5" href="{{ route('password.request') }}">
                                {{ __("Forgot your password?") }}
                            </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
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