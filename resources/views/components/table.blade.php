<div class="table-responsive">
    @php
        $checkTable = $id === 'partialCreditTable' ? 'style="width: 100%;"' : '';
    @endphp
    <table id="{{ $id ?? 'dataTable' }}" class="table dataTable" {!! $checkTable !!}>
        @isset($thead)
            <thead>
                {!! $thead !!}
            </thead>
        @endisset

        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
