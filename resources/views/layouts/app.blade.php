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

</head>

    <body>
        
        @include('layouts.header')

        @include('layouts.navigation')

        @yield('content')

        @include('layouts.footer')

        <!-- jQuery Plugins -->
        <script src="{{ asset('assets/shop/js/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/shop/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('assets/shop/js/slick.min.js') }}"></script>
        <script src="{{ asset('assets/shop/js/nouislider.min.js') }}"></script>
        <script src="{{ asset('assets/shop/js/jquery.zoom.min.js') }}"></script>
        <script src="{{ asset('assets/shop/js/main.js') }}"></script>
        <script src="{{ asset('assets/js/global.js') }}"></script>
        
        @stack('scripts')

    </body>

</html>