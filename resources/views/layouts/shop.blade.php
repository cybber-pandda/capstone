<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="horizontal">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $page ?? '' }} | {{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" type="image/png" href="{{ asset($companySettings->company_logo  ?? 'assets/dashboard/images/noimage.png'  ) }}">

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

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />

    @if(Route::is('home'))
    <style>
        @media (max-width: 767.98px) {
            .section-title {
                display: none;
            }

            html,
            body {
                height: 100%;
                margin: 0;
                /* Remove this: overflow: hidden; */
                overflow-x: hidden;
                /* only hide horizontal scroll */
                padding-top: 50px !important;
            }
        }

        .header-row {
            display: flex;
            align-items: center;
            /* vertical alignment */
            justify-content: space-between;
            /* space out logo, search, nav */
            flex-wrap: nowrap;
            /* keep everything in one row */
        }
    </style>
    @endif

    @if(!Route::is('welcome'))
    <style>
        .header-row {
            display: flex;
            align-items: center;
            /* vertical alignment */
            justify-content: space-between;
            /* space out logo, search, nav */
            flex-wrap: nowrap;
            /* keep everything in one row */
        }

        .header-row .header-logo,
        .header-row .header-search,
        .header-row .header-ctn {
            flex: 1;
            /* equal width sections */
        }

        .header-row .header-search {
            margin: 0 10px;
            /* spacing between sections */
        }

        .header-row .header-ctn {
            text-align: right;
            /* nav icons to the right */
        }

        @media (max-width: 767px) {
            .header-row .header-search input {
                width: 100%;
                /* shrink search bar on mobile */
            }
        }
    </style>
    @endif



    <style>
        @media (max-width: 767.98px) {

            html,
            body {
                height: 100%;
                margin: 0;
                /* Remove this: overflow: hidden; */
                overflow-x: hidden;
                /* only hide horizontal scroll */
                padding-top: 10px;
            }

            header {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                z-index: 101;
                /* must be higher than footer */
                background: #fff;
                /* keep background visible */
                border-bottom: 1px solid #ddd;
                /* optional separator */
            }


            .section-scrollable {
                height: auto;
                /* allow full height */
                overflow-y: visible;
                /* let pagination show */
                padding-bottom: 80px;
                /* add space for fixed footer */

                margin-bottom: 20px;
                margin-top: 20px;
            }

            .address-map-view {
                display: none !important;
            }

            footer {
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
                z-index: 100;
            }

            .col-sm-6 {
                flex: 0 0 50% !important;
                max-width: 50% !important;
            }

            /* Fix scattered product cards */
            .row [class*="col-"] {
                display: flex;
                flex-direction: column;
            }

            .product {
                display: flex;
                flex-direction: column;
                width: 100%;
                height: 100%;
            }

            .product-body {
                flex-grow: 1;
                /* stretch evenly */
            }

            #hideHeaderFormobile,
            #hideFooterFormobile,
            #hideLimitForMobile,
            #hidePaginateMobile {
                display: none !important;
            }

            #showForMobile {
                display: block !important;
            }

            .section,
            #showForMobile {
                padding-top: 0px !important;
                padding-bottom: 0px !important;
            }

            #showHeaderFormobile {
                display: block !important;
            }

            #header,
            #showHeaderFormobile {
                padding-top: 0px !important;
                padding-bottom: 0px !important;
            }

            #showPaginateMobile {
                display: block !important;
            }

            #showLimitForMobile {
                display: block !important;
            }

        }

        #showForMobile {
            display: none;
        }

        #showHeaderFormobile {
            display: none;
        }

        #showPaginateMobile {
            display: none;
        }

        #showLimitForMobile {
            display: none;
        }

        /* Make pagination responsive on mobile */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            /* allow wrapping if too many links */
        }

        .pagination>li {
            display: inline-block;
            margin: 2px;
        }

        .pagination>li>a,
        .pagination>li>span {
            padding: 6px 10px;
            font-size: 14px;
        }


        /* Make product cards equal height */
        .product-list {
            display: flex;
            flex-wrap: wrap;
        }

        .product-list .col-xs-6,
        .product-list .col-sm-6,
        .product-list .col-md-3 {
            display: flex;
        }

        .product {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #fff;
            border: 1px solid #ddd;
            padding: 0px;
            width: 100%;
        }

        /* Fix image box height */
        .product-img-wrapper {
            width: 100%;
            height: 80px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-img-wrapper img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }

        /* Keep text tidy */
        .product-body {
            flex-grow: 1;
            font-size: 12px;
        }

        .product-name {
            min-height: 32px;
            /* reserve space for 2 lines of text */
            overflow: hidden;
        }

        /* Pagination Wrapper */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 15px 0;
        }

        /* Force pagination inline */
        .pagination {
            display: flex !important;
            flex-wrap: nowrap !important;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding: 5px;
            gap: 5px;
            /* space between buttons */
        }

        /* Hide scrollbar but allow scroll */
        .pagination::-webkit-scrollbar {
            display: none;
        }

        .table-responsive {
            padding: 20px !important;
        }

        @media (max-width: 767.98px) {
            .leaflet-routing-container {
                display: none !important;
            }
        }

        /* Make header-ctn always flex row */
        .header-ctn {
            display: flex;
            justify-content: space-around;
            /* distribute icons evenly */
            align-items: center;
            /* vertically align icons */
            gap: 10px;
            /* adjust spacing between items */
            flex-wrap: nowrap;
            /* prevent wrapping to new line */
        }

        /* Remove your old flex-column override */
        @media (max-width: 767.98px) {


            .header-ctn {
                justify-content: space-between;
                /* tighter on mobile */
                gap: 5px;
            }
        }

        /* Icon + label styling */
        .header-ctn a {
            display: flex;
            flex-direction: column;
            /* icon above label */
            align-items: center;
            text-decoration: none;
            color: inherit;
        }

        .header-ctn i {
            font-size: 18px;
            /* consistent size */
            margin-bottom: 3px;
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

            #b2bAddressTable_paginate{
               display:flex !important;
               justify-content:center !important;
               margin-bottom:70px !important;
            }

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
        }

        /* Apply only for iPad Pro portrait/landscape */
        @media (min-width: 1024px) and (max-width: 1366px) {
            .dropdown-menu {
                left: -90px !important;
            }
        }


        /* iPad Mini 768px and iPad Air 820px */
        @media (min-width: 768px) and (max-width: 820px) {
            
            /* top header container */
            #top-header .container {
                display: flex !important;
                justify-content: space-between;
                align-items: center;
                flex-wrap: nowrap;
            }

            /* left and right ul */
            #top-header .header-links {
                float: none !important;
                /* cancel bootstrap floats */
                display: flex !important;
                align-items: center;
                flex-wrap: nowrap;
                margin: 0;
                padding: 0;
            }

            /* li spacing */
            #top-header .header-links li {
                margin-right: 15px;
                display: flex;
                align-items: center;
            }

            /* hide address to save space */
            .removeAddressinTablet {
                display: none !important;
            }

            .removeEmailinTablet {
                display: none !important;
            }

            /* nested div inside right ul */
            #top-header .user-dropdown>div {
                display: flex !important;
                align-items: center;
            }

            /* dropdown menu fix */
            #top-header .header-links .dropdown-menu {
                position: absolute;
            }

            /* override Bootstrap col stacking */
            #top-header .header-links.pull-left,
            #top-header .header-links.pull-right {
                width: auto !important;
                /* allow flex to control width */
            }
        }

        /* Default height for desktop */
        #map.responsive-map {
            height: 470px;
        }

        /* Smaller height for mobile devices */
        @media (max-width: 768px) {
            #map.responsive-map {
                height: 60vh;
                /* 60% of viewport height */
            }
        }

        @media (max-width: 480px) {
            #map.responsive-map {
                height: 50vh;
                /* 50% of viewport height for very small screens */
            }
        }
    </style>
</head>

<body>

    @include('layouts.shop.header')

    @include('layouts.shop.navigation')

    @yield('content')

    @include('layouts.shop.footer')

    @auth
    @php
    $showB2BModal = false;
    $showPendingRequirements = false;

    if (Auth::user()->role === 'b2b') {
    if (is_null($b2bDetails) || ($b2bDetails->status === 'rejected')) {
    $showB2BModal = true;
    } elseif ($b2bDetails->status == null) {
    $showPendingRequirements = true;
    }
    }

    @endphp
    @endauth

    @if($showB2BModal)
    <div class="modal fade" id="B2BDetailsFormModal" tabindex="-1" aria-labelledby="B2BDetailsFormModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">B2B Requirements (PDF only)</h5>
                </div>
                <div class="modal-body">
                    <form id="requirementForm" enctype="multipart/form-data" method="POST" action="{{ route('b2b.business.requirement') }}">
                        @csrf

                        @if($b2bDetails && $b2bDetails->status === 'rejected')
                        <div class="alert alert-danger">
                            Your previous submission was rejected. Please correct and resubmit.
                        </div>
                        @endif

                        <div style="margin-bottom:10px;">
                            <label for="certificate_registration" class="form-label">Certificate Registration:</label>
                            <input type="file" class="form-control" name="certificate_registration" id="certificate_registration" accept="application/pdf">
                            <div class="invalid-feedback certificate_registration_error text-danger"></div>
                        </div>

                        <div style="margin-bottom:10px;">
                            <label for="business_permit" class="form-label">Business Permit:</label>
                            <input type="file" class="form-control" name="business_permit" id="business_permit" accept="application/pdf">
                            <div class="invalid-feedback business_permit_error text-danger"></div>
                        </div>

                        <div style="margin-bottom:10px;">
                            <label for="business_name" class="form-label">Business Store Name:</label>
                            <input type="text" class="form-control" name="business_name" id="business_name">
                            <div class="invalid-feedback business_name_error text-danger"></div>
                        </div>

                        <div style="margin-bottom:10px;">
                            <label for="tin_number" class="form-label">Tin Number:</label>
                            <input type="text" class="form-control" name="tin_number" id="tin_number">
                            <div class="invalid-feedback tin_number_error text-danger"></div>
                        </div>

                        <div style="margin-bottom:10px;">
                            <label for="contact_number" class="form-label">Contact Number:</label>
                            <input type="text" class="form-control" name="contact_number" id="contact_number">
                            <div class="invalid-feedback contact_number_error text-danger"></div>
                        </div>

                        <div style="margin-bottom:10px;">
                            <label for="contact_person" class="form-label">Contact Person:</label>
                            <input type="text" class="form-control" name="contact_person" id="contact_person">
                            <div class="invalid-feedback contact_person_error text-danger"></div>
                        </div>

                        <div style="margin-bottom:10px;">
                            <label for="contact_person_number" class="form-label">Contact Person Phone #:</label>
                            <input type="text" class="form-control" name="contact_person_number" id="contact_person_number">
                            <div class="invalid-feedback contact_person_number_error text-danger"></div>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="saveRequirementBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($showPaymentModal && $overduePayment && !request()->routeIs('purchase.credit'))
    <div class="modal fade" id="overduePaymentModal" tabindex="-1" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="border:0px;">
                    <h5 class="modal-title">Payment Overdue</h5>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Payment Due: {{ number_format($overduePayment->credit_amount - $overduePayment->paid_amount, 2) }}</strong>
                        <p>Original Due Date: {{ \Carbon\Carbon::parse($overduePayment->due_date)->format('M d, Y') }}</p>
                    </div>
                    <a href="{{ route('b2b.purchase.credit') }}" class="btn btn-primary btn-block">
                        Make Payment Now
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

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

    <script src="https://unpkg.com/maplibre-gl@2.4.0/dist/maplibre-gl.js"></script>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/dayjs/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs/plugin/relativeTime.js"></script>

    @auth
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        window.purchaseRequestCart = {!!$cartJson!!};

        $(document).ready(function() {
            updateCartDropdown();
        });
    </script>

    @if($showB2BModal)
    <script>
        $(document).ready(function() {
            $('#B2BDetailsFormModal').modal({
                show: true,
                backdrop: 'static',
                keyboard: false
            });

            $('#saveRequirementBtn').click(function(e) {
                e.preventDefault();

                // Reset validation errors
                $('.invalid-feedback').text('').hide();
                $('.is-invalid').removeClass('is-invalid');

                let formData = new FormData($('#requirementForm')[0]);

                $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...');
                $(this).prop('disabled', true);

                $.ajax({
                    url: "{{ route('b2b.business.requirement') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                $('#B2BDetailsFormModal').modal('hide');
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        $('#saveRequirementBtn').html('Save Changes').prop('disabled', false);

                        if (xhr.status === 422) {
                            // Validation errors
                            let errors = xhr.responseJSON.errors;
                            for (let field in errors) {
                                let errorMessage = errors[field][0];
                                $(`#${field}`).addClass('is-invalid');
                                $(`.${field}_error`).text(errorMessage).show();
                            }
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: xhr.responseJSON.message || 'An error occurred',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    }
                });
            });

            // Clear validation when file is selected
            $('input[type="file"]').change(function() {
                let fieldName = $(this).attr('name');
                $(this).removeClass('is-invalid');
                $(`.${fieldName}_error`).text('').hide();
            });
        });
    </script>
    @endif


    <script>
    $(document).ready(function() {
        // Business Name Validation
        const businessNameInput = $('#business_name');
        const businessNameError = $('.business_name_error');

        businessNameInput.on('input', function() {
            let value = $(this).val();

            // Allow letters, numbers, spaces, and selected punctuation
            value = value.replace(/[^A-Za-z0-9 &\-',.!]/g, '');

            // Collapse multiple spaces into one
            value = value.replace(/\s{2,}/g, ' ');

            // Trim leading spaces
            value = value.trimStart();

            $(this).val(value);

            // Live error message
            if (/[^A-Za-z0-9 &\-',.!]/.test(value)) {
                businessNameError.text('Invalid character detected. Only letters, numbers, spaces, and & - \' , . ! are allowed.').show();
            } else {
                businessNameError.text('').hide();
            }
        });
        const tinInput = $('#tin_number');
        const tinError = $('.tin_number_error');

        tinInput.on('input', function() {
            let value = $(this).val();

            // 1. Remove any non-digit characters
            value = value.replace(/\D/g, '');

            // 2. Limit to 12 digits
            if (value.length > 12) {
                value = value.substring(0, 12);
            }

            // 3. Insert hyphens every 3 digits
            let formatted = value.replace(/(\d{3})(?=\d)/g, '$1-');

            $(this).val(formatted);
        });

        // Optional: prevent pasting invalid characters
        tinInput.on('paste', function(e) {
            e.preventDefault();
            let pasteData = (e.originalEvent || e).clipboardData.getData('text/plain');
            pasteData = pasteData.replace(/\D/g, '').substring(0, 12);
            let formatted = pasteData.replace(/(\d{3})(?=\d)/g, '$1-');
            $(this).val(formatted);
        });
        
const contactInput = $('#contact_number');

contactInput.on('input', function() {
    let value = $(this).val();

    // 1. Remove all invalid characters (allow digits, space, (, ))
    value = value.replace(/[^0-9\s()-]/g, '');

    // 2. Remove consecutive spaces
    value = value.replace(/\s+/g, ' ');

    // 3. Remove consecutive parentheses
    value = value.replace(/\(\(+/g, '(');
    value = value.replace(/\)\)+/g, ')');

    // 4. Optional: remove space immediately after '(' or before ')'
    value = value.replace(/\(\s+/g, '(');
    value = value.replace(/\s+\)/g, ')');

    // 5. Limit total length (you can adjust max)
    if (value.length > 14) value = value.substring(0, 14);

    $(this).val(value);
});

// Handle paste to clean input
contactInput.on('paste', function(e) {
    e.preventDefault();
    let pasteData = (e.originalEvent || e).clipboardData.getData('text/plain');
    pasteData = pasteData.replace(/[^0-9\s()]/g, '')
                         .replace(/\s+/g, ' ')
                         .replace(/\(\(+/g, '(')
                         .replace(/\)\)+/g, ')')
                         .replace(/\(\s+/g, '(')
                         .replace(/\s+\)/g, ')')
                         .substring(0, 14);

    $(this).val(pasteData);
});


                const contactPersonInput = $('#contact_person');
                const contactPersonError = $('.contact_person_error');

                contactPersonInput.on('input', function() {
                    let value = $(this).val();

                    // 1. Allow only letters and spaces
                    value = value.replace(/[^A-Za-z\s]/g, '');

                    // 2. Collapse multiple spaces into one
                    value = value.replace(/\s{2,}/g, ' ');

                    // 3. Trim leading spaces
                    value = value.trimStart();

                    // 4. Auto-capitalize each word
                    value = value.replace(/\b\w/g, c => c.toUpperCase());

                    $(this).val(value);

                    // 5. Live error messages
                    if (/[^A-Za-z\s]/.test(value)) {
                        contactPersonError.text('Only letters are allowed.').show();
                    } else if (/\s{2,}/.test(value)) {
                        contactPersonError.text('Only one space is allowed between names.').show();
                    } else {
                        contactPersonError.text('').hide();
                    }
                });

                    const phoneInput = $('#contact_phone');
                    const phoneError = $('.contact_phone_error');

                    phoneInput.on('input', function() {
                        let value = $(this).val();

                        // Remove any character that is not a digit or space
                        value = value.replace(/[^0-9\s]/g, '');

                        // Remove consecutive spaces
                        value = value.replace(/\s+/g, ' ');

                        // Trim leading/trailing spaces
                        value = value.trim();

                        $(this).val(value);

                        // Count only digits for validation
                        const digitsOnly = value.replace(/\s/g, '');

                        if (digitsOnly.length > 0 && digitsOnly.length < 11) {
                            phoneError.text('Phone number must have exactly 11 digits.').show();
                        } else if (digitsOnly.length > 11) {
                            // Trim to 11 digits
                            let trimmed = digitsOnly.substring(0, 11);
                            // Optional: reformat with spaces for readability
                            phoneInput.val(trimmed.replace(/(\d{4})(\d{3})(\d{4})/, '$1 $2 $3'));
                            phoneError.text('').hide();
                        } else {
                            phoneError.text('').hide();
                        }
                    });
                    
        const contactPersonPhoneInput = $('#contact_person_number');
        const contactPersonPhoneError = $('.contact_person_number_error');

        contactPersonPhoneInput.on('input', function() {
            let value = $(this).val();

            // Remove all non-digit characters
            value = value.replace(/\D/g, '');

            // Limit to 11 digits
            if (value.length > 11) value = value.substring(0, 11);

            // Auto-insert spaces: 0930 984 7881
            if (value.length > 7) {
                value = value.replace(/(\d{4})(\d{3})(\d{0,4})/, '$1 $2 $3');
            } else if (value.length > 4) {
                value = value.replace(/(\d{4})(\d{0,3})/, '$1 $2');
            }

            $(this).val(value);

            // Count digits only for validation
            const digitsOnly = value.replace(/\s/g, '');

        });

    // Optional: handle paste event to auto-format
    contactPersonPhoneInput.on('paste', function(e) {
        e.preventDefault();
        let pasteData = (e.originalEvent || e).clipboardData.getData('text/plain');
        pasteData = pasteData.replace(/\D/g, '').substring(0, 11);

        if (pasteData.length > 7) {
            pasteData = pasteData.replace(/(\d{4})(\d{3})(\d{0,4})/, '$1 $2 $3');
        } else if (pasteData.length > 4) {
            pasteData = pasteData.replace(/(\d{4})(\d{0,3})/, '$1 $2');
        }

        $(this).val(pasteData);
        contactPersonPhoneError.text(digitsOnly.length < 11 ? 'Phone number must have exactly 11 digits.' : '').hide();
    });
        
        // Payment Modal Logic (optional, separate)
        @if($showPaymentModal)
        if (!window.location.pathname.includes('purchase/credit')) {
            $('#overduePaymentModal').modal({
                show: true,
                backdrop: 'static',
                keyboard: false
            });
        }
        @endif
    });
    </script>

    @endauth

    @stack('scripts')

</body>

</html>