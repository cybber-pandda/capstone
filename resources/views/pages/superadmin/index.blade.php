@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Welcome Sales Officer</h4>
        </div>
        <div class="d-flex align-items-center flex-wrap text-nowrap">
            <a href="{{ route('summary.sales') }}" 
               class="btn btn-outline-primary btn-icon-text me-2 mb-2 mb-md-0">
               <i class="btn-icon-prepend" data-lucide="chart-no-axes-combined"></i> 
               Summary List of Sales
            </a>

            {{-- ✅ Added Manual Order (SLS) button --}}
            <a href="{{ route('summary.sales.manualorder') }}"  
               class="btn btn-outline-primary btn-icon-text me-2 mb-2 mb-md-0">
               <i class="btn-icon-prepend" data-lucide="chart-no-axes-combined"></i> 
               Manual Order (SLS)
            </a>
        </div>
    </div>

<div class="row">
    <div class="col-12 col-xl-12 stretch-card">
        <div class="row flex-grow-1">

            {{-- Total Cash Sales --}}
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="card-title mb-2">Total Cash Sales</h6>
                        <h4 class="mb-0">₱{{ number_format($totalcashsales,2) }}</h4>
                    </div>
                </div>
            </div>

            {{-- Total Credit Sales --}}
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="card-title mb-2">Total Credit Sales</h6>
                        <h4 class="mb-0">₱{{ number_format($totalpaylater,2) }}</h4>
                    </div>
                </div>
            </div>

            {{-- Total Delivery Fee --}}
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="card-title mb-2">Total Delivery Fee</h6>
                        <h4 class="mb-0">₱{{ number_format($totalDeliveryFeeAll, 2) }}</h4>
                    </div>
                </div>
            </div>

            {{-- Total Customers --}}
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="card-title mb-2">Total Customers</h6>
                        <h4 class="mb-0">{{ number_format($totalB2BAllTime) }}</h4>
                        <p class="mb-0 {{ $b2bChange >= 0 ? 'text-success' : 'text-danger' }}">
                            <span>{{ $b2bChange >= 0 ? '+' : '' }}{{ number_format($b2bChange, 1) }}%</span>
                            <i data-lucide="{{ $b2bChange >= 0 ? 'arrow-up' : 'arrow-down' }}" class="icon-sm mb-1"></i>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Total Assistant Sales Officer --}}
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="card-title mb-2">Total Assistant Sales Officer</h6>
                        <h4 class="mb-0">{{ number_format($totalSalesOfficerAllTime) }}</h4>
                        <p class="mb-0 {{ $salesChange >= 0 ? 'text-success' : 'text-danger' }}">
                            <span>{{ $salesChange >= 0 ? '+' : '' }}{{ number_format($salesChange, 1) }}%</span>
                            <i data-lucide="{{ $salesChange >= 0 ? 'arrow-up' : 'arrow-down' }}" class="icon-sm mb-1"></i>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Total Delivery Driver --}}
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="card-title mb-2">Total Delivery Driver</h6>
                        <h4 class="mb-0">{{ number_format($totalDeliveryRiderAllTime) }}</h4>
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

    <div class="row">
        <div class="col-12 col-xl-12 grid-margin stretch-card">
            <div class="card overflow-hidden">
                <div class="card-body">
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
                </div>
            </div>
        </div>
    </div> <!-- row -->



    <div class="row">
        <div class="col-lg-7 col-xl-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                        <h6 class="card-title mb-0">Top Purchased Products (Monthly)</h6>
                        <div class="dropdown mb-2">
                            <a type="button" id="dropdownMenuButton4" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <i class="icon-lg text-secondary pb-3px" data-lucide="more-horizontal"></i>
                            </a>
                        </div>
                    </div>
                    <p class="text-secondary">
                        This chart shows the most purchased products for the current month based on the total quantity delivered. It highlights which items have the highest sales volume.
                    </p>
                    <div id="monthlySalesChart"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-5 col-xl-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body position-relative" style="min-height: 400px;">
                    <div class="d-flex justify-content-between align-items-baseline mb-3">
                        <h6 class="card-title mb-0">Inventory</h6>
                    </div>

                    <div id="inventoryPieChart" style="height: 400px;"></div>

                    <div id="noInventoryData" class="text-center text-muted position-absolute top-0 start-0 w-100 h-100"
                        style="display: none; display: flex; align-items: center; justify-content: center;z-index: 10;">
                        No data yet
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- row -->

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


    document.addEventListener("DOMContentLoaded", function() {
        fetch('/api/inventory-pie-data')
            .then(res => res.json())
            .then(data => {
                const values = data.values || [];
                const hasData = Array.isArray(values) && values.length > 0 && values.reduce((a, b) => a + b, 0) > 0;

                if (!hasData) {
                    document.getElementById('inventoryPieChart').style.display = 'none';
                    document.getElementById('noInventoryData').style.display = 'flex';
                    return;
                }

                document.getElementById('inventoryPieChart').style.display = 'block';
                document.getElementById('noInventoryData').style.display = 'none';

                const options = {
                    chart: {
                        type: 'pie',
                        height: 400
                    },
                    labels: data.labels,
                    series: data.values,
                    colors: ['#4e73df', '#f6c23e', '#e74a3b', '#36b9cc', '#1cc88a', '#858796'],
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return `${val} pcs`;
                            }
                        }
                    }
                };

                const chart = new ApexCharts(document.querySelector("#inventoryPieChart"), options);
                chart.render();
            });
    });

    document.addEventListener("DOMContentLoaded", function() {
        const monthlySalesChartElement = document.querySelector('#monthlySalesChart');

        if (monthlySalesChartElement) {
            fetch('/api/monthly-top-products')
                .then(res => res.json())
                .then(data => {
                    // Get the latest month with data
                    const lastMonthKey = Object.keys(data).reverse().find(k => data[k].length > 0);
                    const topProducts = data[lastMonthKey] || [];

                    if (!topProducts.length) {
                        monthlySalesChartElement.innerHTML = "<div class='text-muted text-center'>No sales data</div>";
                        return;
                    }

                    const productNames = topProducts.map(p => p.product);
                    const quantities = topProducts.map(p => p.quantity);

                    const monthlySalesChartOptions = {
                        chart: {
                            type: 'bar',
                            height: 318,
                            parentHeightOffset: 0,
                            foreColor: '#6c757d',
                            toolbar: {
                                show: false
                            },
                            zoom: {
                                enabled: false
                            }
                        },
                        colors: ['#727cf5'],
                        series: [{
                            name: 'Quantity Sold',
                            data: quantities
                        }],
                        xaxis: {
                            categories: productNames,
                            axisBorder: {
                                color: '#dee2e6'
                            },
                            axisTicks: {
                                color: '#dee2e6'
                            }
                        },
                        yaxis: {
                            title: {
                                text: 'Qty',
                                style: {
                                    fontSize: '12px',
                                    color: '#6c757d'
                                }
                            }
                        },
                        dataLabels: {
                            enabled: true
                        },
                        plotOptions: {
                            bar: {
                                columnWidth: "20%",
                                borderRadius: 4
                            }
                        }
                    };

                    const chart = new ApexCharts(monthlySalesChartElement, monthlySalesChartOptions);
                    chart.render();
                });
        }
    });
</script>
@endpush