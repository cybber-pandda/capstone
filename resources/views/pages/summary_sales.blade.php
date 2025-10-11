@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

<div class="row">
    @foreach ([
        // Sales
        ['label' => 'VAT Exclusive Sales', 'value' => $vatExclusive],
        ['label' => 'Sales VAT', 'value' => $vatAmount],
        ['label' => 'Sales (VAT Inclusive)', 'value' => $vatExclusive + $vatAmount],

        // Delivery
        ['label' => 'Delivery Fee (VAT Exclusive)', 'value' => $deliveryExclusive],
        ['label' => 'Delivery VAT', 'value' => $deliveryVAT],
        ['label' => 'Delivery Fee (VAT Inclusive)', 'value' => $deliveryFee],

        // VAT Totals
        ['label' => 'Total VAT', 'value' => $totalVAT],

        // Grand Total
        ['label' => 'Grand Total', 'value' => $total + $deliveryFee, 'highlight' => true],
    ] as $stat)
    <div class="col-md-3 grid-margin stretch-card">
        <div class="card" 
             @if(isset($stat['highlight']) && $stat['highlight']) 
                 style="background-color: #6571ff; color: #ffffff; font-weight: bold; box-shadow: 0 4px 15px rgba(0,0,0,0.2);" 
             @endif>
            <div class="card-body">
                <h6 class="card-title mb-0">{{ $stat['label'] }}</h6>
                <h3 class="mb-2">₱{{ number_format($stat['value'], 2) }}</h3>
            </div>
        </div>
    </div>
    @endforeach
</div>

    <div class="mt-3 mb-4">
        @if(!empty($companySettings) && $companySettings)
        <p><strong>Trade Name: </strong> Tantuco Construction & Trading Corporation</p>
        <p><strong>TIN: </strong> {{ $companySettings->company_vat_reg }}</p>
        <p><strong>Address: </strong> {{ $companySettings->company_address }}</p>
        @else
        @endif
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between mb-3">
        <div class="d-flex flex-column flex-sm-row mb-2 mb-md-0">
            <input id="date_from" class="form-control me-0 me-sm-2 mb-2 mb-sm-0" type="date">
            <input id="date_to" class="form-control me-0 me-sm-2 mb-2 mb-sm-0" type="date">
            <input id="clear_date" class="form-control text-center btn btn-dark" value="CLEAR DATE">
        </div>
        <div class="mt-2 mt-md-0">
            <button id="downloadExcel" class="btn btn-primary w-100 w-md-auto">Download as Excel</button>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
            'title' => 'Summary of Sales',
            'cardtopAddButton' => false,
            ])

            @component('components.table', [
            'id' => 'summarySalesTable',
            'thead' => '
            <tr>
                <!-- <th>Date</th> -->
                <th>Invoice</th>
                <th>Customer</th>
                <th>TIN</th>
                <!-- <th>Address</th> -->
                <th>Items</th>
                <th>Total (Excl. VAT)</th>
                <th>VAT Sales</th>
                <!--th>Avg Price</th -->
               <!-- <th>Subtotal</th> -->
                <th>Total (Incl. VAT)</th>
                <th>Grand Total</th>
            </tr>
            '
            ])
            @endcomponent


            @endcomponent
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    $(function() {
        function getApiUrl() {
            let from = $('#date_from').val();
            let to = $('#date_to').val();

            if (!from) from = moment().startOf('month').format('YYYY-MM-DD');
            if (!to) to = moment().endOf('month').format('YYYY-MM-DD');

            return '/summary-sales-api/' + from + '/' + to;
        }

        let table = $('#summarySalesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: getApiUrl() // call the function, don’t pass function itself
            },
            columns: [
                // {
                //     data: 'created_at',
                //     name: 'created_at'
                // },
                {
                    data: 'invoice_no',
                    name: 'invoice_no'
                },
                {
                    data: 'customer',
                    name: 'customer'
                },
                {
                    data: 'tin',
                    name: 'tin'
                },
                // {
                //     data: 'address',
                //     name: 'address'
                // },
                {
                    data: 'total_items',
                    name: 'total_items'
                },
                //{
                //  data: 'avg_price',
                //   name: 'avg_price'
                //},
                {
                   data: 'subtotal',
                   name: 'subtotal'
                },
                {
                    data: 'vat_amount',
                    name: 'vat_amount'
                },
                {
                    data: 'vat_inclusive',
                    name: 'vat_inclusive'
                },
                {
                    data: 'grand_total',
                    name: 'grand_total'
                },
            ]
        });

        function toggleDownloadBtn() {
            let from = $('#date_from').val();
            let to = $('#date_to').val();

            if (from && to) {
                $('#downloadExcel').removeClass('d-none');
            } else {
                $('#downloadExcel').addClass('d-none');
            }
        }

        toggleDownloadBtn();

        $('#date_from, #date_to').on('change', function() {
            toggleDownloadBtn();
            table.ajax.url(getApiUrl()).load();
        });

        $('#clear_date').on('click', function() {
            $('#date_from').val('');
            $('#date_to').val('');
            toggleDownloadBtn();
            table.ajax.url(getApiUrl()).load();
        });

        $('#downloadExcel').on('click', function() {
            let from = $('#date_from').val() || moment().startOf('month').format('YYYY-MM-DD');
            let to = $('#date_to').val() || moment().endOf('month').format('YYYY-MM-DD');

            window.location.href = '/download/summary-sales/export/' + from + '/' + to;
        });
    });
</script>
@endpush