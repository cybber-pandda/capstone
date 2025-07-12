@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">
    @include('layouts.dashboard.breadcrumb')

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            @component('components.card', [
            'title' => 'B2B Requirements List',
            'cardtopAddButton' => false
            ])
            @component('components.table', [
            'id' => 'businessRequirementTable',
            'thead' => '
            <tr>
                <th>Customer Name</th>
                <th>Certificate Registration</th>
                <th>Business Permit</th>
                <th>Status</th>
                <th>Action</th>
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
        $('#businessRequirementTable').DataTable({
            processing: true,
            serverSide: true,
            paginationType: "simple_numbers",
            responsive: true,
            aLengthMenu: [
                [5, 10, 30, 50, -1],
                [5, 10, 30, 50, "All"],
            ],
            iDisplayLength: 10,
            language: {
                search: ""
            },
            fixedHeader: {
                header: true
            },
            scrollCollapse: true,
            scrollX: true,
            scrollY: 600,
            autoWidth: false,
            ajax: "{{ route('tracking.b2b.requirement') }}",
            columns: [{
                    data: 'customer_name'
                },
                {
                    data: 'certificate_registration',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'business_permit',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'status_badge'
                },
                {
                    data: 'action'
                },
            ],
            drawCallback: function() {
                if (typeof lucide !== "undefined") {
                    lucide.createIcons();
                }
            },
        });

        $(document).on('click', '.approve-btn', function() {
            let id = $(this).data('id');
            updateStatus(id, 'approved');
        });

        $(document).on('click', '.reject-btn', function() {
            let id = $(this).data('id');
            updateStatus(id, 'rejected');
        });

        function updateStatus(id, status) {
            Swal.fire({
                title: 'Confirm Action',
                text: `Are you sure you want to ${status} this requirement?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: `Yes, ${status}`,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/b2b/requirement/update-status',
                        method: 'POST',
                        data: {
                            id: id,
                            status: status
                        },
                        success: function(response) {
                            $('#businessRequirementTable').DataTable().ajax.reload();
                            toast('success', `Requirement ${status} successfully`);
                        }
                    });
                }
            });
        }

    });
</script>
@endpush