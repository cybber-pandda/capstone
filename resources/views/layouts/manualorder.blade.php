<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $page }} | {{ config('app.name', 'Laravel') }}</title>
    
    <link rel="icon" type="image/png" href="{{ asset($companySettings->company_logo  ?? 'assets/dashboard/images/noimage.png'  ) }}">

    <!-- color-modes:js -->
    <script src="{{ asset('assets/dashboard/js/color-modes.js') }}"></script>
    <!-- endinject -->

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&amp;display=swap" rel="stylesheet">
    <!-- End fonts -->

    <link rel="stylesheet" href="{{ asset('assets/dashboard/vendors/core/core.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dashboard/vendors/flatpickr/flatpickr.min.css') }}">
    
    <link rel="stylesheet" href="{{ asset('assets/dashboard/css/horizontal.css') }}">
  

    <link rel="stylesheet" href="{{ asset('assets/dashboard/vendors/datatables.net-bs5/dataTables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dashboard/vendors/datatables.net-bs5/dataTables.responsive.min.js') }}">

    <link rel="stylesheet" href="{{ asset('assets/dashboard/vendors/sweetalert2/sweetalert2.min.css') }}">

    <link type="text/css" rel="stylesheet" href="{{ asset('assets/css/modified.css') }}" />
     
     <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />

    <style>
        .swal2-title {
            font-size: 16px !important;
        }

        .swal2-loader {
            display: none !important;
        }
    </style>
    
</head>

<body>

    <div class="main-wrapper">
        
        @include('layouts.dashboard.top_manualorder_navbar')

        <div class="page-wrapper">
            @yield('content')
        </div>
    </div>

    <script src="{{ asset('assets/dashboard/vendors/jquery/jquery.min.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>

    <script src="{{ asset('assets/dashboard/vendors/core/core.js') }}"></script>
    <script src="{{ asset('assets/dashboard/vendors/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/dashboard/vendors/apexcharts/apexcharts.min.js') }}"></script>

    <script src="{{ asset('assets/dashboard/vendors/tinymce/tinymce.min.js') }}"></script>

    <script src="{{ asset('assets/dashboard/vendors/datatables.net/dataTables.js') }}"></script>
    <script src="{{ asset('assets/dashboard/vendors/datatables.net-bs5/dataTables.bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/dashboard/vendors/datatables.net-bs5/dataTables.responsive.min.js') }}"></script>
    <!-- <script src="{{ asset('assets/dashboard/js/data-table.js') }}"></script> -->

    <script src="{{ asset('assets/dashboard/vendors/sweetalert2/sweetalert2.min.js') }}"></script>

    <!-- <script src="{{ asset('assets//dashboard/js/tinymce.js') }}"></script> -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/dayjs/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs/plugin/relativeTime.js"></script>

    <script src="{{ asset('assets/js/global.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/app.js') }}"></script>
    <!-- <script src="{{ asset('assets/dashboard/js/dashboard.js') }}"></script> -->
    <script src="{{ asset('assets/dashboard/js/chat.js') }}"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    @stack('scripts')

</body>

</html>