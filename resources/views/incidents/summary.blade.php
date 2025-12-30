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
    <td>{{ $z->beat_name ?? $z->beat_id ?? '—' }}</td>
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
<div class="card p-3 mb-4">
<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="fw-bold mb-0">Incident Heatmap & Zones</h6>
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-outline-primary" id="showAllIncidentsBtn">Show All</button>
        <button class="btn btn-sm btn-outline-secondary" id="toggleGeofencesBtn">Toggle Geofences</button>
    </div>
</div>

{{-- Map Tabs --}}
<div class="btn-group mb-2" role="group">
    <button type="button" class="btn btn-sm btn-outline-primary active" id="incidentMapTab">Map</button>
    <button type="button" class="btn btn-sm btn-outline-primary" id="incidentSatelliteTab">Satellite</button>
</div>

<div id="incidentMap" style="height:500px;border-radius:8px;position:relative;"></div>
<div class="mt-2 d-flex justify-content-between align-items-center">
    <div class="text-muted small">
        <i class="bi bi-info-circle"></i> Click anywhere on the map to view nearby incidents
    </div>
    <div class="text-muted small">
        Hold Ctrl + Scroll to zoom
    </div>
</div>
</div>

{{-- ================= INCIDENT DETAILS PANEL ================= --}}
<!-- <div class="row justify-content-center">
<div class="col-xl-10">
<div class="card shadow-sm">
<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <h6 class="mb-0">
        <i class="bi bi-geo-alt-fill"></i> Incidents at Selected Location
    </h6>
    <span id="locationInfo" class="badge bg-light text-dark"></span>
</div>
<div class="card-body">
<div id="incidentListPanel" class="text-center py-5 text-muted">
    <i class="bi bi-cursor-fill fs-1 mb-3 d-block opacity-50"></i>
    <p class="mb-0">Click on the heatmap above to view incidents at that location</p>
</div>
</div>
</div>
</div>
</div> -->

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
            labels: {!! json_encode($typeStats->pluck('type')->map(function($t){
                return ucwords(str_replace('_', ' ', $t));
            })) !!},
            datasets: [{
                data: {!! json_encode($typeStats->pluck('total')) !!},
                backgroundColor:['#2e7d32','#1e88e5','#f9a825','#c62828']
            }]
        },
        options:{ 
            cutout:'60%',
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    /* ========== MAP ========== */
    let incidentMap;
    let currentTileLayer;
    let satelliteTileLayer;
    let geofenceLayers = [];
    let isGeofencesVisible = true;

    // Initialize map with enhanced features
    incidentMap = L.map('incidentMap', {
        center: [22.5, 78.5],
        zoom: 7,
        zoomControl: true,
        scrollWheelZoom: false,  // Disable scroll zoom by default
        dragging: true
    });
    
    // Enable zoom with Ctrl+scroll
    incidentMap.on('wheel', function(e) {
        if (e.originalEvent.ctrlKey) {
            e.originalEvent.preventDefault();
            const delta = e.originalEvent.deltaY;
            if (delta > 0) {
                incidentMap.setZoom(incidentMap.getZoom() - 1);
            } else {
                incidentMap.setZoom(incidentMap.getZoom() + 1);
            }
        }
    });
    
    // Also handle Ctrl+scroll via keyboard events
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey) {
            incidentMap.scrollWheelZoom.enable();
        }
    });
    
    document.addEventListener('keyup', function(e) {
        if (!e.ctrlKey) {
            incidentMap.scrollWheelZoom.disable();
        }
    });

    // Default tile layer (Map)
    currentTileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(incidentMap);

    // Satellite tile layer
    satelliteTileLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '© Esri'
    });

    // Load geofences if available
    function loadIncidentGeofences() {
        // This would need to be implemented based on your geofence data
        // For now, it's a placeholder for the functionality
    }




    let heat = [];
    let bounds = [];
    let clickMarker = null; // Track the click marker

    @foreach($heatmap as $h)
        heat.push([{{ $h->lat }}, {{ $h->lng }}, 0.8]);
        bounds.push([{{ $h->lat }}, {{ $h->lng }}]);
    @endforeach

    if (heat.length) {
        L.heatLayer(heat,{radius:28,blur:20}).addTo(incidentMap);
        incidentMap.fitBounds(bounds,{padding:[40,40]});
    } else {
        incidentMap.setView([22.5,78.5],7);
    }

    /* ========== REPEAT ZONES (Red Circles) ========== */
    @foreach($repeatZones as $z)
        L.circle(
            [{{ $z->lat }}, {{ $z->lng }}],
            {
                radius: Math.min({{ $z->incidents }} * 300, 2000),
                color:'#c62828',
                fillColor:'#c62828',
                fillOpacity:0.25,
                weight: 2
            }
        ).bindPopup(`
            <div style="min-width:200px;">
                <strong style="font-size:14px;">{{ $z->compartment }}</strong><br>
                @if($z->range_name)<strong>Range:</strong> {{ $z->range_name }}<br>@endif
                <strong>Beat:</strong> {{ $z->beat_id }}<br>
                <span class="badge bg-danger mt-1">{{ $z->incidents }} Incidents</span>
            </div>
        `).addTo(incidentMap);
    @endforeach

    /* ========== MAP CLICK HANDLER ========== */
    incidentMap.on('click', e => {
        const lat = e.latlng.lat.toFixed(6);
        const lng = e.latlng.lng.toFixed(6);

        // Remove previous click marker if exists
        if (clickMarker) {
            incidentMap.removeLayer(clickMarker);
        }

        // Add new marker at click location
        clickMarker = L.marker([lat, lng], {
            icon: L.divIcon({
                className: 'custom-marker',
                html: '<div style="background:#2196F3;width:16px;height:16px;border-radius:50%;border:3px solid white;box-shadow:0 2px 4px rgba(0,0,0,0.3);"></div>',
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            })
        }).addTo(incidentMap);

        // Update location info
        document.getElementById('locationInfo').textContent = `${lat}, ${lng}`;

        // Show loading state
        document.getElementById('incidentListPanel').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted">Searching for nearby incidents...</p>
            </div>
        `;

        // Fetch nearby incidents
        fetch(`/incidents/nearby?lat=${lat}&lng=${lng}&radius=5`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(html => {
                document.getElementById('incidentListPanel').innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('incidentListPanel').innerHTML = `
                    <div class="alert alert-danger m-3">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Error loading incidents. Please try again.
                    </div>
                `;
            });
    });

    // Map control event listeners
    document.getElementById('incidentMapTab').addEventListener('click', () => switchIncidentMapType('map'));
    document.getElementById('incidentSatelliteTab').addEventListener('click', () => switchIncidentMapType('satellite'));
    document.getElementById('showAllIncidentsBtn').addEventListener('click', () => {
        incidentMap.fitBounds(bounds, { padding: [40, 40] });
    });
    document.getElementById('toggleGeofencesBtn').addEventListener('click', toggleIncidentGeofences);

    // Switch map type function
    function switchIncidentMapType(type) {
        incidentMap.removeLayer(currentTileLayer);
        
        if (type === 'satellite') {
            currentTileLayer = satelliteTileLayer;
            document.getElementById('incidentMapTab').classList.remove('active');
            document.getElementById('incidentSatelliteTab').classList.add('active');
        } else {
            currentTileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            });
            document.getElementById('incidentSatelliteTab').classList.remove('active');
            document.getElementById('incidentMapTab').classList.add('active');
        }
        
        currentTileLayer.addTo(incidentMap);
    }

    // Toggle geofences function
    function toggleIncidentGeofences() {
        isGeofencesVisible = !isGeofencesVisible;
        const btn = document.getElementById('toggleGeofencesBtn');
        
        if (isGeofencesVisible) {
            geofenceLayers.forEach(layer => layer.addTo(incidentMap));
            btn.textContent = 'Hide Geofences';
        } else {
            geofenceLayers.forEach(layer => incidentMap.removeLayer(layer));
            btn.textContent = 'Show Geofences';
        }
    }
});
</script>

<style>
/* Custom styles for incident cards */
.incident-card {
    transition: all 0.2s ease;
}
.incident-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Severity badge colors */
.severity-high { background: #c62828; }
.severity-medium { background: #f57c00; }
.severity-low { background: #388e3c; }

/* Custom marker animation */
@keyframes markerPulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.2); opacity: 0.7; }
    100% { transform: scale(1); opacity: 1; }
}

.custom-marker div {
    animation: markerPulse 1.5s ease-in-out infinite;
}

/* Map container styling */
#incidentMap {
    border: 1px solid #dee2e6;
    position: relative;
}

#incidentMap:active {
    cursor: grabbing;
}

/* Hint for Ctrl+scroll zoom */
#incidentMap::after {
    content: 'Hold Ctrl + Scroll to zoom';
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.75rem;
    z-index: 1000;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.3s;
}

#incidentMap:hover::after {
    opacity: 1;
}

.btn-group .btn.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

/* Loading state */
.spinner-border {
    width: 3rem;
    height: 3rem;
}
</style>
@endpush