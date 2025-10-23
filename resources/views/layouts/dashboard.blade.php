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

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

    <style>
        /* Left-align content */
        td.dt-left-int,
        th.dt-left-int {
            text-align: left !important;
            padding-left: 12px;
            /* or use margin if needed */
        }

        /* Numeric type left-aligned with margin */
        td.dt-left-int.dt-type-numeric,
        th.dt-left-int.dt-type-numeric {
            text-align: left !important;
            padding-left: 12px;
            /* adjust margin/padding as needed */
        }

        /* Numeric type left-aligned with margin */
        td.dt-left-int.dt-type-numeric,
        th.dt-left-int.dt-type-numeric {
            padding-left: 12px;
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

        .table-2 {
            border: 1px solid #ccc;
            border-collapse: collapse;
            margin: 0;
            padding: 0;
            width: 100%;
            table-layout: fixed;
        }

        .table-2 caption {
            font-size: 1.5em;
            margin: .5em 0 .75em;
        }

        .table-2 tr {
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            padding: .35em;
        }

        .table-2 th,
        .table-2 td {
            padding: .625em;
            /* text-align: center; */
        }

        .table-2 th {
            font-size: .85em;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        @media screen and (max-width: 600px) {
            .table-2 {
                border: 0;
            }

            .table-2 caption {
                font-size: 1.3em;
            }

            .table-2 thead {
                border: none;
                clip: rect(0 0 0 0);
                height: 1px;
                margin: -1px;
                overflow: hidden;
                padding: 0;
                position: absolute;
                width: 1px;
            }

            .table-2 tr {
                border-bottom: 3px solid #ddd;
                display: block;
                margin-bottom: .625em;
            }

            .table-2 td {
                border-bottom: 1px solid #ddd;
                display: block;
                font-size: .8em;
                text-align: right;
            }

            .table-2 td::before {
                content: attr(data-label);
                float: left;
                font-weight: bold;
                text-transform: uppercase;
            }

            .table-2 td:last-child {
                border-bottom: 0;
            }

            @media (max-width: 767px) {
                ul.delivery-list {
                    list-style: none;
                    padding-left: 0;
                    margin-left: 0;
                }
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

    @if($showCriticalStockModal)
    <!-- Modal -->
    <div class="modal fade" id="criticalStockModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">⚠️ Critical Stock Alert</h5>
                </div>
                <div class="modal-body">
                    <ul>
                        @foreach($criticalProducts as $product)
                        <li>
                            <strong>{{ $product['name'] }}</strong>
                            (Stock: {{ $product['current_stock'] }} / {{ $product['maximum_stock'] }})
                            <br>
                            ⚠ Critical level: {{ $product['critical_stock_level'] }} ({{ $product['critical_percent'] }})
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif


    <script src="{{ asset('assets/dashboard/vendors/jquery/jquery.min.js') }}"></script>

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

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/dayjs/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs/plugin/relativeTime.js"></script>

    <script src="{{ asset('assets/js/global.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/app.js') }}"></script>
    <!-- <script src="{{ asset('assets/dashboard/js/dashboard.js') }}"></script> -->
    <script src="{{ asset('assets/dashboard/js/chat.js') }}"></script>

    <script>
        dayjs.extend(dayjs_plugin_relativeTime);
    </script>

    @if($showCriticalStockModal)
    <script>
        $(function() {
            // Check if the modal has already been shown this session
            if (!sessionStorage.getItem('criticalStockModalShown')) {
                $('#criticalStockModal').modal('show');
                // Mark it as shown so it won't appear again this session
                sessionStorage.setItem('criticalStockModalShown', 'true');
            }

            // Optional: clear when user logs out (if you use logout button)
            $(document).on('click', '#logoutButton', function() {
                sessionStorage.removeItem('criticalStockModalShown');
            });
        });
    </script>
    @endif

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        const CURRENT_USER_ID = <?= auth()->id() ?>;

        window.assetUrl = "{{ asset('') }}";

        $(document).ready(function() {
            refreshRecentMessages();
            fetchNotifications();
            getProfileDetails(CURRENT_USER_ID);

            if (typeof tinymce !== 'undefined') {
                // Initialize TinyMCE for general textareas
                tinymce.init({
                    selector: 'textarea#content',
                    plugins: 'lists advlist', // Add advlist for advanced list options
                    toolbar: 'undo redo | formatselect | bold italic | bullist numlist outdent indent | alignleft aligncenter alignright alignjustify',
                    menubar: false
                });

            } else {
                console.error('TinyMCE is not loaded');
            }

        });
    </script>

    @stack('scripts')

</body>

</html>