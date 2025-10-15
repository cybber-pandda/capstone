    @extends('layouts.auth')

    @section('content')
    <div class="register-wrapper">  {{-- ✅ wrapper for centering --}}
        <div class="register-card"> {{-- ✅ card for fixed width + shadow --}}
            
    @if (session('exist'))
    <div class="alert alert-danger">
        {{ session('exist') }}
    </div>
    @endif

    <form id="registerForm" action="{{ route('register') }}" method="POST">
        @csrf
    
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
                    <div class="input-group-append">
                        <span class="input-group-text bg-white border-md border-left-0 toggle-password" id="password_prepend_left" data-target="#password" style="cursor: pointer;">
                            <i class="fa fa-eye"></i>
                        </span>
                    </div>
                    <span class="invalid-feedback d-block" role="alert" id="password_error"></span>
                </div>


                <!-- Password Confirmation -->
                <div class="input-group col-lg-12 mb-2">
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

                <div class="mb-2 ml-3 w-100">
                    <div class="form-check custom-checkbox">
                        <input type="checkbox" class="form-check-input" name="agree" id="agree" />
                        <label class="form-check-label text-muted" for="agree">
                            {{ __('Agree to our') }}
                            <a href="javascript:void(0);" class="showTCP link-orange" data-tab="terms">Terms and Conditions</a>
                            {{ __('and') }}
                            <a href="javascript:void(0);" class="showTCP link-orange" data-tab="policy">Privacy Policy</a>
                        </label>
                        <span class="invalid-feedback d-block" role="alert" id="agree_error"></span>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-group col-lg-12 mx-auto mb-0">
                    <button type="button" id="registerAccount" class="btn btn-twitter btn-block py-2">
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
                    <p class="text-muted font-weight-bold">Already Registered? <a href="{{ route('login') }}" class="fs-5 mr-3 link-orange">Login</a></p>
                </div>

            </div>
        </div>
    </form>

    </div>
</div>
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
                    <button type="button" class="btn btn-orange" data-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>
    @endsection
    
    @push('styles')
    <style>
        .register-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;       /* Center vertically */
            justify-content: center;   /* Center horizontally */
            padding: 40px 15px;        /* Equal spacing top & bottom */
            box-sizing: border-box;
            overflow-y: auto;          /* Scroll on very small screens */
            background: #f8f9fa;       /* light bg to contrast the card */
        }

        .register-card {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 420px;           /* Control card width */
        }

        /* Handle First & Last Name responsive stacking */
        @media (max-width: 576px) {
            .register-card .input-group.col-lg-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
    </style>
    @endpush

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

            // --- First Name Validation ---
            const firstNameInput = $('#firstname');
            const firstNameError = $('#firstname_error');

            firstNameInput.on('input', function() {
                let value = $(this).val();

                // 1. Allow only letters and spaces
                value = value.replace(/[^A-Za-z\s]/g, '');

                // 2. Replace multiple spaces with a single space
                value = value.replace(/\s{2,}/g, ' ');

                // 3. Remove leading/trailing spaces
                value = value.trimStart();

                // 4. Auto-capitalize each word
                value = value.replace(/\b\w/g, c => c.toUpperCase());

                $(this).val(value);

                // Error messages
                if (/[^A-Za-z\s]/.test(value)) {
                    firstNameError.text('Only letters are allowed.').show();
                } else if (/\s{2,}/.test(value)) {
                    firstNameError.text('Only one space is allowed between names.').show();
                } else {
                    firstNameError.text('').hide();
                }
            });

            const lastNameInput = $('#lastname');
            const lastNameError = $('#lastname_error');

            lastNameInput.on('input', function() {
                let value = $(this).val();

                // 1. Allow only letters, spaces, and periods (for suffix)
                value = value.replace(/[^A-Za-z.\s]/g, '');

                // 2. Replace multiple spaces with a single space
                value = value.replace(/\s{2,}/g, ' ');

                // 3. Trim leading spaces
                value = value.trimStart();

                // 4. Prevent starting or trailing dots (e.g., ".Dator", "Dator.")
                value = value.replace(/^\./, '');
                value = value.replace(/\.$/, '');

                // 5. Prevent dot in the middle (e.g., Dator.Santos → Dator Santos)
                value = value.replace(/([A-Za-z]+)\.([A-Za-z]+)/g, '$1 $2');

                // 6. Auto-capitalize each word
                value = value.replace(/\b\w/g, c => c.toUpperCase());

                // 7. Auto-fix suffix casing (jr → Jr., sr → Sr.)
                const suffixFix = {
                    'jr': 'Jr.',
                    'jr.': 'Jr.',
                    'sr': 'Sr.',
                    'sr.': 'Sr.'
                };

                let parts = value.split(' ');
                if (parts.length > 1) {
                    let lastWord = parts[parts.length - 1].toLowerCase();
                    if (suffixFix[lastWord]) {
                        parts[parts.length - 1] = suffixFix[lastWord];
                    }
                }

                // 8. Disallow Sr./Jr. as prefix (e.g., "Sr Dator")
                if (/^(Sr\.?|Jr\.?)\s/i.test(value)) {
                    value = value.replace(/^(Sr\.?|Jr\.?)\s*/i, '');
                    lastNameError.text('“Sr.” or “Jr.” cannot be at the start of the surname.').show();
                } 
                else {
                    // 9. Disallow Sr./Jr. in the middle (e.g., "Dator Jr. Dator")
                    if (/\b(Jr\.?|Sr\.?)\s+[A-Za-z]/i.test(value)) {
                        lastNameError.text('“Sr.” or “Jr.” must only appear at the end of the surname.').show();
                        // Remove anything after the suffix (keeps only up to Jr./Sr.)
                        value = value.replace(/\b(Jr\.?|Sr\.?).*$/i, '$1');
                    } else {
                        lastNameError.text('').hide();
                    }
                }

                // 10. Allow only these valid suffixes at the end
                const validSuffixes = ['Jr.', 'Sr.', 'II', 'III', 'IV'];
                const lastWord = parts[parts.length - 1];
                if (parts.length > 1 && /\./.test(lastWord) && !validSuffixes.includes(lastWord)) {
                    parts.pop();
                    lastNameError.text('Invalid suffix. Only "Jr.", "Sr.", "II", "III", or "IV" allowed.').show();
                } else if (parts.length > 1 && !/\./.test(lastWord) && !validSuffixes.includes(lastWord)) {
                    lastNameError.text('').hide();
                }

                value = parts.join(' ');
                $(this).val(value);
            });
            
            const usernameInput = $('#username');
            const usernameError = $('#username_error');

            usernameInput.on('input', function() {
                let value = $(this).val();

                // New: Allows [A-Za-z0-9._-]
                value = value.replace(/[^a-z0-9_]/g, '');

                // 2. Collapse consecutive underscores or periods
                value = value.replace(/([_])\1+/g, '$1');

                // 3. Remove leading underscores or periods
                value = value.replace(/^([_]+)/, '');

                $(this).val(value);

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
            });
                        
        });
    </script>
    @endpush





    