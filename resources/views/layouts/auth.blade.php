<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $page }} | {{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" type="image/png" href="{{ asset($companySettings->company_logo  ?? 'assets/dashboard/images/noimage.png'  ) }}">

    <!-- Bootstrap -->
    <link type="text/css" rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/css/modified.css') }}" />

    <style>
        /*
        *
        * ==========================================
        * CUSTOM UTIL CLASSES
        * ==========================================
        *
        */

        .border-md {
            border-width: 2px;
        }

        .btn-facebook {
            background: #405D9D;
            border: none;
        }

        .btn-facebook:hover,
        .btn-facebook:focus {
            background: #314879;
        }

        .btn-twitter {
            background: #42AEEC;
            border: none;
        }

        .btn-twitter:hover,
        .btn-twitter:focus {
            background: #1799e4;
        }

        /*
        *
        * ==========================================
        * FOR DEMO PURPOSES
        * ==========================================
        *
        */

        body {
            min-height: 100vh;
        }

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

        @media (min-width: 768px) {
            .row.desktop-mt {
                margin-top: 5rem !important;
            }
        }

        .input-group-text.border-danger {
            border-top: 2px solid #dc3545;
            border-bottom: 2px solid #dc3545;
            border-left: 2px solid #dc3545;
            border-right: none;
        }

        .input-group-text.border-danger-left {
            border-top: 2px solid #dc3545;
            border-bottom: 2px solid #dc3545;
            border-right: 2px solid #dc3545;
            border-left: none;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body style="background-color:#FC6319;">
    <!-- Navbar-->
    <header class="header">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container">
                <!-- Navbar Brand -->
                <a href="#" class="navbar-brand">
                    <img src="{{ asset($companySettings->company_logo  ?? 'assets/dashboard/images/noimage.png'  ) }}" alt="" width="70">
                </a>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="row align-items-center py-2 ">
            <div class="col-md-5 pr-lg-5 mb-2 mb-md-0">
                <img src="{{ asset('assets/shop/img/tools.webp') }}" alt="hardware shop illustration" class="img-fluid d-none d-md-block mb-0">
                <h2>Welcome to <b>Tantuco</b><span class="lead">CTC</span></h2>
                <p>Access Top-Quality Hardware</p>
                <p class="font-italic text-white mb-0">Register to explore top-quality hardware tools, parts, and supplies for all your business and DIY needs.</p>
                <p class="font-italic text-white">Brought to you by <strong>TantucoCTC</strong></p>
            </div>


            <!-- Registeration Form -->
            <div class="col-md-7 col-lg-6 ml-auto">
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