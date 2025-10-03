@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Welcome
                @if(Auth::user()->role === 'salesofficer')
                Assistant Sales Offcer
                @elseif(Auth::user()->role === 'deliveryrider')
                Delivery Driver
                @else
                Anonymous
                @endif
            </h4>
        </div>
        <div class="d-flex align-items-center flex-wrap text-nowrap">
            <!-- <div class="input-group flatpickr w-200px me-2 mb-2 mb-md-0" id="dashboardDate">
                <span class="input-group-text input-group-addon bg-transparent border-primary" data-toggle><i data-lucide="calendar" class="text-primary"></i></span>
                <input type="text" class="form-control bg-transparent border-primary" placeholder="Select date" data-input>
            </div> -->
            @if(Auth::user()->role === 'salesofficer')
            <button type="button" class="btn btn-outline-primary btn-icon-text me-2 mb-2 mb-md-0 d-none"
                data-bs-toggle="modal" data-bs-target="#salesSummaryModal">
                <i class="btn-icon-prepend" data-lucide="chart-no-axes-combined"></i>
                Summary List of Sales
            </button>
            <a href="{{ route('summary.sales') }}"  class="btn btn-outline-primary btn-icon-text me-2 mb-2 mb-md-0"><i class="btn-icon-prepend" data-lucide="chart-no-axes-combined"></i> Summary List of Sales</a>
            <a href="{{ route('summary.sales.manualorder') }}"  class="btn btn-outline-primary btn-icon-text me-2 mb-2 mb-md-0"><i class="btn-icon-prepend" data-lucide="chart-no-axes-combined"></i> Manual Order (SLS)</a>
            @endif

            <!-- Modal -->
            <!-- <div class="modal fade" id="salesSummaryModal" tabindex="-1" aria-labelledby="salesSummaryLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="" method="GET">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="salesSummaryLabel">Generate Sales Summary</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Generate Excel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div> -->

            <!-- <button type="button" class="btn btn-primary btn-icon-text mb-2 mb-md-0">
                <i class="btn-icon-prepend" data-lucide="download-cloud"></i>
                Download Report
            </button> -->
        </div>
    </div>

    @php
    function renderChange($value) {
    $class = $value >= 0 ? 'text-success' : 'text-danger';
    $icon = $value >= 0 ? 'arrow-up' : 'arrow-down';
    $prefix = $value >= 0 ? '+' : '';
    return "<p class='mb-0 $class'>
        <span>{$prefix}" . number_format($value, 1) . "%</span>
        <i data-lucide='$icon' class='icon-sm mb-1'></i>
    </p>";
    }
    @endphp

    <div class="row">
        @foreach ([
        ['label' => 'Total Pending PR', 'value' => $totalPendingPR, 'change' => $totalPendingPRChange],
        ['label' => 'Total PO Submitted PR', 'value' => $totalPOSubmittedPR, 'change' => $totalPOSubmittedPRChange],
        ['label' => 'Total Sales Order PR', 'value' => $totalSalesOrderPR, 'change' => $totalSalesOrderPRChange],
        ['label' => 'Total Delivered PR', 'value' => $totalDeliveredPR, 'change' => $totalDeliveredPRChange],
        ] as $stat)
        <div class="col-md-3 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title mb-0">{{ $stat['label'] }}</h6>
                    <h3 class="mb-2">{{ number_format($stat['value']) }}</h3>
                    <div class="d-flex align-items-baseline">
                        {!! renderChange($stat['change']) !!}
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if(Auth::user()->role === 'salesofficer')
    <div class="row">
        <div class="col-12 col-xl-12 stretch-card">
            <div class="row flex-grow-1">
                {{-- Total Paid Pay Now --}}
                <div class="col-md-6 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Total Cash Sales</h6>
                            </div>
                            <h3 class="mb-2 mt-2">{{ number_format($totalcashsales,2) }}</h3>

                        </div>
                    </div>
                </div>

                {{-- Total Paid Pay Later --}}
                <div class="col-md-6 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Total Credit Sales</h6>
                            </div>
                            <h3 class="mb-2 mt-2">{{ number_format($totalpaylater,2) }}</h3>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @endif


    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    @if(Auth::user()->role === 'deliveryrider')
                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                        <h6 class="card-title mb-3">My Assign Deliveries</h6>
                    </div>
                    @include('components.delivery-list', ['deliveries' => $deliveries])
                    @else
                    <div class="d-flex justify-content-between align-items-baseline mb-4 mb-md-3">
                        <h6 class="card-title mb-0">Sales Revenue</h6>
                    </div>
                    <div class="row align-items-start">
                        <div class="col-md-7">
                            <p class="text-secondary fs-13px mb-3 mb-md-0">Sales Revenue represents the income generated
                                by Tantuco CTC from the successful delivery of hardware product sales. It reflects the
                                earnings from regular business operations, specifically through the fulfillment of
                                delivered goods to customers.</p>
                        </div>
                        <div class="col-md-5 d-flex justify-content-md-end">
                            <div class="btn-group mb-3 mb-md-0" role="group" aria-label="Basic example">
                                <button type="button" class="btn btn-outline-primary">Today</button>
                                <button type="button" class="btn btn-outline-primary d-none d-md-block">Week</button>
                                <button type="button" class="btn btn-primary">Month</button>
                                <button type="button" class="btn btn-outline-primary">Year</button>
                            </div>
                        </div>
                    </div>
                    <div id="revenueChart"></div>
                    @endif
                </div>
            </div>
        </div>
    </div> <!-- row -->

    @component('components.modal', ['id' => 'viewAddressModal', 'size' => 'md', 'scrollable' => true])
    <div id="addressDetails"></div>
    @endcomponent

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const revenueChartElement = document.querySelector('#revenueChart');
        const buttons = document.querySelectorAll('.btn-group button');
        let revenueChart;

        function fetchAndRenderChart(filter = 'month') {
            fetch(`/api/sales-revenue-data?filter=${filter}`)
                .then(res => res.json())
                .then(data => {
                    const chartOptions = {
                        chart: {
                            type: "line",
                            height: 400,
                            parentHeightOffset: 0,
                            toolbar: {
                                show: false
                            },
                            zoom: {
                                enabled: false
                            },
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 800
                            },
                            foreColor: '#6c757d'
                        },
                        colors: ['#727cf5'],
                        grid: {
                            padding: {
                                bottom: -4
                            },
                            borderColor: '#dee2e6',
                            xaxis: {
                                lines: {
                                    show: true
                                }
                            }
                        },
                        series: [{
                            name: "Sales Revenue",
                            data: data.chart_values
                        }],
                        xaxis: {
                            type: "category",
                            categories: data.chart_categories,
                            axisBorder: {
                                color: '#dee2e6'
                            },
                            axisTicks: {
                                color: '#dee2e6'
                            },
                            crosshairs: {
                                stroke: {
                                    color: '#6c757d'
                                }
                            }
                        },
                        yaxis: {
                            min: 0,
                            title: {
                                text: 'Revenue (₱)',
                                style: {
                                    fontSize: '12px',
                                    color: '#6c757d'
                                }
                            },
                            tickAmount: 4,
                            crosshairs: {
                                stroke: {
                                    color: '#6c757d'
                                }
                            }
                        },
                        markers: {
                            size: 0
                        },
                        stroke: {
                            width: 2,
                            curve: "straight"
                        }
                    };

                    // If chart already exists, update it
                    if (revenueChart) {
                        revenueChart.updateOptions(chartOptions);
                    } else {
                        revenueChart = new ApexCharts(revenueChartElement, chartOptions);
                        revenueChart.render();
                    }
                });
        }

        // Initial fetch
        fetchAndRenderChart();

        // Button click handlers
        buttons.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active from all
                buttons.forEach(b => b.classList.remove('btn-primary'));
                buttons.forEach(b => b.classList.add('btn-outline-primary'));

                // Set active to current
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary');

                const label = this.textContent.trim().toLowerCase();
                let filter = 'month';
                if (label === 'today') filter = 'day';
                else if (label === 'week') filter = 'week';
                else if (label === 'year') filter = 'year';

                fetchAndRenderChart(filter);
            });
        });
    });

    $(document).on('click', '.pickup-btn', function() {
        const deliveryId = $(this).data('delivery-id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to start this delivery.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, pick up!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/deliveryrider/delivery/pickup/${deliveryId}`,
                    type: 'PUT',
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Delivery picked up!',
                            text: response.message,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = `/deliveryrider/delivery/tracking/${deliveryId}`;
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to update delivery.'
                        });
                    }
                });
            }
        });
    });
</script>
@endpush