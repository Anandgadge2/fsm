@extends('layouts.app')

@section('content')

{{-- KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card kpi-card">Total Guards<br><strong>{{ $stats['total_guards'] }}</strong></div></div>
    <div class="col-md-3"><div class="card kpi-card">Active Patrols<br><strong>{{ $stats['active_patrols'] }}</strong></div></div>
    <div class="col-md-3"><div class="card kpi-card">Completed Patrols<br><strong>{{ $stats['completed_patrols'] }}</strong></div></div>
    <div class="col-md-3"><div class="card kpi-card">Distance (KM)<br><strong>{{ $stats['total_distance_km'] }}</strong></div></div>
</div>

<div class="row g-3">
    <div class="col-md-9 position-relative">
        <div class="card p-2">
            <h6 class="fw-bold mb-2">Patrol & Geo-Fence Map</h6>

            <div class="map-controls">
                <button id="showAllBtn" class="btn btn-sm btn-outline-secondary">Show All</button>
                <button id="playbackBtn" class="btn btn-sm btn-outline-primary">Playback</button>
            </div>

            <div id="map" style="height:650px;"></div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-2 h-100">
            <h6 class="fw-bold mb-2">Guards</h6>

            <div style="max-height:620px;overflow:auto;">
                <table class="table table-sm table-hover sortable-table">
                    <thead>
                        <tr>
                            <th data-sortable>User</th>
                            <th data-sortable>Designation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($guards as $g)
                        <tr class="guard-row" data-user="{{ $g->id }}">
                            <td>
                                <a href="#" class="guard-name-link" data-guard-id="{{ $g->id }}">
                                    {{ \App\Helpers\FormatHelper::formatName($g->name) }}
                                </a>
                            </td>
                            <td class="text-muted">{{ \App\Helpers\FormatHelper::formatName($g->designation ?? '') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $guards->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<script>
const map = L.map('map',{dragging:false,scrollWheelZoom:false}).setView([22.5,78.5],7);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

document.addEventListener('keydown',e=>{ if(e.ctrlKey){map.dragging.enable();map.scrollWheelZoom.enable();}});
document.addEventListener('keyup',()=>{ map.dragging.disable();map.scrollWheelZoom.disable();});

map.createPane('fencePane'); map.getPane('fencePane').style.zIndex=200;
map.createPane('pathPane');  map.getPane('pathPane').style.zIndex=400;

/* Geo-fences */
@foreach($geofences as $g)
@if($g->type==='Circle')
L.circle([{{ $g->lat }},{{ $g->lng }}],{
    pane:'fencePane',radius:{{ $g->radius }},
    color:'#6a1b9a',fillOpacity:0.07
}).addTo(map).bindPopup('{{ $g->site_name }}');
@elseif($g->poly_lat_lng)
L.polygon(
    JSON.parse(@json($g->poly_lat_lng)).map(p=>[p.lat,p.lng]),
    {pane:'fencePane',color:'#6a1b9a',fillOpacity:0.07}
).addTo(map).bindPopup('{{ $g->site_name }}');
@endif
@endforeach

/* Path storage */
const pathsByUser = {};
const sessionColors = { Foot:'#2e7d32', Vehicle:'#fb8c00', Bicycle:'#1565c0', Other:'#6d4c41' };

@foreach($paths as $uid=>$rows)
pathsByUser[{{ $uid }}] = [];
@foreach($rows as $r)
try{
    const geo = JSON.parse(@json($r->path_geojson));
    const layer = L.geoJSON(geo,{
        pane:'pathPane',
        color: sessionColors['{{ $r->session }}'] || '#999',
        weight:4
    });
    pathsByUser[{{ $uid }}].push(layer);
}catch(e){}
@endforeach
@endforeach

let activeLayers = [];
let activeUser = null;

function clearPaths(){
    activeLayers.forEach(l=>map.removeLayer(l));
    activeLayers=[];
}

/* Guard click */
document.querySelectorAll('.guard-row').forEach(row=>{
row.onclick=()=>{
    clearPaths();
    activeUser = row.dataset.user;

    if(pathsByUser[activeUser]){
        pathsByUser[activeUser].forEach(l=>{
            l.addTo(map);
            activeLayers.push(l);
        });
        map.fitBounds(L.featureGroup(activeLayers).getBounds(),{padding:[40,40]});
    }
};
});

/* Show all */
document.getElementById('showAllBtn').onclick=()=>{
    clearPaths();
    Object.values(pathsByUser).flat().forEach(l=>{
        l.addTo(map);
        activeLayers.push(l);
    });
};

/* Playback */
document.getElementById('playbackBtn').onclick=()=>{
    if(!activeLayers.length) return alert('Select a guard first');
    activeLayers.forEach(l=>{
        l.eachLayer(pl=>{ if(pl.snakeIn) pl.snakeIn(); });
    });
};
</script>

@endsection
