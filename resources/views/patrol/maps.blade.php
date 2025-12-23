@extends('layouts.app')

@section('content')

<div class="card p-3">
    <h5 class="fw-bold mb-2">Patrol Movement Map</h5>
    <div id="map" style="height:600px;"></div>
</div>

<script>
const map = L.map('map').setView([21.9, 77.9], 10);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18
}).addTo(map);

// GeoJSON Patrol Paths
@foreach($paths as $path)
    L.geoJSON({!! $path->path_geojson !!}, {
        style: {
            color: "{{ $path->session === 'Foot' ? '#4caf50' : '#ff9800' }}",
            weight: 4
        }
    }).addTo(map);
@endforeach
</script>

@endsection
