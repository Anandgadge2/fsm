{{-- Guard Detail Modal --}}
<div class="modal fade" id="guardDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-person-badge"></i> Guard Details
                </h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="guardDetailContent" class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-3">Loading guard information…</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('click', function (e) {
    const link = e.target.closest('.guard-name-link');
    if (!link) return;

    e.preventDefault();
    const guardId = link.dataset.guardId;
    const modal = new bootstrap.Modal(document.getElementById('guardDetailModal'));
    modal.show();

    const content = document.getElementById('guardDetailContent');
    content.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
            <p class="mt-3">Loading guard information…</p>
        </div>
    `;

    fetch(`/api/guard-details/${guardId}`)
        .then(r => r.json())
        .then(d => {
            if (!d.success) throw 'Failed';
            content.innerHTML = renderGuardDetails(d.guard);
            setTimeout(() => initGuardPatrolMap(d.guard.patrol_paths || []), 150);
        })
        .catch(() => {
            content.innerHTML = `<div class="alert alert-danger">Failed to load guard details</div>`;
        });
});

function renderGuardDetails(g) {
    return `
    <div class="row">
        <!-- BASIC -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">Basic Info</div>
                <div class="card-body">
                    <p><strong>Name:</strong> ${g.name}</p>
                    <p><strong>Contact:</strong> ${g.contact || 'N/A'}</p>
                    <p><strong>Email:</strong> ${g.email || 'N/A'}</p>
                    <p><strong>Designation:</strong> ${g.designation || 'N/A'}</p>
                </div>
            </div>
        </div>

        <!-- ATTENDANCE -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-success text-white">Attendance</div>
                <div class="card-body">
                    <p><strong>Total Days:</strong> ${g.attendance_stats.total_days}</p>
                    <p><strong>Present:</strong> ${g.attendance_stats.present_count}</p>
                    <p><strong>Absent:</strong> ${g.attendance_stats.absent_count}</p>
                    <p>
                        <strong>Attendance:</strong>
                        <span class="badge bg-success">${g.attendance_stats.attendance_rate}%</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- PATROL STATS -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">Patrol Summary</div>
                <div class="card-body">
                    <p><strong>Total Sessions:</strong> ${g.patrol_stats.total_sessions}</p>
                    <p><strong>Completed:</strong> ${g.patrol_stats.completed_sessions}</p>
                    <p><strong>Ongoing:</strong> ${g.patrol_stats.ongoing_sessions}</p>
                    <p><strong>Total Distance:</strong> ${g.patrol_stats.total_distance_km} km</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <!-- INCIDENTS -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warning">Incidents</div>
                <div class="card-body text-center">
                    <h3 class="text-warning">${g.incident_stats.total_incidents}</h3>
                    <small>Total Incidents</small>
                </div>
            </div>
        </div>

        <!-- MAP -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">Patrol Paths</div>
                <div class="card-body">
                    <div id="guardPatrolMap" style="height:400px"></div>
                </div>
            </div>
        </div>
    </div>
    `;
}

let guardMap = null;

function initGuardPatrolMap(paths) {
    const el = document.getElementById('guardPatrolMap');
    if (!el) return;

    if (guardMap) {
        guardMap.remove();
        guardMap = null;
    }

    guardMap = L.map('guardPatrolMap').setView([20.6, 78.9], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png')
        .addTo(guardMap);

    const layers = [];

    paths.forEach(p => {
        try {
            const geo = typeof p.path_geojson === 'string'
                ? JSON.parse(p.path_geojson)
                : p.path_geojson;

            if (!geo.coordinates) return;

            const line = L.polyline(
                geo.coordinates.map(c => [c[1], c[0]]),
                { color: p.color || '#999', weight: 4 }
            ).addTo(guardMap);

            layers.push(line);
        } catch (e) {
            console.warn('Invalid path', e);
        }
    });

    if (layers.length) {
        guardMap.fitBounds(L.featureGroup(layers).getBounds(), { padding: [40, 40] });
    } else {
        el.innerHTML = '<p class="text-muted text-center py-5">No patrol paths available</p>';
    }
}
</script>

<style>
.guard-name-link {
    cursor: pointer;
    color: #0d6efd;
    font-weight: 500;
}
.guard-name-link:hover {
    text-decoration: underline;
}
</style>
