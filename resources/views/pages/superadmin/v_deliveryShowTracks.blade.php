@extends('layouts.dashboard')

@section('content')
<div class="page-content container-xxl">

    @include('layouts.dashboard.breadcrumb') 

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
        <div class="mb-2 mb-md-0">
            <h4 class="mb-3">Delivery Tracking for Order #{{ $delivery->order->order_number ?? 'N/A' }}</h4>
        </div>
        <div class="d-flex flex-row flex-wrap">
            <div>
                <a href="{{ route('tracking.delivery.location') }}" class="btn btn-inverse-secondary">
                    <i class="link-icon" data-lucide="arrow-big-left-dash"></i> Back
                </a>
            </div>
        </div>
    </div>
    <div id="map" style="height: 470px;"></div>


</div>
@endsection

@php
$trackingData = [
'deliveryId' => $delivery->id,
'deliveryManLat' => $deliveryManLat ?? 0,
'deliveryManLng' => $deliveryManLng ?? 0,
'customerLat' => $customerLat ?? 0,
'customerLng' => $customerLng ?? 0
];
@endphp

@push('scripts')
<script>
    const tracking = {!! json_encode($trackingData) !!};
    const deliveryId = tracking.deliveryId;

    const map = L.map('map').setView([tracking.deliveryManLat, tracking.deliveryManLng], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    L.marker([tracking.deliveryManLat, tracking.deliveryManLng], {
        title: 'Starting Point'
    }).addTo(map);
    L.marker([tracking.customerLat, tracking.customerLng], {
        title: 'Customer',
        icon: L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/484/484167.png',
            iconSize: [24, 24]
        })
    }).addTo(map);

    const truckIcon = L.icon({
        iconUrl: "https://i.ibb.co/zhDLPgns/truck-1-unscreen.gif",
        iconSize: [32, 32],
        iconAnchor: [16, 16]
    });
    const liveMarker = L.marker([tracking.deliveryManLat, tracking.deliveryManLng], {
        icon: truckIcon
    }).addTo(map);

    let routingControl = L.Routing.control({
        waypoints: [
            L.latLng(tracking.deliveryManLat, tracking.deliveryManLng),
            L.latLng(tracking.customerLat, tracking.customerLng)
        ],
        routeWhileDragging: false,
        createMarker: () => null,
        addWaypoints: false
    }).addTo(map);

    function speak(text) {
        const speech = new SpeechSynthesisUtterance(text);
        window.speechSynthesis.cancel();
        window.speechSynthesis.speak(speech);
    }

    function reverseGeocodeAndSpeak(lat, lng) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
            .then(res => res.json())
            .then(data => {
                const address = data.display_name || `Lat ${lat.toFixed(5)}, Lng ${lng.toFixed(5)}`;
                speak(`Rider moved to ${address}`);
            })
            .catch(() => {
                speak(`Rider moved to Latitude ${lat.toFixed(5)}, Longitude ${lng.toFixed(5)}`);
            });
    }

    let lastLat = tracking.deliveryManLat;
    let lastLng = tracking.deliveryManLng;

    const source = new EventSource(`/api/delivery/sse/tracking/${deliveryId}`);
    source.onmessage = (event) => {
        const data = JSON.parse(event.data);
        const lat = parseFloat(data.lat);
        const lng = parseFloat(data.lng);

        if (!isNaN(lat) && !isNaN(lng)) {
            const moved = Math.sqrt(Math.pow(lat - lastLat, 2) + Math.pow(lng - lastLng, 2)) >= 0.0001;
            if (moved) {
                lastLat = lat;
                lastLng = lng;
                liveMarker.setLatLng([lat, lng]);
                reverseGeocodeAndSpeak(lat, lng);

                // 🚚 Update routing
                routingControl.setWaypoints([
                    L.latLng(lat, lng),
                    L.latLng(tracking.customerLat, tracking.customerLng)
                ]);
            }
        }
    };
    source.onerror = err => console.error("SSE error:", err);
</script>

<style>
    .leaflet-container {
        width: 100%;
        height: 100%;
    }
</style>
@endpush