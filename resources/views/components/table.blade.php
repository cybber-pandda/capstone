<div class="table-responsive">
    <table id="{{ $id ?? 'dataTable' }}" class="table dataTable">
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
