@extends('layouts.app')

@section('content')
<!-- Global Filters -->
@include('partials.global-filters')

<!-- Guard Header -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        @if($guard->profile_pic)
                        <img src="{{ asset('storage/profiles/' . $guard->profile_pic) }}" 
                             class="rounded-circle border border-3 border-primary" 
                             style="width: 120px; height: 120px; object-fit: cover;" 
                             alt="{{ $guard->name }}">
                        @else
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                             style="width: 120px; height: 120px;">
                            <i class="fas fa-user-tie text-white fa-3x"></i>
                        </div>
                        @endif
                    </div>
                    <div class="col-md-7">
                        <h3 class="mb-1">{{ \App\Helpers\FormatHelper::formatName($guard->name) }}</h3>
                        <p class="text-muted mb-2">
                            <i class="fas fa-id-badge"></i> ID: {{ $guard->gen_id }}
                            @if($guard->designation) • <i class="fas fa-briefcase"></i> {{ $guard->designation }} @endif
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-phone"></i> {{ $guard->contact }}
                            @if($guard->email) • <i class="fas fa-envelope"></i> {{ $guard->email }} @endif
                        </p>
                        @if($guard->address)
                        <p class="mb-0">
                            <i class="fas fa-map-marker-alt"></i> {{ $guard->address }}
                        </p>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <div class="row g-2 text-center">
                            <div class="col-6">
                                <div class="bg-primary bg-opacity-10 rounded p-2">
                                    <h5 class="mb-0 text-primary">{{ $stats['total_sessions'] }}</h5>
                                    <small class="text-muted">Total Sessions</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-success bg-opacity-10 rounded p-2">
                                    <h5 class="mb-0 text-success">{{ $stats['completed_sessions'] }}</h5>
                                    <small class="text-muted">Completed</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-info bg-opacity-10 rounded p-2">
                                    <h5 class="mb-0 text-info">{{ $stats['total_distance_km'] }} km</h5>
                                    <small class="text-muted">Total Distance</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-warning bg-opacity-10 rounded p-2">
                                    <h5 class="mb-0 text-warning">{{ $stats['sites_covered'] }}</h5>
                                    <small class="text-muted">Sites Covered</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Statistics -->
<div class="row g-3 mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Performance Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <div class="text-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-block">
                                <i class="fas fa-route text-primary fa-2x"></i>
                            </div>
                            <h6 class="mt-2">Total Sessions</h6>
                            <h4 class="mb-0">{{ $stats['total_sessions'] }}</h4>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3 d-inline-block">
                                <i class="fas fa-check-circle text-success fa-2x"></i>
                            </div>
                            <h6 class="mt-2">Completed</h6>
                            <h4 class="mb-0">{{ $stats['completed_sessions'] }}</h4>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3 d-inline-block">
                                <i class="fas fa-clock text-warning fa-2x"></i>
                            </div>
                            <h6 class="mt-2">In Progress</h6>
                            <h4 class="mb-0">{{ $stats['active_sessions'] }}</h4>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3 d-inline-block">
                                <i class="fas fa-road text-info fa-2x"></i>
                            </div>
                            <h6 class="mt-2">Distance (KM)</h6>
                            <h4 class="mb-0">{{ $stats['total_distance_km'] }}</h4>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <div class="bg-purple bg-opacity-10 rounded-circle p-3 d-inline-block">
                                <i class="fas fa-hourglass-half text-purple fa-2x"></i>
                            </div>
                            <h6 class="mt-2">Patrol Hours</h6>
                            <h4 class="mb-0">{{ $stats['total_patrol_hours'] }}</h4>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <div class="bg-secondary bg-opacity-10 rounded-circle p-3 d-inline-block">
                                <i class="fas fa-chart-line text-secondary fa-2x"></i>
                            </div>
                            <h6 class="mt-2">Avg Duration</h6>
                            <h4 class="mb-0">{{ $stats['avg_session_duration'] }}h</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assigned Sites and Regions -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Assigned Sites</h5>
            </div>
            <div class="card-body">
                @if($assignedSites->count() > 0)
                    @foreach($assignedSites as $site)
                    <div class="border-bottom pb-2 mb-2">
                        <h6 class="mb-1">{{ $site->site_name }}</h6>
                        <p class="text-muted mb-1 small">
                            <i class="fas fa-building"></i> {{ $site->client_name }}
                            @if($site->shift_name) • <i class="fas fa-clock"></i> {{ $site->shift_name }} @endif
                        </p>
                        <p class="text-muted mb-0 small">
                            <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($site->start_date)->format('M j, Y') }} - 
                            {{ \Carbon\Carbon::parse($site->end_date)->format('M j, Y') }}
                        </p>
                        @if($site->site_address)
                        <p class="text-muted mb-0 small">
                            <i class="fas fa-map-marker-alt"></i> {{ $site->site_address }}
                        </p>
                        @endif
                    </div>
                    @endforeach
                @else
                    <p class="text-muted">No assigned sites found</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Guard Regions</h5>
            </div>
            <div class="card-body">
                @if($guardRegions->count() > 0)
                    @foreach($guardRegions as $region)
                    <div class="border-bottom pb-2 mb-2">
                        <h6 class="mb-1">{{ $region->name ?? 'Unnamed Region' }}</h6>
                        <p class="text-muted mb-1 small">
                            <i class="fas fa-map"></i> {{ $region->type }}
                            @if($region->radius) • <i class="fas fa-ruler"></i> {{ $region->radius }}m radius @endif
                        </p>
                        @if($region->site_name)
                        <p class="text-muted mb-0 small">
                            <i class="fas fa-location-dot"></i> {{ $region->site_name }}
                        </p>
                        @endif
                    </div>
                    @endforeach
                @else
                    <p class="text-muted">No guard regions found</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Patrol Sessions -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Patrol Sessions</h5>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="searchSessions" placeholder="Search sessions..." style="width: 200px;">
                    <select class="form-select form-select-sm" id="filterStatus" style="width: 150px;">
                        <option value="">All Status</option>
                        <option value="Completed">Completed</option>
                        <option value="In Progress">In Progress</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Session ID</th>
                                <th>Type</th>
                                <th>Session</th>
                                <th>Site</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Duration</th>
                                <th>Distance</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sessionsTableBody">
                            @foreach($sessions as $session)
                            <tr class="session-row" data-status="{{ $session->status }}">
                                <td><strong>#{{ $session->session_id }}</strong></td>
                                <td><span class="badge bg-info">{{ $session->type }}</span></td>
                                <td><span class="badge bg-primary">{{ $session->session }}</span></td>
                                <td>{{ $session->site_name ?? 'Unknown' }}</td>
                                <td>{{ \Carbon\Carbon::parse($session->started_at)->format('M j, Y H:i') }}</td>
                                <td>{{ $session->ended_at ? \Carbon\Carbon::parse($session->ended_at)->format('M j, Y H:i') : 'In Progress' }}</td>
                                <td>{{ floor($session->duration_minutes / 60) }}h {{ $session->duration_minutes % 60 }}m</td>
                                <td>{{ $session->distance_km }} km</td>
                                <td>
                                    <span class="badge bg-{{ $session->status == 'Completed' ? 'success' : 'warning' }}">
                                        {{ $session->status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @if($session->path_geojson)
                                        <button type="button" class="btn btn-outline-primary view-path-btn" 
                                                data-session-id="{{ $session->session_id }}"
                                                data-path-geojson="{{ $session->path_geojson }}"
                                                data-start-lat="{{ $session->start_lat }}"
                                                data-start-lng="{{ $session->start_lng }}">
                                            <i class="fas fa-map-marked-alt"></i> View Path
                                        </button>
                                        @endif
                                        <button type="button" class="btn btn-outline-info session-details-btn" 
                                                data-session-id="{{ $session->session_id }}">
                                            <i class="fas fa-info-circle"></i> Details
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $sessions->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Patrol Logs -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Patrol Logs (Last 50)</h5>
            </div>
            <div class="card-body">
                @if($patrolLogs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Type</th>
                                    <th>Notes</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($patrolLogs as $log)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($log->created_at)->format('M j, Y H:i:s') }}</td>
                                    <td><span class="badge bg-secondary">{{ $log->type }}</span></td>
                                    <td>{{ $log->notes ?? 'No notes' }}</td>
                                    <td>
                                        @if($log->lat && $log->lng)
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt"></i> {{ $log->lat }}, {{ $log->lng }}
                                        </small>
                                        @else
                                        <span class="text-muted">No location</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No patrol logs found</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Path View Modal -->
<div class="modal fade" id="pathModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Patrol Path View</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="pathMap" style="height: 500px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Session Details Modal -->
<div class="modal fade" id="sessionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Session Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="sessionModalBody">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<script>
// Search and filter functionality
document.getElementById('searchSessions').addEventListener('input', filterSessions);
document.getElementById('filterStatus').addEventListener('change', filterSessions);

function filterSessions() {
    const searchTerm = document.getElementById('searchSessions').value.toLowerCase();
    const statusFilter = document.getElementById('filterStatus').value;

    document.querySelectorAll('.session-row').forEach(row => {
        const status = row.dataset.status;
        const text = row.textContent.toLowerCase();

        const matchesSearch = text.includes(searchTerm);
        const matchesStatus = !statusFilter || status === statusFilter;

        if (matchesSearch && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Path view functionality
document.querySelectorAll('.view-path-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const sessionId = this.dataset.sessionId;
        const pathGeoJson = this.dataset.pathGeojson;
        const startLat = this.dataset.startLat;
        const startLng = this.dataset.startLng;
        
        showPathModal(sessionId, pathGeoJson, startLat, startLng);
    });
});

function showPathModal(sessionId, pathGeoJson, startLat, startLng) {
    const modal = new bootstrap.Modal(document.getElementById('pathModal'));
    
    // Initialize map when modal is shown
    document.getElementById('pathModal').addEventListener('shown.bs.modal', function() {
        if (!window.pathMap) {
            window.pathMap = L.map('pathMap').setView([startLat || 22.5, startLng || 78.5], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(window.pathMap);
        } else {
            window.pathMap.setView([startLat || 22.5, startLng || 78.5], 13);
        }
        
        // Clear existing layers
        window.pathMap.eachLayer(function(layer) {
            if (layer instanceof L.GeoJSON || layer instanceof L.Marker) {
                window.pathMap.removeLayer(layer);
            }
        });
        
        // Add path
        try {
            const geoJson = JSON.parse(pathGeoJson);
            const pathLayer = L.geoJSON(geoJson, {
                color: '#2e7d32',
                weight: 4,
                opacity: 0.8
            }).addTo(window.pathMap);
            
            // Add start marker
            if (startLat && startLng) {
                L.marker([startLat, startLng])
                    .addTo(window.pathMap)
                    .bindPopup('Start Point')
                    .openPopup();
            }
            
            // Fit bounds to show the entire path
            if (pathLayer.getBounds().isValid()) {
                window.pathMap.fitBounds(pathLayer.getBounds(), { padding: [20, 20] });
            }
        } catch(e) {
            console.error('Error parsing GeoJSON:', e);
        }
    }, { once: true });
    
    modal.show();
}

// Session details functionality
document.querySelectorAll('.session-details-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const sessionId = this.dataset.sessionId;
        showSessionDetails(sessionId);
    });
});

function showSessionDetails(sessionId) {
    fetch(`/api/patrol-session/${sessionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Session not found');
                return;
            }
            
            const session = data.session;
            const logs = data.logs || [];
            
            const modalBody = document.getElementById('sessionModalBody');
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-8">
                        <h6>Session Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Session ID:</strong></td><td>#${session.session_id}</td></tr>
                            <tr><td><strong>Type:</strong></td><td><span class="badge bg-info">${session.type}</span></td></tr>
                            <tr><td><strong>Session:</strong></td><td><span class="badge bg-primary">${session.session}</span></td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="badge bg-${session.status == 'Completed' ? 'success' : 'warning'}">${session.status}</span></td></tr>
                            <tr><td><strong>Site:</strong></td><td>${session.site_name || 'Unknown'}</td></tr>
                            <tr><td><strong>Range:</strong></td><td>${session.range_name || 'Unknown'}</td></tr>
                            <tr><td><strong>Start Time:</strong></td><td>${new Date(session.started_at).toLocaleString()}</td></tr>
                            <tr><td><strong>End Time:</strong></td><td>${session.ended_at ? new Date(session.ended_at).toLocaleString() : 'In Progress'}</td></tr>
                            <tr><td><strong>Duration:</strong></td><td>${Math.floor(session.duration_minutes / 60)}h ${session.duration_minutes % 60}m</td></tr>
                            <tr><td><strong>Distance:</strong></td><td>${session.distance_km} km</td></tr>
                            ${session.method ? `<tr><td><strong>Method:</strong></td><td>${session.method}</td></tr>` : ''}
                        </table>
                    </div>
                    <div class="col-md-4">
                        <h6>Coordinates</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Start:</strong></td><td>${session.start_lat ? `${session.start_lat}, ${session.start_lng}` : 'N/A'}</td></tr>
                            <tr><td><strong>End:</strong></td><td>${session.end_lat ? `${session.end_lat}, ${session.end_lng}` : 'N/A'}</td></tr>
                        </table>
                        
                        ${logs.length > 0 ? `
                        <h6 class="mt-3">Patrol Logs (${logs.length})</h6>
                        <div style="max-height: 200px; overflow-y: auto;">
                            ${logs.map(log => `
                                <div class="border-bottom pb-2 mb-2">
                                    <small class="text-muted">${new Date(log.created_at).toLocaleString()}</small><br>
                                    <strong>${log.type}</strong>
                                    ${log.notes ? `<br><small>${log.notes}</small>` : ''}
                                    ${log.lat && log.lng ? `<br><small class="text-muted">📍 ${log.lat}, ${log.lng}</small>` : ''}
                                </div>
                            `).join('')}
                        </div>
                        ` : '<p class="text-muted">No patrol logs available</p>'}
                    </div>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('sessionModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error fetching session details:', error);
            alert('Error loading session details');
        });
}
</script>

@endsection
