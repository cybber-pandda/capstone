@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">
    @include('layouts.dashboard.breadcrumb')

    {{-- Top Role Cards --}}
    <div class="row mb-4">
        <div class="col-md-4">
            @component('components.card', ['title' => 'B2B Users'])
            <h3 class="text-primary">{{ $userCounts['b2b'] }}</h3>
            @endcomponent
        </div>
        <div class="col-md-4">
            @component('components.card', ['title' => 'Delivery Riders'])
            <h3 class="text-info">{{ $userCounts['deliveryrider'] }}</h3>
            @endcomponent
        </div>
        <div class="col-md-4">
            @component('components.card', ['title' => 'Sales Officers'])
            <h3 class="text-success">{{ $userCounts['salesofficer'] }}</h3>
            @endcomponent
        </div>
    </div>

    {{-- User DataTable (View Only) --}}
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
            'title' => 'User Report',
            'cardtopAddButton' => false
            ])
            @component('components.table', [
            'id' => 'userReportTable',
            'thead' => '
            <tr>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Created At</th>
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
        $('#userReportTable').DataTable({
            processing: true,
            serverSide: true,
            paginationType: "simple_numbers",
            responsive: true,
            aLengthMenu: [
                [5, 10, 30, 50, -1],
                [5, 10, 30, 50, "All"],
            ],
            iDisplayLength: 10,
            language: { search: "Search: " , searchPlaceholder: "Search here"},
            fixedHeader: {
                header: true
            },
            scrollCollapse: true,
            scrollX: true,
            scrollY: 600,
            autoWidth: false,
            ajax: "{{ route('user.report') }}",
            columns: [{
                    data: 'name'
                },
                {
                    data: 'username'
                },
                {
                    data: 'email'
                },
                {
                    data: 'role'
                },
                {
                    data: 'status'
                },
                {
                    data: "created_at",
                    name: "created_at",
                    width: "15%",
                    render: function(data) {
                        return new Date(data).toLocaleString();
                    },
                },
            ]
        });
    });
</script>
@endpush