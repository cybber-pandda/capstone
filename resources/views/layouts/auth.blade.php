<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $page ?? 'Auth' }} | {{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" type="image/png" href="{{ asset($companySettings->company_logo ?? 'assets/dashboard/images/noimage.png') }}">

    <link type="text/css" rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/css/modified.css') }}" />

    <style>
    /* DONE */
    /* General Page */
    body {
        min-height: 100vh; 
        background-color: #ffffff;
    }

    /* Branding & Buttons */
    .btn-twitter {
        background: #6571ff;
        border: none;
        color: #fff;
    }
    .btn-twitter:hover,
    .btn-twitter:focus {
        background: #4d5ad1; /* darker shade of #6571ff */
        color: #fff;
    }

    .btn-facebook {
        background: #232323;
        border: none;
        color: #fff;
    }
    .btn-facebook:hover,
    .btn-facebook:focus {
        background: #000;
        color: #fff;
    }

    .link-orange {
        color: #6571ff;
        text-decoration: none;
    }
    .link-orange:hover {
        color: #4d5ad1;
    }

    /* Forms */
    .form-control:not(select) {
        padding: 1.5rem 0.5rem;
    }
    select.form-control {
        height: 52px;
        padding-left: 0.5rem;
    }
    .form-control::placeholder {
        color: #ccc;
        font-weight: bold;
        font-size: 0.9rem;
    }
    .form-control:focus {
        box-shadow: none;
    }

    /* Tabs (Register modal: Terms & Privacy) */
    #termsTabs .nav-link {
        color: #6c757d;
        border: none;
        border-radius: 0;
    }
    #termsTabs .nav-link.active {
        color: #6571ff;
        border-bottom: 2px solid #6571ff;
        background-color: transparent;
    }

    /* Blue button (used in modal footer) */
    .btn-orange {
        color: #fff;
        background-color: #6571ff;
        border-color: #6571ff;
    }
    .btn-orange:hover {
        background-color: #4d5ad1;
        border-color: #4250c0;
        color: #fff;
    }
    .btn-orange:focus,
    .btn-orange:active {
        background-color: #4250c0;
        border-color: #3b49b3;
        box-shadow: 0 0 0 0.25rem rgba(101, 113, 255, 0.5);
        color: #fff;
    }
    .btn-orange:disabled {
        background-color: #6571ff;
        border-color: #6571ff;
        opacity: 0.65;
        color: #fff;
    }
    
    /* Make login card 95% width on mobile */
    @media (max-width: 767.98px) {
        .row.mt-4.mt-md-5.shadow-lg.rounded.overflow-hidden {
            width: 95% !important;
            margin: auto;
        }
    }

.otp-input-fields {
  display: flex;
  justify-content: center;
  gap: 12px;
  margin: 20px 0;
  flex-wrap: nowrap; /* always one row */
}

.otp-input-fields input {
  width: 45px;
  font-size: 22px;
  text-align: center;
  border: none;
  border-bottom: 2px solid #ccc; /* underscore */
  outline: none;
  background: transparent;
  font-weight: bold;
  transition: border-color 0.2s ease-in-out;
}

/* Highlight active field */
.otp-input-fields input:focus {
  border-bottom: 2px solid #6571ff; /* your main color */
}

/* Smaller screens */
@media (max-width: 480px) {
  .otp-input-fields input {
    width: 35px;
    font-size: 18px;
  }
}

@media (max-width: 360px) {
  .otp-input-fields input {
    width: 30px;
    font-size: 16px;
  }
}


    @media (max-width: 768px) {
        .otp__digit {
            width: 45px;
            height: 50px;
            font-size: 1.3rem;
        }
    }

    @media (max-width: 400px) {
        .otp__digit {
            width: 38px;
            height: 45px;
            font-size: 1.1rem;
        }
    }

    @media (max-width: 576px) {
        .input-group .form-control {
            font-size: 14px;
            padding: 8px;
        }
        .input-group-text {
            padding: 6px 10px;
            font-size: 14px;
        }
    }

    /* 🔹 Custom Swal styling */
    .swal-custom-popup {
        border-radius: 12px !important;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15) !important;
        font-family: "Segoe UI", Roboto, sans-serif;
    }

    .swal-custom-title {
        font-size: 1.1rem !important;
        font-weight: 600 !important;
        color: #333 !important;
    }

    .swal-custom-button {
        border-radius: 8px !important;
        padding: 8px 18px !important;
        font-weight: 500 !important;
    }

    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="d-flex flex-column align-items-center justify-content-center">
<div class="container" style = "margin-bottom: 45px;">
    <div class="row mt-4 mt-md-5 shadow-lg rounded overflow-hidden">

        <!-- Left Branding Column (Desktop Only) -->
        <div class="col-md-6 d-none d-md-flex flex-column justify-content-center align-items-center text-center p-4 text-white"
             style="background:#6571ff;">
            <img src="{{ asset($companySettings->company_logo ?? 'assets/dashboard/images/noimage.png') }}" 
                 alt="Company Logo" 
                 class="img-fluid mb-3 bg-white p-2 rounded-circle" 
                 width="140">

            <h2 class="mb-2">Welcome to <b>Tantuco</b><span class="lead">CTC</span></h2>
            <p class="h5">Access Top-Quality Hardware</p>
            <p class="mb-1 px-3 px-sm-5">Register to explore top-quality hardware tools, parts, and supplies for all your business and DIY needs.</p>
            <p>Brought to you by <strong>TantucoCTC</strong></p>
        </div>

        <!-- Right Column (Form, Vertically Centered) -->
        <div class="col-12 col-md-6 bg-white d-flex flex-column justify-content-center p-4">

            <!-- Mobile Branding (Visible on Small Screens Only) -->
            <div class="text-center mb-4 d-md-none">
                <img src="{{ asset($companySettings->company_logo ?? 'assets/dashboard/images/noimage.png') }}" 
                     alt="Company Logo" 
                     class="img-fluid mb-2 bg-white p-2 rounded-circle" 
                     width="100">
                <h4 class="mb-0 mt-2 text-dark"><strong>TantucoCTC</strong></h4>
            </div>

            @yield('content')
        </div>
    </div>
</div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/global.js') }}"></script>

    <script>
        $(function() {
            $('input, select').on('focus', function() {
                $(this).parent().find('.input-group-text').css('border-color', '#80bdff');
            });
            $('input, select').on('blur', function() {
                $(this).parent().find('.input-group-text').css('border-color', '#ced4da');
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
