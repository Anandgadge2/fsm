@extends('layouts.app')

@section('content')



{{-- ================= TITLE ================= --}}
<div class="mb-3">
    <h5 class="fw-bold mb-0">Patrol Analysis</h5>
</div>

{{-- ================= KPIs ================= --}}
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card kpi-card text-center p-3">
            <small class="text-muted">Total Sessions</small>
            <h4 class="fw-bold mb-0">{{ $stats['total_sessions'] }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card text-center p-3">
            <small class="text-muted">Completed</small>
            <h4 class="fw-bold mb-0">{{ $stats['completed_sessions'] }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card text-center p-3">
            <small class="text-muted">Active</small>
            <h4 class="fw-bold mb-0 text">{{ $stats['active_sessions'] }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card text-center p-3">
            <small class="text-muted">Total Distance</small>
            <h4 class="fw-bold mb-0">{{ number_format($stats['total_distance_km'], 2) }} km</h4>
        </div>
    </div>
</div>
<!-- <form method="GET" class="d-flex gap-2 mb-3 align-items-end">
    <div>
        <label class="small text-muted">Guard</label>
        <select name="user" class="form-select form-select-sm">
            <option value="">All Guards</option>
            @foreach($guardList as $u)
                <option value="{{ $u->id }}" {{ request('user') == $u->id ? 'selected' : '' }}>
                    {{ $u->name }}
                </option>
            @endforeach
        </select>
    </div>

    <input type="hidden" name="sort" value="distance_desc">

    <button class="btn btn-sm btn-primary">
        Apply
    </button>
</form> -->


{{-- ================= MAP AND SIDEBAR ================= --}}
<div class="row g-3">
    {{-- ================= MAP SECTION ================= --}}
    <div class="col-lg-9 position-relative">
        <div class="card p-2">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0">Patrol Analysis</h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" id="showAllBtn">Show All</button>
                    <button class="btn btn-sm btn-outline-secondary" id="hideGeofencesBtn">Hide Geofences</button>
                </div>
            </div>
            
            {{-- Map Tabs --}}
            <div class="btn-group mb-2" role="group">
                <button type="button" class="btn btn-sm btn-outline-primary active" id="mapTab">Map</button>
                <button type="button" class="btn btn-sm btn-outline-primary" id="satelliteTab">Satellite</button>
            </div>

            <div id="patrol-map" style="height:650px;width:100%;border-radius:8px;"></div>
            
            <div class="mt-2 d-flex justify-content-between align-items-center">
                <small class="text-muted" id="mapDistance">Map distance (client): 0.00 km</small>
                <small class="text-muted">Unattended Geofences: <span id="unattendedCount">0</span></small>
            </div>
        </div>
    </div>

    {{-- ================= SIDEBAR SESSIONS LIST ================= --}}
    <div class="col-lg-3">
        <div class="card p-2 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="fw-bold mb-0">Patrol Sessions</h6>

    <select id="sortSessions" class="form-select form-select-sm w-auto">
        <option value="">Sort</option>
        <option value="distance_desc">Distance ↓</option>
        <option value="distance_asc">Distance ↑</option>
    </select>
</div>
            <div id="sessionsList" style="max-height:720px;overflow-y:auto;">
                @foreach($sessions as $s)
                    @if($s->path_geojson)
                        @php
                            // Generate session indicator color based on session ID
                            $colors = ['#28a745', '#e91e63', '#9c27b0', '#2196f3', '#00bcd4', '#4caf50', '#ff9800', '#f44336'];
                            $indicatorColor = $colors[$s->session_id % count($colors)];
                        @endphp
                        <div class="session-card mb-3 p-3 border rounded shadow-sm"
                             data-session-id="{{ $s->session_id }}"
                             data-user-id="{{ $s->user_id }}"
                             data-status="{{ $s->status }}"
                                  data-color="{{ $indicatorColor }}"
                             style="cursor: pointer; transition: all 0.2s; background: white;">
                            {{-- Session Header with Indicator --}}
                            <div class="d-flex align-items-center mb-3">
                                <div class="session-indicator me-2" 
                                     style="width: 14px; height: 14px; border-radius: 50%; background: {{ $indicatorColor }}; flex-shrink: 0;"></div>
                                <strong class="text-primary" style="font-size: 0.95rem;">Session #{{ $s->session_id }}</strong>
                            </div>

                            {{-- User Profile Picture and Name --}}
                            <div class="d-flex align-items-center mb-3">
                                @if($s->user_profile)
                                    @php
                                        // Handle different profile picture path formats
                                        $profilePic = $s->user_profile;
                                        if (strpos($profilePic, 'http') === 0) {
                                            $profileUrl = $profilePic; // Full URL
                                        } else {
                                            $profileUrl = asset('storage/profiles/' . $profilePic);
                                        }
                                    @endphp
                                    <img src="{{ $profileUrl }}" 
                                         class="rounded-circle me-3 border border-2 user-profile-img" 
                                         style="width: 50px; height: 50px; object-fit: cover; border-color: #dee2e6 !important;" 
                                         alt="{{ $s->user_name }}"
                                         onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    
                                @else
                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3 border border-2" 
                                         style="width: 50px; height: 50px; border-color: #dee2e6 !important;">
                                        <span class="text-white fw-bold" style="font-size: 1.2rem;">{{ strtoupper(substr($s->user_name, 0, 1)) }}</span>
                                    </div>
                                @endif
                                <div class="flex-grow-1">
                                    <a href="#" class="guard-name-link text-decoration-none fw-bold d-block mb-1" 
                                       data-guard-id="{{ $s->user_id }}"
                                       style="color: #212529; font-size: 0.95rem;">
                                        {{ \App\Helpers\FormatHelper::formatName($s->user_name) }}
                                    </a>
                                    <div class="text-muted small" style="font-size: 0.8rem;">
                                        {{ $s->site_name ? $s->site_name . ($s->range_name ? ' (' . $s->range_name . ')' : '') : 'N/A' }}
                                    </div>
                                </div>
                            </div>

                            {{-- Status, Time, and Distance Buttons --}}
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge bg-success" style="font-size: 0.75rem; padding: 0.4em 0.6em;">
                                    {{ $s->status }}
                                </span>
                                <span class="badge bg-success" style="font-size: 0.75rem; padding: 0.4em 0.6em;">
                                    {{ \Carbon\Carbon::parse($s->started_at)->format('d M H:i') }}
                                </span>
                                @if($s->ended_at)
                                    <span class="badge bg-danger" style="font-size: 0.75rem; padding: 0.4em 0.6em;">
                                        {{ \Carbon\Carbon::parse($s->ended_at)->format('d M H:i') }}
                                    </span>
                                @endif
                                <span class="badge bg-primary" style="font-size: 0.75rem; padding: 0.4em 0.6em;">
                                    {{ number_format($s->distance_km, 2) }} km
                                </span>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-primary zoom-session-btn flex-fill" 
                                        data-session-id="{{ $s->session_id }}"
                                        data-user-id="{{ $s->user_id }}"
                                        style="font-size: 0.85rem;">
                                    <i class="bi bi-zoom-in"></i> Zoom
                                </button>
                                <button type="button" class="btn btn-sm btn-info text-white view-session-btn flex-fill" 
                                        data-session-id="{{ $s->session_id }}"
                                        style="font-size: 0.85rem;">
                                    <i class="bi bi-eye"></i> View Details
                                </button>
                            </div>
                        </div>
                    @endif
                @endforeach
                
                {{-- Pagination --}}
                {{-- @if(method_exists($sessions, 'links'))
                    <div class="mt-3">
                        {{ $sessions->links('pagination::bootstrap-5') }}
                    </div>
                @endif --}}
            </div>
        </div>
    </div>
</div>

{{-- ================= SESSION DETAILS MODAL ================= --}}
<div class="modal fade" id="sessionModal" tabindex="-1" aria-labelledby="sessionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sessionModalLabel">Session Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="sessionModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ================= JAVASCRIPT ================= --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.animatedmarker@1.0.0/dist/leaflet.animatedmarker.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
let map;
let currentTileLayer;
let satelliteTileLayer;
let sessionLayers = {};
let markers = {};
let activeLayers = [];
let activeUserId = null;
let geofenceLayers = [];
let isGeofencesVisible = true;
let sessionDetailsModal = null;

// Session colors by type - using brown/orange for patrol paths like in image
const sessionColors = {
    'Foot': '#8B4513',      // Brown/Saddle Brown
    'Vehicle': '#D2691E',   // Chocolate/Orange Brown
    'Bicycle': '#CD853F',   // Peru Brown
    'Other': '#A0522D'      // Sienna Brown
};

function normalizePathGeoJson(raw) {
    let data = raw;
    if (!data) return null;

    if (typeof data === 'string') {
        try {
            data = JSON.parse(data);
        } catch (e) {
            return null;
        }
    }

    if (Array.isArray(data)) {
        if (data.length === 0) return null;

        if (data[0] && typeof data[0] === 'object' && !Array.isArray(data[0]) && ('lat' in data[0] || 'lng' in data[0])) {
            const coords = data
                .map(p => [Number(p.lng), Number(p.lat)])
                .filter(c => Number.isFinite(c[0]) && Number.isFinite(c[1]));
            if (coords.length === 0) return null;
            return { type: 'LineString', coordinates: coords };
        }

        if (Array.isArray(data[0]) && data[0].length >= 2) {
            let coords = data
                .map(p => [Number(p[0]), Number(p[1])])
                .filter(c => Number.isFinite(c[0]) && Number.isFinite(c[1]));
            if (coords.length === 0) return null;

            const first = coords[0];
            const a = Math.abs(first[0]);
            const b = Math.abs(first[1]);
            if (a <= 90 && b <= 180 && a < b) {
                coords = coords.map(([x, y]) => [y, x]);
            }

            return { type: 'LineString', coordinates: coords };
        }

        return null;
    }

    if (data && typeof data === 'object' && data.type) {
        return data;
    }

    return null;
}

// Initialize map with Ctrl+scroll zoom (disabled by default)
function initMap() {
    map = L.map('patrol-map', {
        center: [22.5, 78.5],
        zoom: 7,
        zoomControl: true,
        scrollWheelZoom: false,  // Disable scroll zoom by default
        dragging: true
    });
    
    // Enable zoom with Ctrl+scroll
    map.on('wheel', function(e) {
        if (e.originalEvent.ctrlKey) {
            e.originalEvent.preventDefault();
            const delta = e.originalEvent.deltaY;
            if (delta > 0) {
                map.setZoom(map.getZoom() - 1);
            } else {
                map.setZoom(map.getZoom() + 1);
            }
        }
    });
    
    // Also handle Ctrl+scroll via keyboard events
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey) {
            map.scrollWheelZoom.enable();
        }
    });
    
    document.addEventListener('keyup', function(e) {
        if (!e.ctrlKey) {
            map.scrollWheelZoom.disable();
        }
    });

    // Default tile layer (Map)
    currentTileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Satellite tile layer
    satelliteTileLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '© Esri'
    });

    // Load geofences
    loadGeofences();

    // Load all sessions
    loadSessions();

    // Show all paths by default and fit bounds
    setTimeout(() => {
        showAllPaths();
    }, 500);
}

// Load geofences
function loadGeofences() {
    @foreach($geofences as $g)
        @if($g->type === 'Circle' && $g->lat && $g->lng)
            const circle{{ $g->id }} = L.circle([{{ $g->lat }}, {{ $g->lng }}], {
                radius: {{ $g->radius }},
                color: sessionColor,
                fillColor: '#6a1b9a',
                fillOpacity: 0.07,
                weight: 2
            }).bindPopup('{{ $g->site_name ?? "Geofence" }}');
            geofenceLayers.push(circle{{ $g->id }});
            if (isGeofencesVisible) {
                circle{{ $g->id }}.addTo(map);
            }
        @elseif($g->poly_lat_lng)
            try {
                const polyCoords = JSON.parse(@json($g->poly_lat_lng));
                const polygon{{ $g->id }} = L.polygon(
                    polyCoords.map(p => [p.lat, p.lng]),
                    {
                        color: '#6a1b9a',
                        fillColor: '#6a1b9a',
                        fillOpacity: 0.07,
                        weight: 2
                    }
                ).bindPopup('{{ $g->site_name ?? "Geofence" }}');
                geofenceLayers.push(polygon{{ $g->id }});
                if (isGeofencesVisible) {
                    polygon{{ $g->id }}.addTo(map);
                }
            } catch(e) {
                console.error('Error parsing geofence polygon:', e);
            }
        @endif
    @endforeach
}

// Load patrol sessions
function loadSessions() {
    @foreach($sessions as $s)
        @if($s->path_geojson)
            try {
                let rawPath = @json($s->path_geojson);
                if (typeof rawPath === 'string') {
                    rawPath = JSON.parse(rawPath);
                }
                const geoJson = normalizePathGeoJson(rawPath);
                if (!geoJson) {
                    throw new Error('Unsupported path format');
                }
const sessionColor = document
    .querySelector(`.session-card[data-session-id="{{ $s->session_id }}"]`)
    ?.dataset.color || '#999';
                
                // Create path layer with brown/orange color and thicker lines
                const pathLayer = L.geoJSON(geoJson, {
                    style: {
                        color: sessionColor,
                        weight: 6,  // Thicker lines like in image
                        opacity: 0.9,
                        lineCap: 'round',
                        lineJoin: 'round'
                    },
                    onEachFeature: function(feature, layer) {
                        layer.on('click', function() {
                            showSessionDetails({{ $s->session_id }});
                        });
                    }
                });
                
                // Add green direct line connecting start and end points (like in image)
                @if($s->start_lat && $s->start_lng && $s->end_lat && $s->end_lng)
                    const directLine = L.polyline([
                        [{{ $s->start_lat }}, {{ $s->start_lng }}],
                        [{{ $s->end_lat }}, {{ $s->end_lng }}]
                    ], {
                        color: '',  // Green color
                        weight: 2,
                        opacity: 0,
                        dashArray: '5, 5'
                    });
                @endif

                // Add start marker (red circular marker with white border - like in image)
                @if($s->start_lat && $s->start_lng)
                    const startMarker = L.marker([{{ $s->start_lat }}, {{ $s->start_lng }}], {
                        icon: L.divIcon({
                            className: 'start-marker',
                            html: '<div style="background: #dc3545; width: 14px; height: 14px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.5);"></div>',
                            iconSize: [14, 14],
                            iconAnchor: [7, 7]
                        })
                    }).bindPopup(`
                        <div style="min-width: 200px;">
                            <strong>Session #{{ $s->session_id }}</strong><br>
                            <strong>{{ \App\Helpers\FormatHelper::formatName($s->user_name) }}</strong><br>
                            {{ $s->site_name ? $s->site_name . ($s->range_name ? ' (' . $s->range_name . ')' : '') : 'N/A' }}<br>
                            <span style="color: #28a745;">▶</span> {{ \Carbon\Carbon::parse($s->started_at)->format('d M Y H:i') }}<br>
                            @if($s->ended_at)
                                <span style="color: #dc3545;">■</span> {{ \Carbon\Carbon::parse($s->ended_at)->format('d M Y H:i') }}<br>
                            @endif
                            <span style="color: #0d6efd;">📏</span> {{ number_format($s->distance_km, 2) }} km
                        </div>
                    `);
                @endif

                // Add end marker (red circular marker with white border - like in image)
                @if($s->end_lat && $s->end_lng)
                    const endMarker = L.marker([{{ $s->end_lat }}, {{ $s->end_lng }}], {
                        icon: L.divIcon({
                            className: 'end-marker',
                            html: '<div style="background: #dc3545; width: 14px; height: 14px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.5);"></div>',
                            iconSize: [14, 14],
                            iconAnchor: [7, 7]
                        })
                    }).bindPopup(`
                        <div style="min-width: 200px;">
                            <strong>Session #{{ $s->session_id }}</strong><br>
                            <strong>{{ \App\Helpers\FormatHelper::formatName($s->user_name) }}</strong><br>
                            {{ $s->site_name ? $s->site_name . ($s->range_name ? ' (' . $s->range_name . ')' : '') : 'N/A' }}<br>
                            <span style="color: #28a745;">▶</span> {{ \Carbon\Carbon::parse($s->started_at)->format('d M Y H:i') }}<br>
                            @if($s->ended_at)
                                <span style="color: #dc3545;">■</span> {{ \Carbon\Carbon::parse($s->ended_at)->format('d M Y H:i') }}<br>
                            @endif
                            <span style="color: #0d6efd;">📏</span> {{ number_format($s->distance_km, 2) }} km
                        </div>
                    `);
                @endif

                // Store session data
                sessionLayers[{{ $s->session_id }}] = {
                    layer: pathLayer,
                    user_id: {{ $s->user_id }},
                    user_name: "{{ $s->user_name }}",
                    session_id: {{ $s->session_id }},
                    start_marker: @if($s->start_lat && $s->start_lng) startMarker @else null @endif,
                    end_marker: @if($s->end_lat && $s->end_lng) endMarker @else null @endif,
                    direct_line: @if($s->start_lat && $s->start_lng && $s->end_lat && $s->end_lng) directLine @else null @endif,
                    session_type: '{{ $s->session }}',
                    color: sessionColor,
                };
            } catch(e) {
                console.error('Error loading session {{ $s->session_id }}:', e);
            }
        @endif
    @endforeach
}

// Show all paths
function showAllPaths() {
    clearActiveLayers();
    Object.values(sessionLayers).forEach(session => {
        session.layer.setStyle({
            color: session.color,
            weight: 5,  // Thicker paths
            opacity: 0.9
        });
        session.layer.addTo(map);
        activeLayers.push(session.layer);
        
        // Add direct line if available
        if (session.direct_line) {
            session.direct_line.addTo(map);
            activeLayers.push(session.direct_line);
        }
        
        if (session.start_marker) {
            session.start_marker.addTo(map);
            activeLayers.push(session.start_marker);
        }
        if (session.end_marker) {
            session.end_marker.addTo(map);
            activeLayers.push(session.end_marker);
        }
    });
    
    // Reset all session card styles
    document.querySelectorAll('.session-card').forEach(card => {
        card.style.border = '1px solid #dee2e6';
        card.style.boxShadow = 'none';
    });
    
    fitAllPaths();
}

// Show paths for specific user
function showUserPaths(userId) {
    clearActiveLayers();
    activeUserId = userId;
    
    const userSessions = Object.values(sessionLayers).filter(s => s.user_id == userId);
    
    if (userSessions.length === 0) {
        alert('No patrol paths found for this guard');
        return;
    }
    
    userSessions.forEach(session => {
        // Highlight paths with thicker, brighter lines
        session.layer.setStyle({
            color: session.color,
            weight: 5,  // Thicker when highlighted
            opacity: 1
        });
        session.layer.addTo(map);
        activeLayers.push(session.layer);
        
        // Add direct line if available
        if (session.direct_line) {
            session.direct_line.addTo(map);
            activeLayers.push(session.direct_line);
        }
        
        if (session.start_marker) {
            session.start_marker.addTo(map);
            activeLayers.push(session.start_marker);
        }
        if (session.end_marker) {
            session.end_marker.addTo(map);
            activeLayers.push(session.end_marker);
        }
    });
    
    // Fit bounds to user's paths
    if (userSessions.length > 0) {
        const group = L.featureGroup(userSessions.map(s => s.layer));
        if (group.getBounds && group.getBounds().isValid()) {
            map.fitBounds(group.getBounds(), { padding: [50, 50] });
        }
    }
    
    // Highlight session cards for this user
    document.querySelectorAll('.session-card').forEach(card => {
        if (parseInt(card.dataset.userId) === userId) {
            card.style.border = '2px solid #28a745';
            card.style.boxShadow = '0 4px 8px rgba(40, 167, 69, 0.3)';
        } else {
            card.style.border = '1px solid #dee2e6';
            card.style.boxShadow = 'none';
        }
    });
}


function zoomToSession(sessionId) {
    const selected = sessionLayers[sessionId];
    if (!selected) return;

    clearActiveLayers();

    Object.values(sessionLayers).forEach(s => {
        if (s.session_id === sessionId) {
            s.layer.setStyle({
                color: s.color,
                weight: 5,
                opacity: 1
            });
        } else {
            s.layer.setStyle({
                color: '#cccccc',
                weight: 5,
                opacity: 0.2
            });
        }

        s.layer.addTo(map);
        activeLayers.push(s.layer);

        if (s.direct_line) s.direct_line.addTo(map);
        if (s.start_marker) s.start_marker.addTo(map);
        if (s.end_marker) s.end_marker.addTo(map);
    });

    const group = L.featureGroup([selected.layer]);
    map.fitBounds(group.getBounds(), { padding: [60, 60] });

    document.querySelectorAll('.session-card').forEach(card => {
        card.style.border = '1px solid #dee2e6';
        card.style.boxShadow = 'none';

        if (+card.dataset.sessionId === sessionId) {
            card.style.border = `3px solid ${selected.color}`;
            card.style.boxShadow = `0 0 12px ${selected.color}`;
            card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });
}


// Clear active layers
function clearActiveLayers() {
    activeLayers.forEach(layer => {
        if (map.hasLayer(layer)) {
            map.removeLayer(layer);
        }
    });
    activeLayers = [];
    activeUserId = null;
}

// Fit bounds to all paths
function fitAllPaths() {
    const allLayers = Object.values(sessionLayers).map(s => s.layer);
    if (allLayers.length > 0) {
        const group = L.featureGroup(allLayers);
        if (group.getBounds && group.getBounds().isValid()) {
            map.fitBounds(group.getBounds(), { padding: [50, 50] });
        }
    }
}

// Enhanced session details with more information and better formatting
function showEnhancedSessionDetails(session, sessionId) {
    const modalBody = document.getElementById('sessionModalBody');
    
    // Calculate additional metrics
    const startTime = new Date(session.started_at);
    const endTime = session.ended_at ? new Date(session.ended_at) : null;
    const duration = endTime ? endTime - startTime : Date.now() - startTime;
    const durationHours = Math.floor(duration / (1000 * 60 * 60));
    const durationMinutes = Math.floor((duration % (1000 * 60 * 60)) / (1000 * 60));
    
    // Format distance
    const distance = session.distance_km || 0;
    const distanceFormatted = distance > 0 ? distance.toFixed(2) + ' km' : 'N/A';
    
    // Calculate average speed if available
    const avgSpeed = distance > 0 && duration > 0 ? 
        (distance / (duration / (1000 * 60 * 60))).toFixed(2) : 'N/A';
    
    modalBody.innerHTML = `
        <div class="session-details-enhanced">
            <!-- Session Header -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Session #${session.session_id}</h5>
                        <span class="badge bg-${session.status === 'Completed' ? 'success' : 'warning'} fs-6">
                            ${session.status}
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Primary Information -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <h6><i class="bi bi-person"></i> Guard Information</h6>
                    <p><strong>Name:</strong> ${session.user_name || 'N/A'}</p>
                    <p><strong>Type:</strong> ${session.type || 'N/A'}</p>
                    <p><strong>Session:</strong> ${session.session || 'N/A'}</p>
                </div>
                <div class="col-md-6">
                    <h6><i class="bi bi-geo-alt"></i> Location Details</h6>
                    <p><strong>Site:</strong> ${session.site_name || 'N/A'}</p>
                    <p><strong>Range:</strong> ${session.range_name || 'N/A'}</p>
                    <p><strong>Client:</strong> ${session.client_name || 'N/A'}</p>
                </div>
            </div>
            
            <!-- Performance Metrics -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">Distance</h6>
                            <h4 class="mb-0">${distanceFormatted}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">Duration</h6>
                            <h4 class="mb-0">${durationHours}h ${durationMinutes}m</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">Avg Speed</h6>
                            <h4 class="mb-0">${avgSpeed} km/h</h4>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Timeline -->
            <div class="row mb-3">
                <div class="col-12">
                    <h6><i class="bi bi-clock"></i> Timeline</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Started:</strong></p>
                            <p class="text-muted">${startTime.toLocaleString()}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Ended:</strong></p>
                            <p class="text-muted">${endTime ? endTime.toLocaleString() : '<span class="text-warning">In Progress</span>'}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="row">
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" onclick="zoomToSession(${sessionId})">
                            <i class="bi bi-zoom-in"></i> Zoom on Map
                        </button>
                                   
                    </div>
                </div>
            </div>
        </div>
    `;

    // Clean up any existing modal and backdrop first
    cleanupModal();
    
    // Get modal element
    const modalEl = document.getElementById('sessionModal');
    
    // Create new modal instance
    sessionDetailsModal = new bootstrap.Modal(modalEl, {
        backdrop: 'static',  // Use static backdrop to prevent issues
        keyboard: true,
        focus: true
    });
    
    // Show modal
    sessionDetailsModal.show();
    
    // Add cleanup event listener
    modalEl.addEventListener('hidden.bs.modal', cleanupModal, { once: true });
}

// Global cleanup function for modal
function cleanupModal() {
    // Remove any existing backdrops
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
        backdrop.remove();
    });
    
    // Reset body styles
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
    
    // Remove any remaining modal instances
    const modalEl = document.getElementById('sessionModal');
    const existingModal = bootstrap.Modal.getInstance(modalEl);
    if (existingModal) {
        existingModal.dispose();
    }
    
    // Remove highlight from session cards
    document.querySelectorAll('.session-card').forEach(card => {
        card.style.border = '';
        card.style.boxShadow = '';
    });
}

// Error toast function for better user feedback
function showErrorToast(message, sessionId = null) {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast" role="alert">
            <div class="toast-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong class="me-auto">Error</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
                ${sessionId ? `<br><small class="text-muted">Session ID: ${sessionId}</small>` : ''}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Initialize and show toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 5000
    });
    toast.show();
    
    // Remove toast element after hiding
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

// Additional helper functions for the enhanced view buttons
function playbackSession(sessionId) {
    console.log('Starting playback for session:', sessionId);
    // Hide modal first
    const modalEl = document.getElementById('sessionModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
    
    // Start playback animation
    setTimeout(() => {
        playAnimation();
    }, 500);
}

function exportSessionData(sessionId) {
    console.log('Exporting data for session:', sessionId);
    // Implement export functionality
    alert('Export functionality coming soon! Session ID: ' + sessionId);
}

// Playback animation
function playAnimation() {
    if (activeLayers.length === 0) {
        alert('Please select a guard or show all paths first');
        return;
    }
    
    // Simple animation: pulse effect
    activeLayers.forEach(layer => {
        if (layer.setStyle) {
            let opacity = 0.3;
            const interval = setInterval(() => {
                opacity = opacity === 0.3 ? 1 : 0.3;
                layer.setStyle({ opacity: opacity });
            }, 500);
            
            setTimeout(() => {
                clearInterval(interval);
                layer.setStyle({ opacity: 0.8 });
            }, 5000);
        }
    });
}

// Toggle geofences
function toggleGeofences() {
    isGeofencesVisible = !isGeofencesVisible;
    const btn = document.getElementById('hideGeofencesBtn');
    
    if (isGeofencesVisible) {
        geofenceLayers.forEach(layer => layer.addTo(map));
        btn.textContent = 'Hide Geofences';
    } else {
        geofenceLayers.forEach(layer => map.removeLayer(layer));
        btn.textContent = 'Show Geofences';
    }
}

// Switch map type
function switchMapType(type) {
    map.removeLayer(currentTileLayer);
    
    if (type === 'satellite') {
        currentTileLayer = satelliteTileLayer;
        document.getElementById('mapTab').classList.remove('active');
        document.getElementById('satelliteTab').classList.add('active');
    } else {
        currentTileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        });
        document.getElementById('satelliteTab').classList.remove('active');
        document.getElementById('mapTab').classList.add('active');
    }
    
    currentTileLayer.addTo(map);
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    initMap();

    // Ensure modal never leaves a stuck backdrop/body lock
    const sessionModalEl = document.getElementById('sessionModal');
    if (sessionModalEl) {
        sessionModalEl.addEventListener('hidden.bs.modal', function() {
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
        });
    }
    
    // User name clicks - highlight guard's paths
    document.querySelectorAll('.user-name-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const userId = parseInt(this.dataset.userId);
            showUserPaths(userId);
        });
    });
    
    // Zoom session buttons
    document.querySelectorAll('.zoom-session-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const sessionId = parseInt(this.dataset.sessionId);
            zoomToSession(sessionId);
        });
    });
    
    // Enhanced View session buttons with loading states and better error handling
    document.querySelectorAll('.view-session-btn').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.stopPropagation();
            
            const sessionId = parseInt(this.dataset.sessionId);
            const originalContent = this.innerHTML;
            
            // Show loading state
            this.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';
            this.disabled = true;
            
            try {
                // Highlight the session card while loading
                const sessionCard = document.querySelector(`.session-card[data-session-id="${sessionId}"]`);
                if (sessionCard) {
                    sessionCard.style.border = '2px solid #007bff';
                    sessionCard.style.boxShadow = '0 0 8px rgba(0,123,255,0.3)';
                }
                
                // Create AbortController for timeout
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 10000);
                
                // Fetch session details with timeout
                const response = await fetch(`/api/patrol-session/${sessionId}`, {
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                
                if (!data.session) {
                    throw new Error('No session data received');
                }
                
                // Show session details
                showEnhancedSessionDetails(data.session, sessionId);
                
                // Zoom to session on map
                zoomToSession(sessionId);
                
                // Update button to success state briefly
                this.innerHTML = '<i class="bi bi-check-circle"></i> Viewed';
                setTimeout(() => {
                    this.innerHTML = originalContent;
                    this.disabled = false;
                }, 1500);
                
            } catch (error) {
                console.error('Error loading session details:', error);
                
                // Show user-friendly error message
                let errorMessage = 'Failed to load session details';
                if (error.name === 'AbortError') {
                    errorMessage = 'Request timed out - please check connection';
                } else if (error.message.includes('404')) {
                    errorMessage = 'Session not found';
                } else if (error.message.includes('500')) {
                    errorMessage = 'Server error - please try again';
                }
                
                // Show error toast or alert
                showErrorToast(errorMessage, sessionId);
                
                // Reset button
                this.innerHTML = originalContent;
                this.disabled = false;
                
                // Remove highlight from session card
                const sessionCard = document.querySelector(`.session-card[data-session-id="${sessionId}"]`);
                if (sessionCard) {
                    sessionCard.style.border = '';
                    sessionCard.style.boxShadow = '';
                }
            }
        });
    });
    
    // Map controls
    document.getElementById('mapTab').addEventListener('click', () => switchMapType('map'));
    document.getElementById('satelliteTab').addEventListener('click', () => switchMapType('satellite'));
    document.getElementById('hideGeofencesBtn').addEventListener('click', toggleGeofences);
    document.getElementById('showAllBtn').addEventListener('click', showAllPaths);
    
    // Session card clicks - zoom to session when clicking on card (but not on buttons/links)
    document.querySelectorAll('.session-card').forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking on buttons, links, or images
            if (!e.target.closest('button') && !e.target.closest('a') && !e.target.closest('img') && !e.target.closest('.badge')) {
                const sessionId = parseInt(this.dataset.sessionId);
                zoomToSession(sessionId);
            }
        });
    });
});

// Calculate total distance
function calculateTotalDistance() {
    let totalDistance = 0;
    Object.values(sessionLayers).forEach(session => {
        // Extract distance from session data if available
        // This is a placeholder - you may need to pass distance from backend
    });
    document.getElementById('mapDistance').textContent = `Map distance (client): ${totalDistance.toFixed(2)} km`;
}


const sortSelect = document.getElementById('sortSessions');
if (sortSelect) {
    sortSelect.addEventListener('change', function () {
        const cards = Array.from(document.querySelectorAll('.session-card'));

        const sorted = cards.sort((a, b) => {
            const da = parseFloat(a.querySelector('.badge.bg-primary').innerText);
            const db = parseFloat(b.querySelector('.badge.bg-primary').innerText);
            return this.value === 'distance_desc' ? db - da : da - db;
        });

        const container = document.getElementById('sessionsList');
        sorted.forEach(card => container.appendChild(card));
    });
}


</script>

<style>
    
.session-card {
    transition: all 0.2s ease;
    border: 1px solid #dee2e6 !important;
    background: white;
}

.session-card:hover {
    background-color: #f8f9fa !important;
    border-color: #0d6efd !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
}

.user-name-link:hover {
    color: #0d6efd !important;
    text-decoration: underline !important;
}

#sessionsList::-webkit-scrollbar {
    width: 6px;
}

#sessionsList::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

#sessionsList::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

#sessionsList::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

#patrol-map {
    border-radius: 8px;
    z-index: 1;
    cursor: grab;
}

#patrol-map:active {
    cursor: grabbing;
}

/* Hint for Ctrl+scroll zoom */
#patrol-map::after {
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

#patrol-map:hover::after {
    opacity: 1;
}

.btn-group .btn.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

.session-indicator {
    flex-shrink: 0;
}
</style>

@endsection
