@extends('layouts.app')

@section('content')

{{-- FILTER BAR --}}
<form method="GET" class="card p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-2">
            <label>Date From</label>
            <input type="date" name="from" class="form-control" value="{{ request('from') }}">
        </div>
        <div class="col-md-2">
            <label>Date To</label>
            <input type="date" name="to" class="form-control" value="{{ request('to') }}">
        </div>
        <div class="col-md-3">
            <label>Range</label>
            <select name="range" class="form-select">
                <option value="">All Ranges</option>
                @foreach($ranges as $r)
                    <option value="{{ $r }}" @selected(request('range')==$r)>
                        {{ $r }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-success w-100">Apply</button>
        </div>
    </div>
</form>
 {{-- KPI STATS --}}
{{-- KPI TILES --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 text-center h-100">
            <small class="text-muted">Total Guards</small>
            <h4 class="fw-bold">{{ $stats['total_guards'] }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center h-100">
            <small class="text-muted">Active Patrols</small>
            <h4 class="fw-bold">{{ $stats['active_patrols'] }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center h-100">
            <small class="text-muted">Completed Patrols</small>
            <h4 class="fw-bold">{{ $stats['completed_patrols'] }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center h-100">
            <small class="text-muted">Distance (KM)</small>
            <h4 class="fw-bold">{{ $stats['total_distance_km'] }}</h4>
        </div>
    </div>
</div>

{{-- MAP + DETAILS --}}
<div class="row g-3">
    <div class="col-md-9">
        <div class="card p-2">
            <h6 class="fw-bold mb-2">Patrol Movement Map</h6>
            <div id="map" style="height:650px;"></div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-2">
            <h6 class="fw-bold">Patrolling Details</h6>
            <div style="max-height:650px; overflow:auto;">
                <table class="table table-sm smart-sort">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Designation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($guards as $g)
                            <tr>
                                <td>{{ $g->name }}</td>
                                <td>{{ $g->designation }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



{{-- MAP SCRIPT --}}
<script>
const map = L.map('map').setView([22.5, 78.5], 7);

/* Base layer */
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18
}).addTo(map);

/* ===== SITE MARKERS ===== */
@foreach($sites as $s)
    L.circleMarker(
        [{{ $s->lat }}, {{ $s->lng }}],
        {
            radius: 6,
            color: '#1e88e5',
            fillColor: '#1e88e5',
            fillOpacity: 0.9
        }
    ).bindPopup(`
        <strong>{{ $s->site_name }}</strong><br>
        Type: {{ $s->type }}
    `).addTo(map);
@endforeach

/* ===== PATROL PATHS ===== */
@foreach($paths as $p)
    try {
        const geo = JSON.parse(@json($p->path_geojson));
        L.geoJSON(geo, {
            color: "{{ $p->session === 'Foot' ? '#2e7d32' : '#fb8c00' }}",
            weight: 3
        }).addTo(map);
    } catch (e) {}
@endforeach
</script>


@endsection
