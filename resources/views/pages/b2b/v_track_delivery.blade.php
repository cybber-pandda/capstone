@extends('layouts.shop')

@section('content')
    <div class="section section-scrollable" style="margin-bottom: 20px;">
        <div class="container">
            <div class="section-title">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 class="title mb-2">{{ $page }}</h3>
                        <p class="mb-2">Delivery Tracking for Order #{{ $delivery->order->order_number ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <a href="{{ route('b2b.delivery.index') }}" class="btn btn-sm btn-primary">
                            Back
                        </a>
                    </div>
                </div>
            </div>
            <div id="map" class="responsive-map"></div>
        </div>
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
    let lastLat = tracking.deliveryManLat;
    let lastLng = tracking.deliveryManLng;

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

    const routingControl = L.Routing.control({
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

    function fetchTrackingUpdate() {
        const source = new EventSource(`/api/delivery/sse/tracking/${tracking.deliveryId}`);

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

                    routingControl.setWaypoints([
                        L.latLng(lat, lng),
                        L.latLng(tracking.customerLat, tracking.customerLng)
                    ]);
                }
            }
        };

        source.onerror = () => {
            console.warn("SSE closed due to error");
            source.close();
        };

        setTimeout(() => {
            source.close(); // Always close after 4 seconds
        }, 4000);
    }

    // Simulate real-time tracking by polling every 5 seconds
    setInterval(fetchTrackingUpdate, 5000);
</script>

<style>
    .leaflet-container {
        width: 100%;
        height: 100%;
    }
</style>
@endpush
