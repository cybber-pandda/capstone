<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $page }} | {{ config('app.name', 'Laravel') }}</title>

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

    <link rel="shortcut icon" href="{{ asset('assets/dashboard/images/favicon.png') }}" />

    @auth
    @if(Auth::user()->role === 'superadmin' )
    <link rel="stylesheet" href="{{ asset('assets/dashboard/css/vertical.css') }}">
    @else
    <link rel="stylesheet" href="{{ asset('assets/dashboard/css/horizontal.css') }}">
    @endif
    @endauth

    <link rel="stylesheet" href="{{ asset('assets/dashboard/vendors/datatables.net-bs5/dataTables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dashboard/vendors/datatables.net-bs5/dataTables.responsive.min.js') }}">

    <link rel="stylesheet" href="{{ asset('assets/dashboard/vendors/sweetalert2/sweetalert2.min.css') }}">

    <link type="text/css" rel="stylesheet" href="{{ asset('assets/css/modified.css') }}" />

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />


    <style>
        td.dt-left-int,
        th.dt-left-int {
            text-align: left !important;
        }

        td.dt-left-int,
        th.dt-left-int {
            text-align: left !important;
        }

        th.dt-left-int .dt-column-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .swal2-title {
            font-size: 16px !important;
        }

        .swal2-loader {
            display: none !important;
        }

        @media (max-width: 767.98px) {
            .leaflet-routing-container {
                display: none !important;
            }
        }

        .ps--active-x>.ps__rail-x {
            display: none !important;
            background-color: transparent;
        }

        .chat-wrapper,
        .chat-wrapper .card,
        .chat-wrapper .card-body,
        .chat-content,
        .chat-aside {
            height: 79vh;
            max-height: 79vh;
            overflow: hidden;
        }

        .chat-content {
            display: flex;
            flex-direction: column;
        }

        .chat-body {
            flex-grow: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }

        @media (max-width: 991.98px) {

            /* Force full height layout on mobile */
            .chat-wrapper,
            .chat-wrapper .card,
            .chat-wrapper .card-body,
            .chat-content,
            .chat-aside {
                height: 86vh;
                max-height: 86vh;
                overflow: hidden;
            }

            .chat-content {
                display: flex;
                flex-direction: column;
            }

            .chat-body {
                flex-grow: 1;
                overflow-y: auto;
                overflow-x: hidden;
            }
        }
    </style>
</head>

<body @if(request()->routeIs('chat.index')) style="overflow: hidden;" @endif>

    <div class="main-wrapper">
        @auth
        @php
        $isSuperAdmin = Auth::user()->role === 'superadmin';
        @endphp

        @if($isSuperAdmin)
        @include('layouts.dashboard.sidebar')
        @else
        @include('layouts.dashboard.top_navbar')
        @endif

        <div class="page-wrapper">
            @if($isSuperAdmin)
            @include('layouts.dashboard.navbar')
            @endif

            @yield('content')

            @if($isSuperAdmin)
            @include('layouts.dashboard.footer')
            @endif
        </div>
        @endauth
    </div>

    <script src="{{ asset('assets/dashboard/vendors/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/dashboard/vendors/core/core.js') }}"></script>
    <script src="{{ asset('assets/dashboard/vendors/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/dashboard/vendors/apexcharts/apexcharts.min.js') }}"></script>

    <script src="{{ asset('assets/dashboard/vendors/datatables.net/dataTables.js') }}"></script>
    <script src="{{ asset('assets/dashboard/vendors/datatables.net-bs5/dataTables.bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/dashboard/vendors/datatables.net-bs5/dataTables.responsive.min.js') }}"></script>
    <!-- <script src="{{ asset('assets/dashboard/js/data-table.js') }}"></script> -->

    <script src="{{ asset('assets/dashboard/vendors/sweetalert2/sweetalert2.min.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/dayjs/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs/plugin/relativeTime.js"></script>

    <script src="{{ asset('assets/js/global.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/app.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/dashboard.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/chat.js') }}"></script>

    <script>
        dayjs.extend(dayjs_plugin_relativeTime);
    </script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        const CURRENT_USER_ID = <?= auth()->id() ?>;

        $(document).ready(function() {
            refreshRecentMessages();
            fetchNotifications();
            getProfileDetails(CURRENT_USER_ID);
        });
    </script>

    @stack('scripts')

</body>

</html>