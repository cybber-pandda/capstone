<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="horizontal">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $page }} | {{ config('app.name', 'Laravel') }}</title>

    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet">

    <!-- Bootstrap -->
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/shop/css/bootstrap.min.css') }}" />

    <!-- Slick -->
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/shop/css/slick.css') }}" />
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/shop/css/slick-theme.css') }}" />

    <!-- nouislider -->
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/shop/css/nouislider.min.css') }}" />

    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="{{ asset('assets/shop/css/font-awesome.min.css') }}">

    <!-- Custom stlylesheet -->
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/shop/css/style.css') }}" />

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap.min.css">

    <link rel="stylesheet" href="{{ asset('assets/dashboard/vendors/sweetalert2/sweetalert2.min.css') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <link href="https://unpkg.com/maplibre-gl@2.4.0/dist/maplibre-gl.css" rel="stylesheet" />

    <style>
        @media (max-width: 767.98px) {

            html,
            body {
                height: 100%;
                margin: 0;
                overflow: hidden;
                /* Prevent body scroll */
            }

            .address-map-view{
                display: none !important;
            }

            .section-scrollable {
                height: calc(100vh - 300px);
                /* Adjust based on footer height */
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
                /* Smooth scroll on iOS */
            }

            footer {
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
                z-index: 100;
            }
        }

        .table-responsive {
            padding: 20px !important;
        }
    </style>
</head>

<body>

    @include('layouts.shop.header')

    @include('layouts.shop.navigation')

    @yield('content')

    @include('layouts.shop.footer')

    <!-- jQuery Plugins -->
    <script src="{{ asset('assets/shop/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/shop/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/shop/js/slick.min.js') }}"></script>
    <script src="{{ asset('assets/shop/js/nouislider.min.js') }}"></script>
    <script src="{{ asset('assets/shop/js/jquery.zoom.min.js') }}"></script>
    <script src="{{ asset('assets/shop/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/global.js') }}"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap.min.js"></script>

    <!-- Responsive extension -->
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap.min.js"></script>

    <script src="{{ asset('assets/dashboard/vendors/sweetalert2/sweetalert2.min.js') }}"></script>

    @auth
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        window.purchaseRequestCart = {
            !!$cartJson!!
        };

        $(document).ready(function() {
            updateCartDropdown();
        });
    </script>
    @endauth

    @stack('scripts')

</body>

</html>