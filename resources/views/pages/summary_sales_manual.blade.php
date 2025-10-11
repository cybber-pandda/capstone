@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    {{-- Totals Cards --}}
    <div class="row">
        @foreach ([
            ['label' => 'VAT Exclusive Sales', 'value' => $vatExclusive],
            ['label' => 'Sales VAT', 'value' => $salesVAT],
            ['label' => 'Sales (VAT Inclusive)', 'value' => $totalInclusive],
            ['label' => 'Delivery Fee (VAT Exclusive)', 'value' => $deliveryExclusive],
            ['label' => 'Delivery VAT', 'value' => $deliveryVAT],
            ['label' => 'Delivery Fee (VAT Inclusive)', 'value' => $deliveryInclusive],
            ['label' => 'Total VAT', 'value' => $totalVAT],
            ['label' => 'Grand Total', 'value' => $grandTotal, 'highlight' => true],
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

    {{-- Company Info --}}
    <div class="mt-3 mb-4">
        @if(!empty($companySettings))
            <p><strong>Trade Name: </strong> Tantuco Construction & Trading Corporation</p>
            <p><strong>TIN: </strong> {{ $companySettings->company_vat_reg }}</p>
            <p><strong>Address: </strong> {{ $companySettings->company_address }}</p>
        @endif
    </div>

    {{-- Filters and Download --}}
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

    {{-- DataTable --}}
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
                'title' => 'Summary of Sales (Manual Order)',
                'cardtopAddButton' => false
            ])
            @component('components.table', [
                'id' => 'summarySalesTable',
                'thead' => '
                    <tr>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Items</th>
                        <th>VAT Exclusive</th>
                        <th>VAT Amount</th>
                        <th>Total (Incl. VAT)</th>
                        <th>Delivery Fee</th>
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
        let from = $('#date_from').val() || moment().startOf('month').format('YYYY-MM-DD');
        let to = $('#date_to').val() || moment().endOf('month').format('YYYY-MM-DD');
        return `/summary-sales-manualorder-api/${from}/${to}`;
    }

    let table = $('#summarySalesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: getApiUrl(),
        columns: [
            { data: 'invoice_no' },
            { data: 'customer' },
            { data: 'customer_type' },
            { data: 'total_items' },
            { data: 'vat_exclusive' },
            { data: 'vat_amount' },
            { data: 'total_inclusive' },
            { data: 'delivery_fee', defaultContent: '0.00' },
            { data: 'grand_total' }
        ]
    });

    function reloadTable() {
        table.ajax.url(getApiUrl()).load();
        toggleDownloadBtn();
    }

    function toggleDownloadBtn() {
        let from = $('#date_from').val();
        let to = $('#date_to').val();
        if (from && to) {
            $('#downloadExcel').removeClass('d-none');
        } else {
            $('#downloadExcel').addClass('d-none');
        }
    }

    // Initial toggle on page load
    toggleDownloadBtn();

    $('#date_from, #date_to').on('change', reloadTable);

    $('#clear_date').on('click', function() {
        $('#date_from').val('');
        $('#date_to').val('');
        reloadTable();
    });

    // Download as Excel behavior
    $('#downloadExcel').on('click', function() {
        let from = $('#date_from').val() || moment().startOf('month').format('YYYY-MM-DD');
        let to = $('#date_to').val() || moment().endOf('month').format('YYYY-MM-DD');
        window.location.href = `/download/summary-sales-manualorder/export/${from}/${to}`;
    });
});
</script>
@endpush
