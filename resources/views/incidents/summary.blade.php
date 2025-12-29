@extends('layouts.app')

@section('content')

{{-- ================= KPIs ================= --}}
<div class="row g-3 mb-4">
@foreach([
    ['Total Incidents',$kpis['total_incidents'],'danger'],
    ['Animal Sightings',$kpis['animal_sightings'],'success'],
    ['Human Impact',$kpis['human_impact'],'warning'],
    ['Water Sources',$kpis['water_sources'],'info'],
    ['Mortality',$kpis['mortality'],'dark'],
] as [$label,$value,$color])
<div class="col-md">
    <div class="card p-3 text-center shadow-sm border-start border-4 border-{{ $color }}">
        <small class="text-muted">{{ $label }}</small>
        <h4 class="fw-bold">{{ number_format($value) }}</h4>
    </div>
</div>
@endforeach
</div>

{{-- ================= REPEAT INCIDENT ZONES ================= --}}
<div class="card p-3 mb-4">
<h6 class="fw-bold mb-2">Repeat Incident Zones (≥ 3 Incidents)</h6>

<div class="table-responsive">
<table class="table table-sm table-hover align-middle sortable-table">
<thead>
<tr>
    <th data-sortable data-type="number">#</th>
    <th data-sortable>Range</th>
    <th data-sortable>Beat</th>
    <th data-sortable>Compartment</th>
    <th data-sortable data-type="number" class="text-center">Incidents</th>
</tr>
</thead>
<tbody>
@forelse($repeatZones as $i => $z)
<tr>
    <td>{{ $repeatZones->firstItem() + $i }}</td>
    <td>{{ $z->range_name ?? '—' }}</td>
    <td>{{ $z->beat_id }}</td>
    <td class="fw-semibold">{{ $z->compartment }}</td>
    <td class="text-center">
        <span class="badge bg-danger px-3">{{ $z->incidents }}</span>
    </td>
</tr>
@empty
<tr>
    <td colspan="5" class="text-center text-muted">No repeat incident zones</td>
</tr>
@endforelse
</tbody>
</table>
</div>

<div class="mt-2 d-flex justify-content-end">
    {{ $repeatZones->links('pagination::bootstrap-5') }}
</div>
</div>

{{-- ================= CHARTS ================= --}}
<div class="row g-4 mb-4">
<div class="col-md-7">
<div class="card p-3">
<h6 class="fw-bold">Incident Density by Site</h6>
<div style="height:300px"><canvas id="densityChart"></canvas></div>
</div>
</div>

<div class="col-md-5">
<div class="card p-3">
<h6 class="fw-bold">Incident Distribution</h6>
<div style="height:300px"><canvas id="typeChart"></canvas></div>
</div>
</div>
</div>

{{-- ================= HEATMAP ================= --}}
<div class="card p-3">
<h6 class="fw-bold">Incident Heatmap</h6>
<div id="incidentMap" style="height:450px;"></div>
</div>

<div class="row justify-content-center mt-4">
<div class="col-xl-10">
<div class="card p-3">
<h6 class="fw-bold">Incidents at Selected Location</h6>
<div id="incidentListPanel" class="text-muted text-center py-4">
Click on heatmap to view incidents
</div>
</div>
</div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet.heat/dist/leaflet-heat.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ========== DENSITY CHART ========== */
    new Chart(document.getElementById('densityChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($densityBySite->pluck('site_name')) !!},
            datasets: [{
                data: {!! json_encode($densityBySite->pluck('incidents')) !!},
                backgroundColor: '#1565c0',
                barThickness: 26
            }]
        },
        options: {
            plugins:{ legend:{ display:false }},
            scales:{ y:{ beginAtZero:true }}
        }
    });

    /* ========== TYPE CHART ========== */
    new Chart(document.getElementById('typeChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($typeStats->pluck('type')) !!},
            datasets: [{
                data: {!! json_encode($typeStats->pluck('total')) !!},
                backgroundColor:['#2e7d32','#1e88e5','#f9a825','#c62828']
            }]
        },
        options:{ cutout:'60%' }
    });

    /* ========== MAP ========== */
    const map = L.map('incidentMap');
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    let heat = [];
    let bounds = [];

    @foreach($heatmap as $h)
        heat.push([{{ $h->lat }}, {{ $h->lng }}, 0.8]);
        bounds.push([{{ $h->lat }}, {{ $h->lng }}]);
    @endforeach

    if (heat.length) {
        L.heatLayer(heat,{radius:28,blur:20}).addTo(map);
        map.fitBounds(bounds,{padding:[40,40]});
    } else {
        map.setView([22.5,78.5],7);
    }

    /* ========== REPEAT ZONES ========== */
    @foreach($repeatZones as $z)
        L.circle(
            [{{ $z->lat }}, {{ $z->lng }}],
            {
                radius: Math.min({{ $z->incidents }} * 300, 2000),
                color:'#c62828',
                fillOpacity:0.35
            }
        ).bindPopup(`
            <strong>{{ $z->compartment }}</strong><br>
            Incidents: {{ $z->incidents }}
        `).addTo(map);
    @endforeach

    map.on('click', e => {
        fetch(`/incidents/explorer?lat=${e.latlng.lat}&lng=${e.latlng.lng}`)
            .then(r=>r.text())
            .then(html=>{
                document.getElementById('incidentListPanel').innerHTML = html;
            });
    });
});
</script>
@endpush
