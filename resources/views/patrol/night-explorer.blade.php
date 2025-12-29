@extends('layouts.app')

@section('content')

{{-- ================= KPIs ================= --}}
<div class="row g-3 mb-4">
@foreach([
    ['Total Sessions',$kpis['total_sessions']],
    ['Completed',$kpis['completed']],
    ['Ongoing',$kpis['ongoing']],
    ['Active Guards',$kpis['active_guards']],
    ['Distance (KM)',number_format($kpis['total_distance'],2)]
] as [$label,$value])
<div class="col-md">
    <div class="card p-3 text-center shadow-sm h-100">
        <small class="text-muted">{{ $label }}</small>
        <h4 class="fw-bold mt-1">{{ $value }}</h4>
    </div>
</div>
@endforeach
</div>

{{-- ================= TABLE ================= --}}
<div class="card p-3 mb-4">
    <h6 class="fw-bold mb-2">Night Patrol Sessions</h6>

    <div class="table-responsive">
        <table class="table table-striped align-middle sortable-table">
            <thead class="table-light">
            <tr>
                <th data-sortable>Guard</th>
                <th data-sortable>Type</th>
                <th data-sortable data-type="number">Start Time</th>
                <th data-sortable data-type="number">End Time</th>
                <th data-sortable data-type="number">Distance (KM)</th>
            </tr>
            </thead>
            <tbody>
            @forelse($patrols as $p)
                <tr>
                    <td>{{ $p->guard }}</td>
                    <td>{{ $p->type }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->started_at)->format('d M h:i A') }}</td>
                    <td>
                        {{ $p->ended_at
                            ? \Carbon\Carbon::parse($p->ended_at)->format('h:i A')
                            : '—'
                        }}
                    </td>
                    <td>{{ number_format($p->distance,2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No night patrol data</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $patrols->links('pagination::bootstrap-5') }}
</div>

{{-- ================= SPEED GRAPH ================= --}}
<div class="card p-3 mb-4">
    <h6 class="fw-bold">Guard Patrol Speed (KM/H)</h6>

    <div class="chart-scroll height:240px;">
        <canvas id="speedChart"></canvas>
    </div>
</div>

{{-- ================= DISTANCE GRAPH ================= --}}
<div class="card p-3 mb-4">
    <h6 class="fw-bold">Total Night Patrolling by Guard (KM)</h6>

    <div class="chart-scroll height:360px;">
        <canvas id="nightDistanceChart"></canvas>
    </div>
</div>

{{-- ================= HEATMAP ================= --}}
<div class="card p-3">
    <h6 class="fw-bold">Night Patrol Heatmap</h6>
    <div id="nightHeatMap" style="height:450px;border-radius:8px;"></div>
</div>

<style>
.chart-scroll {
    overflow-x: auto;
}
#speedChart,
#nightDistanceChart {
    min-width: 1600px;
    height: 360px !important;
}
</style>

@endsection

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

{{-- ================= SPEED CHART ================= --}}
<script>
new Chart(document.getElementById('speedChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($speedStats->pluck('guard')) !!},
        datasets: [{
            data: {!! json_encode($speedStats->pluck('speed')) !!},
            backgroundColor: '#1565c0',
            barThickness: 15
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                ticks: { autoSkip: false, maxRotation: 45, minRotation: 45 }
            },
            y: { beginAtZero: true }
        },
        plugins: { legend: { display: false } }
    }
});
</script>

{{-- ================= DISTANCE CHART ================= --}}
<script>
new Chart(document.getElementById('nightDistanceChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($nightDistanceByGuard->pluck('guard')) !!},
        datasets: [{
            data: {!! json_encode($nightDistanceByGuard->pluck('total_distance')) !!},
            backgroundColor: '#2e7d32',
            barThickness: 15
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                ticks: { autoSkip: false, maxRotation: 45, minRotation: 45 }
            },
            y: { beginAtZero: true }
        },
        plugins: { legend: { display: false } }
    }
});
</script>

{{-- ================= HEATMAP ================= --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.heat/dist/leaflet-heat.js"></script>

<script>
const heatMap = L.map('nightHeatMap').setView([22.5, 78.5], 7);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(heatMap);

let heatPoints = [];

@foreach($nightHeatmap as $h)
try {
    const geo = JSON.parse(@json($h->path_geojson));

    if (geo.type === 'LineString') {
        geo.coordinates.forEach(c => heatPoints.push([c[1], c[0], 0.8]));
    }

    if (geo.type === 'MultiLineString') {
        geo.coordinates.forEach(line =>
            line.forEach(c => heatPoints.push([c[1], c[0], 0.8]))
        );
    }
} catch(e) {}
@endforeach

if (heatPoints.length === 0) {
    console.warn('No night patrol paths found for heatmap');
}

L.heatLayer(heatPoints, {
    radius: 32,
    blur: 25,
    maxZoom: 10,
    minOpacity: 0.5
}).addTo(heatMap);
</script>

@endpush
