{{-- ================= GUARD DETAIL MODAL ================= --}}
<div class="modal fade" id="guardDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            {{-- HEADER --}}
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-person-badge me-2"></i> Guard Intelligence Profile
                </h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body">
                <div id="guardDetailContent" class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-3">Loading guard data…</p>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ================= LEAFLET ================= --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let guardMapInstance = null;

/* ================= CLICK HANDLER ================= */
document.addEventListener('click', function (e) {
    const link = e.target.closest('.guard-name-link');
    if (!link) return;

    e.preventDefault();

    const guardId = link.dataset.guardId;
    const modal = new bootstrap.Modal(document.getElementById('guardDetailModal'));
    modal.show();

    const content = document.getElementById('guardDetailContent');
    content.innerHTML = loadingBlock();

    fetch(`/api/guard-details/${guardId}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success || !res.guard) throw 'Invalid response';
            content.innerHTML = renderGuard(res.guard);
setTimeout(() => {
    initGuardMap(res.guard.patrol_paths || []);
    setTimeout(() => {
        guardMapInstance.invalidateSize(); // ⭐ CRITICAL
    }, 200);
}, 300);
        })
        .catch(err => {
            console.error(err);
            content.innerHTML = `<div class="alert alert-danger">Unable to load guard profile</div>`;
        });
});

/* ================= UI ================= */
function loadingBlock() {
    return `
        <div class="py-5 text-center">
            <div class="spinner-border text-primary"></div>
            <p class="mt-3">Loading guard data…</p>
        </div>
    `;
}

/* ================= RENDER ================= */
function renderGuard(g) {
    const a = g.attendance_stats || {};
    const p = g.patrol_stats || {};
    const i = g.incident_stats || {};

    return `
    <div class="row g-3">

        {{-- PROFILE --}}
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white fw-semibold">Profile</div>
                <div class="card-body small">
                    <p><strong>Name:</strong> ${g.name}</p>
                    <p><strong>Gen ID:</strong> ${g.gen_id ?? '-'}</p>
                    <p><strong>Designation:</strong> ${g.designation ?? '-'}</p>
                    <p><strong>Contact:</strong> ${g.contact ?? '-'}</p>
                    <p><strong>Email:</strong> ${g.email ?? '-'}</p>
                    <p><strong>Company:</strong> ${g.company_name ?? '-'}</p>
                </div>
            </div>
        </div>

        {{-- ATTENDANCE --}}
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white fw-semibold">
                    Attendance (${a.month ?? 'Last Month'})
                </div>
                <div class="card-body small">
                    <p><strong>Total Days:</strong> ${a.total_days ?? 0}</p>
                    <p><strong>Present:</strong> ${a.present_days ?? 0}</p>
                    <p><strong>Absent:</strong> ${a.absent_days ?? 0}</p>
                    <p><strong>Late:</strong> ${a.late_days ?? 0}</p>
                    <p>
                        <strong>Attendance %:</strong>
                        <span class="badge bg-success">${a.attendance_rate ?? 0}%</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- PATROL --}}
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white fw-semibold">Patrol Performance</div>
                <div class="card-body small">
                    <p><strong>Total Sessions:</strong> ${p.total_sessions ?? 0}</p>
                    <p><strong>Completed:</strong> ${p.completed_sessions ?? 0}</p>
                    <p><strong>Ongoing:</strong> ${p.ongoing_sessions ?? 0}</p>
                    <p><strong>Total Distance:</strong> ${p.total_distance_km ?? 0} km</p>
                    <p><strong>Avg Distance:</strong> ${p.avg_distance_km ?? 0} km</p>
                </div>
            </div>
        </div>

        {{-- INCIDENTS --}}
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white fw-semibold">Incidents</div>
                <div class="card-body small">
                    <h4 class="fw-bold text-danger text-center">
                        ${i.total_incidents ?? 0}
                    </h4>
                    <p class="text-center mb-2">Total Incidents</p>
                </div>
            </div>
        </div>

    </div>

    {{-- MAP --}}
    <div class="card shadow-sm mt-3">
        <div class="card-header bg-info text-white fw-semibold">
            Patrol Paths (Last 10 Sessions)
        </div>
        <div class="card-body p-0">
            <div id="guardPatrolMap" style="height:420px;"></div>
        </div>
    </div>

    {{-- INCIDENT LIST --}}
    <div class="card shadow-sm mt-3">
        <div class="card-header fw-semibold">Latest Incidents</div>
        <div class="card-body small">
            ${
                (i.latest || []).length
                ? i.latest.map(x => `
                    <div class="border rounded p-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <strong>${x.type}</strong>
                            <span class="badge bg-secondary">${x.priority}</span>
                        </div>
                        <div class="text-muted small">
                            ${x.site_name} · ${x.date} ${x.time}
                        </div>
                        <div class="mt-1">
                            ${x.remark ?? ''}
                        </div>
                    </div>
                `).join('')
                : `<p class="text-muted text-center">No recent incidents</p>`
            }
        </div>
    </div>
    `;
}


function normalizePathGeoJson(raw) {
    let data = raw;

    if (!data) return null;

    // Parse JSON string
    if (typeof data === 'string') {
        try {
            data = JSON.parse(data);
        } catch {
            return null;
        }
    }

    let coords = [];

    /* Case 1: Proper GeoJSON */
    if (data.type === 'LineString' && Array.isArray(data.coordinates)) {
        coords = data.coordinates;
    }

    /* Case 2: [{lat, lng}] */
    else if (Array.isArray(data) && data[0]?.lat !== undefined) {
        coords = data.map(p => [Number(p.lng), Number(p.lat)]);
    }

    /* Case 3: [[x,y]] */
    else if (Array.isArray(data) && Array.isArray(data[0])) {
        coords = data.map(p => [Number(p[0]), Number(p[1])]);
    }

    if (!coords.length) return null;

    /* ================= VALIDATION & AUTO-FIX ================= */

    const fixed = coords.map(([a, b]) => {
        // Latitude must be -90..90, longitude -180..180
        const aIsLat = Math.abs(a) <= 90;
        const bIsLng = Math.abs(b) <= 180;

        const aIsLng = Math.abs(a) <= 180;
        const bIsLat = Math.abs(b) <= 90;

        // If [lat, lng] → swap
        if (aIsLat && bIsLng && !bIsLat) {
            return [b, a]; // → [lng, lat]
        }

        // Already [lng, lat]
        if (aIsLng && bIsLat) {
            return [a, b];
        }

        return null;
    }).filter(Boolean);

    if (fixed.length < 2) return null;

    return {
        type: 'LineString',
        coordinates: fixed
    };
}




/* ================= MAP ================= */
function initGuardMap(paths) {
    const el = document.getElementById('guardPatrolMap');
    if (!el) return;

    if (guardMapInstance) {
        guardMapInstance.remove();
    }

    guardMapInstance = L.map(el, {
        scrollWheelZoom: false,
        zoomControl: true
    });

    // Ctrl + Scroll zoom only
    el.addEventListener('wheel', e => {
        if (e.ctrlKey) {
            e.preventDefault();
            guardMapInstance.scrollWheelZoom.enable();
        } else {
            guardMapInstance.scrollWheelZoom.disable();
        }
    }, { passive: false });

    document.addEventListener('keyup', () => {
        guardMapInstance.scrollWheelZoom.disable();
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png')
        .addTo(guardMapInstance);

    const focusLayers = [];

    const colors = ['#28a745', '#e91e63', '#9c27b0', '#2196f3', '#00bcd4', '#4caf50', '#ff9800', '#f44336', '#3cb44b', '#f58231'];

    paths.forEach((p, index) => {
        const geo = normalizePathGeoJson(p.path_geojson);
        if (!geo || !geo.coordinates || geo.coordinates.length < 2) return;

        const coords = geo.coordinates.map(c => [c[1], c[0]]);

        // Dynamic Color
        const color = colors[(p.id || index) % colors.length];

        // Highlight path
        const mainPath = L.polyline(coords, {
            color: color,
            weight: 6,
            opacity: 0.95,
            lineCap: 'round',
             interactive: true
        }).addTo(guardMapInstance);


        /* ================= HOVER TOOLTIP ================= */
mainPath.bindTooltip(`
    <div style="font-size:12px; line-height:1.4">
        <strong>Patrol Session</strong><br>
        📅 ${new Date(p.started_at).toLocaleDateString()}<br>
        ▶ ${new Date(p.started_at).toLocaleTimeString()}<br>
        ■ ${p.ended_at ? new Date(p.ended_at).toLocaleTimeString() : 'Ongoing'}<br>
        📏 ${(p.distance / 1000).toFixed(2)} km
    </div>
`, {
    sticky: true,
    opacity: 0.9
});

/* ================= CLICK → DETAILS ================= */
mainPath.on('click', () => {
    showPatrolSessionDetails(p);
});
        // // Glow layer
        L.polyline(coords, {
    color: color,
    weight: 12,
    opacity: 0.25,
    interactive: false   // ⭐ IMPORTANT
}).addTo(guardMapInstance);


        focusLayers.push(mainPath);

        // Start marker
        if (p.start_lat && p.start_lng) {
            focusLayers.push(
                L.circleMarker([p.start_lat, p.start_lng], {
                    radius: 7,
                    color: '#fff',
                    weight: 2,
                    fillColor: '#d00000',
                    fillOpacity: 1
                }).addTo(guardMapInstance)
            );
        }

        // End marker + dashed connector
        if (p.end_lat && p.end_lng && p.start_lat && p.start_lng) {
            focusLayers.push(
                L.circleMarker([p.end_lat, p.end_lng], {
                    radius: 7,
                    color: '#fff',
                    weight: 2,
                    fillColor: '#2d6a4f',
                    fillOpacity: 1
                }).addTo(guardMapInstance)
            );

            // L.polyline(
            //     [[p.start_lat, p.start_lng], [p.end_lat, p.end_lng]],
            //     { color: '#6c757d', dashArray: '6,6', weight: 2 }
            // ).addTo(guardMapInstance);
        }
    });

    if (focusLayers.length) {
        const group = L.featureGroup(focusLayers);
        guardMapInstance.fitBounds(group.getBounds(), {
            padding: [60, 60],
            maxZoom: 17
        });
    } else {
        el.innerHTML = `
            <p class="text-muted text-center py-5">
                No patrol paths available
            </p>
        `;
    }
}
function showPatrolSessionDetails(p) {
    const durationMinutes = p.ended_at
        ? Math.round((new Date(p.ended_at) - new Date(p.started_at)) / 60000)
        : null;

    const html = `
        <div style="min-width:240px; font-size:13px">
            <h6 style="margin-bottom:6px">Patrol Session</h6>
            <p><strong>Date:</strong> ${new Date(p.started_at).toLocaleDateString()}</p>
            <p><strong>Start:</strong> ${new Date(p.started_at).toLocaleString()}</p>
            <p><strong>End:</strong> ${p.ended_at ? new Date(p.ended_at).toLocaleString() : 'In Progress'}</p>
            <p><strong>Duration:</strong> ${durationMinutes ? durationMinutes + ' mins' : '—'}</p>
            <p><strong>Distance:</strong> ${(p.distance / 1000).toFixed(2)} km</p>
            <p><strong>Mode:</strong> ${p.session}</p>
            <p><strong>Type:</strong> ${p.type}</p>
        </div>
    `;

    L.popup({ maxWidth: 320 })
        .setLatLng([p.start_lat, p.start_lng])
        .setContent(html)
        .openOn(guardMapInstance);
}


</script>

<style>
.guard-name-link {
    cursor: pointer;
    color: #0d6efd;
    font-weight: 600;
}
.guard-name-link:hover {
    text-decoration: underline;
}
</style>
