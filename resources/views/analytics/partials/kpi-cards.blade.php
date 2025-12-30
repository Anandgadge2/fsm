{{-- Key Performance Indicators --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1">Active Guards</h6>
                        <h3 class="mb-0 fw-bold text-primary">{{ number_format($kpis['activeGuards']) }}</h3>
                    </div>
                    <div class="text-primary" style="font-size: 2rem;">👥</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1">Total Patrols</h6>
                        <h3 class="mb-0 fw-bold text-success">{{ number_format($kpis['totalPatrols']) }}</h3>
                        <small class="text-muted">{{ $kpis['completedPatrols'] }} completed</small>
                    </div>
                    <div class="text-success" style="font-size: 2rem;">🚶</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1">Total Distance</h6>
                        <h3 class="mb-0 fw-bold text-info">{{ number_format($kpis['totalDistance'], 2) }} km</h3>
                        <small class="text-muted">Avg: {{ number_format($kpis['avgDistancePerGuard'], 2) }} km/guard</small>
                    </div>
                    <div class="text-info" style="font-size: 2rem;">📍</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1">Attendance Rate</h6>
                        <h3 class="mb-0 fw-bold text-warning">{{ number_format($kpis['attendanceRate'], 1) }}%</h3>
                        <small class="text-muted">{{ $kpis['presentCount'] }} present</small>
                    </div>
                    <div class="text-warning" style="font-size: 2rem;">✓</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1">Total Incidents</h6>
                        <h3 class="mb-0 fw-bold text-danger">{{ number_format($kpis['totalIncidents']) }}</h3>
                        <small class="text-muted">{{ $kpis['pendingIncidents'] }} pending</small>
                    </div>
                    <div class="text-danger" style="font-size: 2rem;">⚠️</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1">Resolution Rate</h6>
                        <h3 class="mb-0 fw-bold text-success">{{ number_format($kpis['resolutionRate'], 1) }}%</h3>
                        <small class="text-muted">{{ $kpis['resolvedIncidents'] }} resolved</small>
                    </div>
                    <div class="text-success" style="font-size: 2rem;">✅</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1">Site Coverage</h6>
                        <h3 class="mb-0 fw-bold text-info">{{ number_format($coverageAnalysis['coveragePercentage'], 1) }}%</h3>
                        <small class="text-muted">{{ $coverageAnalysis['sitesWithPatrols'] }} / {{ $coverageAnalysis['totalSites'] }} sites</small>
                    </div>
                    <div class="text-info" style="font-size: 2rem;">🗺️</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1">Total Sites</h6>
                        <h3 class="mb-0 fw-bold text-primary">{{ number_format($kpis['totalSites']) }}</h3>
                    </div>
                    <div class="text-primary" style="font-size: 2rem;">🌲</div>
                </div>
            </div>
        </div>
    </div>
</div>

