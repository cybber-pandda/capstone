@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Welcome to Dashboard</h4>
        </div>
        <!-- <div class="d-flex align-items-center flex-wrap text-nowrap">
            <div class="input-group flatpickr w-200px me-2 mb-2 mb-md-0" id="dashboardDate">
                <span class="input-group-text input-group-addon bg-transparent border-primary" data-toggle><i data-lucide="calendar" class="text-primary"></i></span>
                <input type="text" class="form-control bg-transparent border-primary" placeholder="Select date" data-input>
            </div>
            <button type="button" class="btn btn-outline-primary btn-icon-text me-2 mb-2 mb-md-0">
                <i class="btn-icon-prepend" data-lucide="printer"></i>
                Print
            </button>
            <button type="button" class="btn btn-primary btn-icon-text mb-2 mb-md-0">
                <i class="btn-icon-prepend" data-lucide="download-cloud"></i>
                Download Report
            </button>
        </div> -->
    </div>

    <div class="row">
        <div class="col-12 col-xl-12 stretch-card">
            <div class="row flex-grow-1">
                {{-- Total Customers --}}
                <div class="col-md-4 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Total Customers</h6>
                            </div>
                            <h3 class="mb-2">{{ number_format($totalB2B) }}</h3>
                            <div class="d-flex align-items-baseline">
                                <p class="mb-0 {{ $b2bChange >= 0 ? 'text-success' : 'text-danger' }}">
                                    <span>{{ $b2bChange >= 0 ? '+' : '' }}{{ number_format($b2bChange, 1) }}%</span>
                                    <i data-lucide="{{ $b2bChange >= 0 ? 'arrow-up' : 'arrow-down' }}" class="icon-sm mb-1"></i>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Sales Officer --}}
                <div class="col-md-4 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Total Sales Officer</h6>
                            </div>
                            <h3 class="mb-2">{{ number_format($totalSalesOfficer) }}</h3>
                            <div class="d-flex align-items-baseline">
                                <p class="mb-0 {{ $salesChange >= 0 ? 'text-success' : 'text-danger' }}">
                                    <span>{{ $salesChange >= 0 ? '+' : '' }}{{ number_format($salesChange, 1) }}%</span>
                                    <i data-lucide="{{ $salesChange >= 0 ? 'arrow-up' : 'arrow-down' }}" class="icon-sm mb-1"></i>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Delivery Rider --}}
                <div class="col-md-4 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h6 class="card-title mb-0">Total Delivery Rider</h6>
                            </div>
                            <h3 class="mb-2">{{ number_format($totalDeliveryRider) }}</h3>
                            <div class="d-flex align-items-baseline">
                                <p class="mb-0 {{ $riderChange >= 0 ? 'text-success' : 'text-danger' }}">
                                    <span>{{ $riderChange >= 0 ? '+' : '' }}{{ number_format($riderChange, 1) }}%</span>
                                    <i data-lucide="{{ $riderChange >= 0 ? 'arrow-up' : 'arrow-down' }}" class="icon-sm mb-1"></i>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-xl-12 grid-margin stretch-card">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-baseline mb-4 mb-md-3">
                        <h6 class="card-title mb-0">Revenue</h6>
                        <div class="dropdown">
                            <a type="button" id="dropdownMenuButton3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="icon-lg text-secondary pb-3px" data-lucide="more-horizontal"></i>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton3">
                                <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="eye" class="icon-sm me-2"></i> <span class="">View</span></a>
                                <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="edit-2" class="icon-sm me-2"></i> <span class="">Edit</span></a>
                                <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="trash" class="icon-sm me-2"></i> <span class="">Delete</span></a>
                                <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="printer" class="icon-sm me-2"></i> <span class="">Print</span></a>
                                <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="download" class="icon-sm me-2"></i> <span class="">Download</span></a>
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-start">
                        <div class="col-md-7">
                            <p class="text-secondary fs-13px mb-3 mb-md-0">Revenue is the income that a business has from its normal business activities, usually from the sale of goods and services to customers.</p>
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
                    <div id="revenueChartData"></div>
                </div>
            </div>
        </div>
    </div> <!-- row -->

    <div class="row">
        <div class="col-lg-7 col-xl-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                        <h6 class="card-title mb-0">Monthly sales</h6>
                        <div class="dropdown mb-2">
                            <a type="button" id="dropdownMenuButton4" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="icon-lg text-secondary pb-3px" data-lucide="more-horizontal"></i>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton4">
                                <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="eye" class="icon-sm me-2"></i> <span class="">View</span></a>
                                <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="edit-2" class="icon-sm me-2"></i> <span class="">Edit</span></a>
                                <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="trash" class="icon-sm me-2"></i> <span class="">Delete</span></a>
                                <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="printer" class="icon-sm me-2"></i> <span class="">Print</span></a>
                                <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="download" class="icon-sm me-2"></i> <span class="">Download</span></a>
                            </div>
                        </div>
                    </div>
                    <p class="text-secondary">Sales are activities related to selling or the number of goods or services sold in a given time period.</p>
                    <div id="monthlySalesChart"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-5 col-xl-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-baseline">
                        <h6 class="card-title mb-0">Cloud storage</h6>
                        <div class="dropdown mb-2">
                            <a type="button" id="dropdownMenuButton5" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="icon-lg text-secondary pb-3px" data-lucide="more-horizontal"></i>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton5">
                                <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="eye" class="icon-sm me-2"></i> <span class="">View</span></a>
                                <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="edit-2" class="icon-sm me-2"></i> <span class="">Edit</span></a>
                                <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="trash" class="icon-sm me-2"></i> <span class="">Delete</span></a>
                                <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="printer" class="icon-sm me-2"></i> <span class="">Print</span></a>
                                <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="download" class="icon-sm me-2"></i> <span class="">Download</span></a>
                            </div>
                        </div>
                    </div>
                    <div id="storageChart"></div>
                    <div class="row mb-3">
                        <div class="col-6 d-flex justify-content-end">
                            <div>
                                <label class="d-flex align-items-center justify-content-end fs-10px text-uppercase fw-bolder">Total storage <span class="p-1 ms-1 rounded-circle bg-secondary"></span></label>
                                <h5 class="fw-bolder mb-0 text-end">8TB</h5>
                            </div>
                        </div>
                        <div class="col-6">
                            <div>
                                <label class="d-flex align-items-center fs-10px text-uppercase fw-bolder"><span class="p-1 me-1 rounded-circle bg-primary"></span> Used storage</label>
                                <h5 class="fw-bolder mb-0">~5TB</h5>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-primary">Upgrade storage</button>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- row -->

</div>
@endsection

@push('scripts')
<script>
    let revenueChart;
    fetch('/api/revenue-data')
        .then(res => res.json())
        .then(data => {
            // Update your UI if needed
            document.querySelector('#dailyTotal').innerText = `₱${data.daily.toLocaleString()}`;
            document.querySelector('#weeklyTotal').innerText = `₱${data.weekly.toLocaleString()}`;
            document.querySelector('#monthlyTotal').innerText = `₱${data.monthly.toLocaleString()}`;

            const revenueChartOptions = {
                chart: {
                    type: "line",
                    height: '400',
                    toolbar: { show: false },
                    zoom: { enabled: false }
                },
                colors: ['#727cf5'],
                series: [{
                    name: "Revenue",
                    data: data.chart_values
                }],
                xaxis: {
                    categories: data.chart_categories,
                    type: 'category'
                },
                yaxis: {
                    title: {
                        text: 'Revenue (₱)',
                        style: { fontSize: '12px' }
                    },
                },
                stroke: {
                    width: 2,
                    curve: "smooth"
                },
                markers: {
                    size: 4
                }
            };

            if (document.querySelector("#revenueChartData")) {
                revenueChart = new ApexCharts(document.querySelector("#revenueChartData"), revenueChartOptions);
                revenueChart.render();
            }
        });
</script>
@endpush