@extends('layouts.shop')

@section('content')
<div class="section section-scrollable" style="margin-bottom: 20px;margin-top: 20px;">
    <div class="container">
        <div class="section-title d-flex justify-content-between align-items-center">
            <h3 class="title">{{ $page }}</h3>
            <button class="btn btn-primary btn-sm" id="addAddressBtn" style="margin-top:-30px;padding:5px;">Add Address</button>
        </div>

        @component('components.table', [
        'id' => 'b2bAddressTable',
        'thead' => '
        <tr>
            <th>Select</th>
            <th>Full Address</th>
            <th>Status</th>
            <th>Created At</th>
        </tr>
        ' ])
        @endcomponent
    </div>
</div>

<!-- Address Form Modal -->
<div class="modal fade" id="addressModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="addressForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header" style="border:0px">
                    <h5 class="modal-title" id="modalTitle">Add Address</h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="addressId" name="id">
                    <div class="form-group"><label>Street</label><input type="text" name="street" class="form-control" required></div>
                    <div class="form-group"><label>Barangay</label><input type="text" name="barangay" class="form-control" required></div>
                    <div class="form-group"><label>City</label><input type="text" name="city" class="form-control" required></div>
                    <div class="form-group"><label>Province</label><input type="text" name="province" class="form-control" required></div>
                    <div class="form-group"><label>Zip Code</label><input type="text" name="zip_code" class="form-control"></div>

                    <input type="hidden" name="delivery_address_lat" id="delivery_address_lat">
                    <input type="hidden" name="delivery_address_lng" id="delivery_address_lng">
                </div>
                <div class="modal-footer" style="border:0px">
                    <button type="submit" class="btn btn-primary">Next</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Map Confirmation Modal -->
<div class="modal fade" id="mapConfirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 rounded-0">
            <div class="modal-body p-0">
                <div id="libremap" style="width:100%; height:400px;"></div>
                <div class="p-3 bg-white">
                    <p class="mb-0">Please confirm this location before submitting your address.</p>
                </div>
            </div>
            <div class="modal-footer bg-light p-2" style="border:0px">
                <button id="confirmSubmitBtn" class="btn btn-sm btn-success">Confirm & Submit</button>
                <button class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let map, marker, lat, lon;
    const addressModal = $('#addressModal');
    const mapConfirmModal = $('#mapConfirmModal');
    const addressForm = $('#addressForm');

    const table = $('#b2bAddressTable').DataTable({
        processing: true,
        serverSide: true,
        fixedHeader: {
            header: true
        },
        scrollCollapse: true,
        scrollX: true,
        scrollY: 600,
        autoWidth: false,
        responsive: true,
        ajax: '{{ route("b2b.address.index") }}',
        columns: [{
                data: 'select',
                name: 'select',
                orderable: false,
                searchable: false
            },
            {
                data: 'full_address',
                name: 'full_address'
            },
            {
                data: 'status',
                name: 'status'
            },
            {
                data: 'created_at',
                name: 'created_at'
            }
        ]
    });



    $('#addAddressBtn').click(() => {
        addressForm[0].reset();
        $('#addressId').val('');
        $('#modalTitle').text('Add Address');
        addressModal.modal('show');
    });

    addressForm.on('submit', function(e) {
        e.preventDefault();

        const street = $('input[name="street"]').val();
        const barangay = $('input[name="barangay"]').val();
        const city = $('input[name="city"]').val();
        const province = $('input[name="province"]').val();
        const zip = $('input[name="zip_code"]').val();
        const fullAddress = `${street}, ${barangay}, ${city}, ${province}, ${zip}`;

        $.get('/b2b/geocode', {
            q: fullAddress
        }, function(data) {
            if (data && data.length > 0) {
                lat = parseFloat(data[0].lat);
                lon = parseFloat(data[0].lon);
                $('#delivery_address_lat').val(lat);
                $('#delivery_address_lng').val(lon);

                addressModal.modal('hide');
                mapConfirmModal.modal('show');

                mapConfirmModal.on('shown.bs.modal', function() {
                    $('#libremap').html('');
                    map = new maplibregl.Map({
                        container: 'libremap',
                        style: 'https://tiles.stadiamaps.com/styles/alidade_smooth.json',
                        center: [lon, lat],
                        zoom: 16,
                        attributionControl: true
                    });

                    map.addControl(new maplibregl.NavigationControl());

                    marker = new maplibregl.Marker({
                            color: 'red'
                        })
                        .setLngLat([lon, lat])
                        .addTo(map);

                    map.resize();
                    map.setCenter([lon, lat]);
                });
            } else {
                toast('warning', 'Geolocation failed: address not found.');
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            alert('Geocoding error: ' + errorThrown);
        });
    });

    $('#confirmSubmitBtn').click(() => {
        const id = $('#addressId').val();
        const url = id ? `/b2b/address/${id}` : '{{ route("b2b.address.store") }}';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: addressForm.serialize(),
            success: function() {
                mapConfirmModal.modal('hide');
                table.ajax.reload();
            },
            error: function() {
                toast('warning', 'Failed to save address.');
            }
        });
    });

    $(document).on('change', '.select-address', function() {
        const addressId = $(this).data('id');

        $.ajax({
            url: '/b2b/address/set-default',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: addressId
            },
            success: function() {
               table.ajax.reload();
            },
            error: function() {
                toast('warning','Failed to update default address.');
            }
        });
    });
</script>
@endpush