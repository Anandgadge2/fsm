@extends('layouts.app')

@section('content')

{{-- KPIs --}}
<div class="row g-3">

    <div class="col-md-4">
        <div class="kpi-card">
            <p>Total Foot Patrols</p>
            <h2>{{ $totalPatrols }}</h2>
        </div>
    </div>

    <div class="col-md-4">
        <div class="kpi-card">
            <p>Total Distance (KM)</p>
            <h2>{{ $totalDistance }}</h2>
        </div>
    </div>

    <div class="col-md-4">
        <div class="kpi-card">
            <p>Total Officers</p>
            <h2>{{ $totalOfficers }}</h2>
        </div>
    </div>

</div>


{{-- CHART + MAP --}}
<div class="row g-4">
   <div class="row g-3 mt-2">
    <div class="col-md-6">
        <div class="card p-3">
            <h6>Distance by Range (KM)</h6>
            <canvas id="rangeDistanceChart"
                data-labels='@json($rangeDistance->pluck("range_name"))'
                data-values='@json($rangeDistance->pluck("km"))'>
            </canvas>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-3">
            <h6>Officers by Range</h6>
            <canvas id="rangeOfficerChart"
                data-labels='@json($rangeOfficers->pluck("range_name"))'
                data-values='@json($rangeOfficers->pluck("officers"))'>
            </canvas>
        </div>
    </div>
</div>


    <div class="col-md-8">
        <div class="card p-3">
            <h6 class="fw-bold mb-2">Foot Patrol Map</h6>
            <div id="patrolMap" style="height:420px"></div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
/* -------- DONUT CHART -------- */
const ctx = document.getElementById('rangeChart');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: JSON.parse(ctx.dataset.labels),
        datasets: [{
            data: JSON.parse(ctx.dataset.values),
            backgroundColor: ['#6b8e23','#8fbc8f','#556b2f','#9acd32','#2f4f4f']
        }]
    },
    options: {
        plugins: { legend: { position: 'bottom' } }
    }
});

/* -------- MAP -------- */
const map = L.map('patrolMap').setView([21.5, 78.9], 6);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

@foreach($paths as $p)
    try {
        const geo = JSON.parse(@json($p->path_geojson));
        L.geoJSON(geo, { color: '#2e7d32' }).addTo(map);
    } catch(e){}
@endforeach
</script>
@endpush

{{-- STYLES --}}
<style>
.kpi-card {
    background: linear-gradient(135deg,#f4f8f4,#e1ece1);
    border-radius: 14px;
    padding: 18px;
    text-align: center;
    box-shadow: 0 6px 18px rgba(0,0,0,.08);
}
.kpi-card p {
    margin: 0;
    font-weight: 600;
    color: #5f6f5f;
}
.kpi-card h2 {
    margin-top: 6px;
    font-weight: 800;
}
</style>
